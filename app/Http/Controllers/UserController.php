<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedInclude;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view users', only: ['index', 'show']),
            new Middleware('role_or_permission:create users', only: ['create', 'store']),
            new Middleware('role_or_permission:edit users', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete users', only: ['destroy']),
            new Middleware('role_or_permission:assign permissions', only: ['store', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(User::getAllowedFilters())
            ->allowedSorts(User::getAllowedSorts())
            ->allowedIncludes(User::getAllowedIncludes())
            ->with(['branch', 'roles', 'permissions'])
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 10))
            ->appends(request()->query());

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::all();
        $divisions = Division::all();
        $roles = Role::all();
        $permissions = Permission::all();

        return view('users.create', compact('branches', 'divisions', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'division_id' => $request->division_id,
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

        return redirect()->route('users.index')->with('success', 'User created successfully with assigned roles and permissions.');
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        $divisions = Division::all();
        $roles = Role::all();
        $permissions = Permission::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('users.edit', compact('user', 'branches', 'divisions', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent self-deletion protection
        if ($user->id === auth()->id() && $request->is_active === 'No') {
            return redirect()->back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        // Prevent removal of super-admin role from a super-admin user
        if ($user->hasRole('super-admin')) {
            $submittedRoleIds = $request->input('roles', []);
            $superAdminRole = Role::where('name', 'super-admin')->first();
            if ($superAdminRole && (!in_array($superAdminRole->id, $submittedRoleIds))) {
                return redirect()->back()->withErrors(['roles' => 'You cannot remove the super-admin role from a super-admin user.']);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'division_id' => $request->division_id,
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

        return redirect()->route('users.index')->with('success', 'User updated successfully with assigned roles and permissions.');
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