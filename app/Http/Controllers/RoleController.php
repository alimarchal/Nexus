<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Show the form for creating a new role
    public function create()
    {
        return view('roles.create');
    }

    // Store a newly created role in storage
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
        ]);

        Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    // Display a listing of the roles with pagination
    public function index()
    {
        // Paginate roles with 10 per page (adjust this number as necessary)
        $roles = Role::paginate(10);

        return view('roles.index', compact('roles'));
    }

    // Show the form for editing the specified role
    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    // Update the specified role in storage
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    // Remove the specified role from storage
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }
}
