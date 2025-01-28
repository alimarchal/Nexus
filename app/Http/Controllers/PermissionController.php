<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\BranchTarget;

class PermissionController extends Controller
{
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
        ]);

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    // Display a listing of the permissions with pagination
    public function index(Request $request)
{
    $query = Permission::query();

    // Apply filters based on request inputs
    if ($name = $request->input('filter.name')) {
        $query->where('name', 'LIKE', '%' . $name . '%');
    }

    if ($createdAt = $request->input('filter.created_at')) {
        $query->whereDate('created_at', $createdAt);
    }

    // Paginate the filtered results
    $permissions = $query->paginate(10);

    // Return the view with permissions data
    return view('permissions.index', compact('permissions'));
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
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        // Update the permission
        $permission->update([
            'name' => $request->name,
        ]);

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully!');
    }

    // Remove the specified permission from storage
    public function destroy(Permission $permission)
    {
        // Delete the permission
        $permission->delete();

        // Redirect with success message
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully!');
    }
}