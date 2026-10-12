<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {

        return [
            new Middleware('role_or_permission:view roles', only: ['index', 'show']),
            new Middleware('role_or_permission:create roles', only: ['create', 'store']),
            new Middleware('role_or_permission:edit roles', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete roles', only: ['destroy']),
            new Middleware('role_or_permission:assign permissions', only: ['store', 'update']),
        ];
    }

    // Show the form for creating a new role
    public function create()
    {
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    // Store a newly created role in storage
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        // Assign permissions to the role if provided
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully with assigned permissions!');
    }

    /** Rows-per-page choices on the list. */
    public const PER_PAGE = [10, 25, 50, 100];

    // Display a listing of the roles with pagination
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : 25;
        $sort = in_array(ltrim((string) $request->query('sort', 'name'), '-'), ['name', 'created_at', 'users_count'], true) ? (string) $request->query('sort', 'name') : 'name';

        // Spatie's users() relation needs a guard on the instance, so withCount('users')
        // cannot be used here; count the pivot rows directly instead.
        $query = Role::query()
            ->select('roles.*')
            ->addSelect(['users_count' => DB::table(config('permission.table_names.model_has_roles'))
                ->selectRaw('count(*)')
                ->whereColumn('role_id', 'roles.id')
                ->where('model_type', (new User)->getMorphClass())])
            ->with('permissions:id,name');

        // Filters: one search box (name), plus the older name and created date filters
        if ($term = trim((string) $request->input('filter.search', $request->input('filter.name', '')))) {
            $query->where('name', 'LIKE', '%'.$term.'%');
        }

        if ($createdAt = $request->input('filter.created_at')) {
            $query->whereDate('created_at', $createdAt);
        }

        if ($request->input('filter.users') === 'none') {
            $query->whereRaw('(select count(*) from '.config('permission.table_names.model_has_roles').' where role_id = roles.id and model_type = ?) = 0', [(new User)->getMorphClass()]);
        }

        $query->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')->orderBy('name');

        $roles = $query->paginate($perPage)->withQueryString();

        return view('roles.index', [
            'roles' => $roles,
            'perPage' => $perPage,
            'sort' => $sort,
            'stats' => [
                'roles' => Role::count(),
                'permissions' => Permission::count(),
                'users_with_roles' => DB::table(config('permission.table_names.model_has_roles'))->where('model_type', (new User)->getMorphClass())->distinct()->count('model_id'),
                'unused' => Role::query()->whereNotIn('id', DB::table(config('permission.table_names.model_has_roles'))->where('model_type', (new User)->getMorphClass())->select('role_id'))->count(),
            ],
        ]);
    }

    /**
     * Read-only role page: what the role grants, by module, and who has it.
     */
    public function show(Role $role)
    {
        $role->load('permissions');

        return view('roles.show', [
            'role' => $role,
            'modules' => PermissionCatalog::build($role->permissions),
            'allCount' => Permission::count(),
            'users' => User::role($role->name)->orderBy('name')->limit(15)->get(['id', 'name', 'email', 'is_active']),
            'usersCount' => User::role($role->name)->count(),
        ]);
    }

    // Show the form for editing the specified role
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // Update the specified role in storage
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'guard_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        // Sync permissions - this will add/remove permissions as needed
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully with assigned permissions!');
    }

    // Remove the specified role from storage
    public function destroy(Role $role)
    {
        // Prevent deletion of super-admin role if users are assigned to it
        if ($role->name === 'super-admin') {
            return redirect()->back()->with('error', 'The super-admin role cannot be deleted.');
        }

        // A role that is still assigned would silently strip access from those users.
        if (($count = $role->users()->count()) > 0) {
            return redirect()->back()->with('error', "Role '{$role->name}' is assigned to {$count} user(s). Move them to another role first.");
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }
}
