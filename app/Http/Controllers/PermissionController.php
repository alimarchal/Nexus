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

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('role_or_permission:view permissions', only: ['index', 'show']),
            new Middleware('role_or_permission:create permissions', only: ['create', 'store']),
            new Middleware('role_or_permission:edit permissions', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete permissions', only: ['destroy']),
        ];
    }

    // Show the form for creating a new permission
    public function create()
    {
        return view('permissions.create');
    }

    // Store a newly created permission in storage
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        // Create the permission
        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    /** Rows-per-page choices on the list. */
    public const PER_PAGE = [10, 25, 50, 100];

    // Display a listing of the permissions with pagination
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : 25;
        $sort = in_array(ltrim((string) $request->query('sort', 'name'), '-'), ['name', 'created_at', 'users_count'], true) ? (string) $request->query('sort', 'name') : 'name';
        $directGrants = config('permission.table_names.model_has_permissions');
        $roleGrants = config('permission.table_names.role_has_permissions');

        // Direct (per-user) grants; Spatie's users() relation cannot be used with withCount.
        $query = Permission::query()
            ->select('permissions.*')
            ->addSelect(['users_count' => DB::table($directGrants)
                ->selectRaw('count(*)')
                ->whereColumn('permission_id', 'permissions.id')
                ->where('model_type', (new User)->getMorphClass())])
            ->with('roles:id,name');

        if ($term = trim((string) $request->input('filter.search', $request->input('filter.name', '')))) {
            $query->where('name', 'LIKE', '%'.$term.'%');
        }

        // Module = the product module the permission belongs to (see PermissionCatalog).
        if ($module = $request->input('filter.module')) {
            $names = Permission::pluck('name')->filter(fn (string $name) => PermissionCatalog::moduleKey($name) === $module)->values();
            $query->whereIn('name', $names);
        }

        if ($roleId = $request->input('filter.role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('id', $roleId));
        }

        if ($createdAt = $request->input('filter.created_at')) {
            $query->whereDate('created_at', $createdAt);
        }

        if ($request->input('filter.usage') === 'orphan') {
            $query->whereNotIn('id', DB::table($roleGrants)->select('permission_id'))
                ->whereNotIn('id', DB::table($directGrants)->select('permission_id'));
        }

        $query->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')->orderBy('name');

        $permissions = $query->paginate($perPage)->withQueryString();

        $modules = collect(PermissionCatalog::build(Permission::all()))->map(fn (array $module) => ['key' => $module['key'], 'label' => $module['label'], 'count' => $module['count']]);
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return view('permissions.index', [
            'permissions' => $permissions,
            'modules' => $modules,
            'roles' => $roles,
            'perPage' => $perPage,
            'sort' => $sort,
            'stats' => [
                'permissions' => Permission::count(),
                'modules' => $modules->count(),
                'in_roles' => DB::table($roleGrants)->distinct()->count('permission_id'),
                'orphans' => Permission::query()
                    ->whereNotIn('id', DB::table($roleGrants)->select('permission_id'))
                    ->whereNotIn('id', DB::table($directGrants)->select('permission_id'))
                    ->count(),
            ],
        ]);
    }

    // Show the form for editing the specified permission
    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    // Update the specified permission in storage
    public function update(Request $request, Permission $permission)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$permission->id,
        ]);

        // Update the permission
        $permission->update([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully!');
    }

    // Remove the specified permission from storage
    public function destroy(Permission $permission)
    {
        // Prevent deletion of critical permissions
        $criticalPermissions = ['view users', 'edit users', 'create users', 'delete users', 'view roles', 'edit roles'];

        if (in_array($permission->name, $criticalPermissions)) {
            return redirect()->back()->with('error', 'Cannot delete critical system permission: '.$permission->name);
        }

        // Delete the permission
        $permission->delete();

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully!');
    }
}
