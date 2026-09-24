<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    // Display a listing of the permissions with pagination
    public function index(Request $request)
    {
        // Direct (per-user) grants; Spatie's users() relation cannot be used with withCount.
        $query = Permission::query()
            ->select('permissions.*')
            ->addSelect(['users_count' => DB::table(config('permission.table_names.model_has_permissions'))
                ->selectRaw('count(*)')
                ->whereColumn('permission_id', 'permissions.id')
                ->where('model_type', (new User)->getMorphClass())])
            ->with('roles:id,name')
            ->orderBy('name');

        if ($name = $request->input('filter.name')) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        // Module = everything after the first word ("view aksic claims" -> "aksic claims").
        if ($module = $request->input('filter.module')) {
            $query->where('name', 'LIKE', '% '.$module);
        }

        if ($roleId = $request->input('filter.role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('id', $roleId));
        }

        if ($createdAt = $request->input('filter.created_at')) {
            $query->whereDate('created_at', $createdAt);
        }

        $permissions = $query->paginate(25)->withQueryString();

        $modules = Permission::orderBy('name')->pluck('name')
            ->filter(fn ($name) => str_contains($name, ' '))
            ->map(fn ($name) => Str::of($name)->after(' ')->lower()->value())
            ->unique()->sort()->values();
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return view('permissions.index', compact('permissions', 'modules', 'roles'));
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
