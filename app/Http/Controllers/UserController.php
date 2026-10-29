<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Division;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view users', only: ['index', 'show']),
            new Middleware('role_or_permission:create users', only: ['create', 'store']),
            new Middleware('role_or_permission:edit users', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete users', only: ['destroy']),
            new Middleware('role_or_permission:edit users', only: ['bulkUpdate']),
            new Middleware('role_or_permission:assign permissions', only: ['store', 'update']),
        ];
    }

    /** Rows-per-page choices on the list. */
    public const PER_PAGE = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : 25;

        $users = $this->listQuery($request)
            ->with(['branch', 'region', 'division', 'headOffice', 'roles.permissions', 'permissions'])
            ->paginate($perPage)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'branches' => Branch::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'perPage' => $perPage,
            'stats' => $this->listStats($request),
        ]);
    }

    /**
     * Filters and sort for the list (the Active / Suspended tab is the is_active filter).
     */
    private function listQuery(Request $request, bool $withSort = true): QueryBuilder
    {
        $query = QueryBuilder::for(User::class, $request)
            ->allowedFilters([
                ...User::getAllowedFilters(),
                AllowedFilter::callback('search', function ($query, $value): void {
                    $term = trim((string) (is_array($value) ? implode(' ', $value) : $value));
                    $query->where(fn ($inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                }),
                AllowedFilter::exact('is_super_admin'),
                AllowedFilter::callback('access', function ($query, $value): void {
                    if ($value === 'none') {
                        $query->where('is_super_admin', '!=', 'Yes')->doesntHave('roles')->doesntHave('permissions');
                    }
                }),
            ]);

        if ($withSort) {
            $query->allowedSorts(User::getAllowedSorts())->defaultSort('-created_at');
        }

        return $query;
    }

    /**
     * Headline numbers for the list. Tab counts follow the other filters (search, role, branch ...)
     * but not the Active / Suspended tab itself.
     *
     * @return array{total: int, active: int, inactive: int, super_admins: int, no_access: int}
     */
    private function listStats(Request $request): array
    {
        $filters = array_filter((array) $request->input('filter', []), fn ($v) => $v !== null && $v !== '');
        unset($filters['is_active']);

        $byStatus = $this->listQuery(new Request(['filter' => $filters]), false)
            ->getEloquentBuilder()
            ->reorder()
            ->selectRaw('is_active, COUNT(*) as aggregate')
            ->groupBy('is_active')
            ->pluck('aggregate', 'is_active');

        return [
            'total' => (int) $byStatus->sum(),
            'active' => (int) ($byStatus['Yes'] ?? 0),
            'inactive' => (int) ($byStatus['No'] ?? 0),
            'super_admins' => User::query()->where(fn ($q) => $q->where('is_super_admin', 'Yes')->orWhereHas('roles', fn ($r) => $r->where('name', 'super-admin')))->count(),
            'no_access' => User::query()->where('is_active', 'Yes')->where('is_super_admin', '!=', 'Yes')->doesntHave('roles')->doesntHave('permissions')->count(),
        ];
    }

    /**
     * Read-only profile: office, roles, what the user can open (by module, with where each
     * permission comes from) and recent activity. Changes happen on Edit.
     */
    public function show(User $user)
    {
        $user->load(['branch', 'region', 'division', 'headOffice', 'roles.permissions', 'permissions']);

        // permission id => where it comes from (extra and / or role names)
        $grants = [];
        foreach ($user->permissions as $permission) {
            $grants[(string) $permission->id]['direct'] = true;
        }
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $grants[(string) $permission->id]['roles'][] = $role->name;
            }
        }

        // Role permissions revoked for this user are not granted to them.
        foreach ($user->deniedPermissions()->pluck('permissions.id') as $deniedId) {
            unset($grants[(string) $deniedId]);
        }

        $activity = rescue(fn () => Activity::query()
            ->where(fn ($q) => $q->where(fn ($c) => $c->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id))
                ->orWhere(fn ($s) => $s->where('subject_type', $user->getMorphClass())->where('subject_id', $user->id)))
            ->latest()
            ->limit(10)
            ->get(), collect(), false);

        return view('users.show', [
            'user' => $user,
            'modules' => PermissionCatalog::build(Permission::all()),
            'grants' => $grants,
            'activity' => $activity,
        ]);
    }

    /**
     * Activate, suspend or delete several users at once. Never touches your own account or the last super admin.
     */
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'in:activate,deactivate,delete'],
        ]);

        if ($data['action'] === 'delete' && ! $request->user()->can('delete users')) {
            abort(403);
        }

        $ids = collect($data['ids'])->map(fn ($id) => (int) $id)->reject(fn (int $id) => $id === auth()->id())->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'No valid users selected for this action.');
        }

        DB::transaction(function () use ($ids, $data): void {
            if ($data['action'] === 'delete') {
                $remainingSuperAdmins = User::role('super-admin')->count();

                foreach (User::whereIn('id', $ids)->get() as $user) {
                    if ($user->hasRole('super-admin')) {
                        if ($remainingSuperAdmins <= 1) {
                            continue;
                        }
                        $remainingSuperAdmins--;
                    }

                    $user->delete();
                }

                return;
            }

            User::whereIn('id', $ids)->update(['is_active' => $data['action'] === 'activate' ? 'Yes' : 'No']);
        });

        return redirect()->route('users.index')->with('success', match ($data['action']) {
            'activate' => 'Users activated.',
            'deactivate' => 'Users suspended.',
            default => 'Users deleted (the last super admin and your own account are always kept).',
        });
    }

    public function create()
    {
        $branches = Branch::all();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $headOffices = HeadOffice::orderBy('name')->get();
        $roles = Role::all();
        $permissions = Permission::all();

        return view('users.create', compact('branches', 'regions', 'divisions', 'headOffices', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'region_id' => 'nullable|exists:regions,id',
            'division_id' => 'nullable|exists:divisions,id',
            'head_office_id' => 'nullable|exists:head_offices,id',
            'is_president_office' => 'nullable|boolean',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'denied_permissions' => 'array',
            'denied_permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'region_id' => $request->region_id,
            'division_id' => $request->division_id,
            'head_office_id' => $request->head_office_id,
            'is_president_office' => $request->boolean('is_president_office'),
            'password' => Hash::make($request->password),
            'is_super_admin' => $request->is_super_admin,
            'is_active' => $request->is_active,
        ]);

        // Assign roles if provided (convert IDs to names for spatie/permission)
        if ($request->filled('roles')) {
            $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        }

        // Assign individual permissions if provided (convert IDs to names)
        if ($request->filled('permissions')) {
            $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
            $user->syncPermissions($permissionNames);
        }

        $this->syncDeniedPermissions($user, $request);

        return redirect()->route('users.index')->with('success', 'User created successfully with assigned roles and permissions.');
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $headOffices = HeadOffice::orderBy('name')->get();
        $roles = Role::all();
        $permissions = Permission::all();
        $userRoles = $user->roles->pluck('id')->map(fn (int $roleId): string => (string) $roleId)->all();
        $userPermissions = $user->permissions->pluck('id')->toArray();
        $userDenied = $user->deniedPermissions()->pluck('permissions.id')->map(fn (int $id): string => (string) $id)->all();

        return view('users.edit', compact('user', 'branches', 'regions', 'divisions', 'headOffices', 'roles', 'permissions', 'userRoles', 'userPermissions', 'userDenied'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent self-deletion protection
        if ($user->id === auth()->id() && $request->is_active === 'No') {
            return redirect()->back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        // A super-admin may not remove their own role or demote the final super-admin.
        if ($user->hasRole('super-admin') && ($user->is(auth()->user()) || User::role('super-admin')->count() <= 1)) {
            $submittedRoleIds = $request->input('roles', []);
            $superAdminRole = Role::where('name', 'super-admin')->first();
            if ($superAdminRole && (! in_array($superAdminRole->id, $submittedRoleIds))) {
                return redirect()->back()->withErrors(['roles' => 'You cannot remove your own super-admin role or demote the final super-admin.']);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'region_id' => 'nullable|exists:regions,id',
            'division_id' => 'nullable|exists:divisions,id',
            'head_office_id' => 'nullable|exists:head_offices,id',
            'is_president_office' => 'nullable|boolean',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'denied_permissions' => 'array',
            'denied_permissions.*' => 'exists:permissions,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'region_id' => $request->region_id,
            'division_id' => $request->division_id,
            'head_office_id' => $request->head_office_id,
            'is_president_office' => $request->boolean('is_president_office'),
            'is_super_admin' => $request->is_super_admin,
            'is_active' => $request->is_active,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Sync roles (IDs -> names) if provided, otherwise clear all roles
        if ($request->has('roles')) {
            $roleIds = $request->roles ?? [];
            $roleNames = empty($roleIds) ? [] : Role::whereIn('id', $roleIds)->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        } else {
            // Explicitly clear roles if roles key omitted? Keep existing behavior (clear) by passing empty array
            $user->syncRoles([]);
        }

        // Sync individual permissions (IDs -> names) if provided, otherwise clear all permissions
        if ($request->has('permissions')) {
            $permIds = $request->permissions ?? [];
            $permissionNames = empty($permIds) ? [] : Permission::whereIn('id', $permIds)->pluck('name')->toArray();
            $user->syncPermissions($permissionNames);
        } else {
            $user->syncPermissions([]);
        }

        $this->syncDeniedPermissions($user, $request);

        return redirect()->route('users.index')->with('success', 'User updated successfully with assigned roles and permissions.');
    }

    /**
     * Keep only the revoked permissions that a selected role really grants: a
     * revoke means "the role gives this, but not to this user".
     */
    private function syncDeniedPermissions(User $user, Request $request): void
    {
        $roleGranted = $user->fresh()->getPermissionsViaRoles()->pluck('id');
        $denied = collect($request->input('denied_permissions', []))->map(fn ($id) => (int) $id)->intersect($roleGranted)->values()->all();

        $user->deniedPermissions()->sync($denied);
        $user->forgetDeniedPermissions();
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        // Prevent deletion of super admin if it's the last one
        if ($user->hasRole('super-admin') && User::role('super-admin')->count() <= 1) {
            return redirect()->back()->withErrors(['user' => 'Cannot delete the last super admin user.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
