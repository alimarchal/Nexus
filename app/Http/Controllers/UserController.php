<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)

    {
        $query = \App\Models\User::query();

    // Apply branch filter
    if ($branchId = $request->input('filter.branch_id')) {
        $query->where('branch_id', $branchId);
    }

    // Apply role filter
    if ($role = $request->input('filter.role')) {
        $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    // Apply status filter
    if ($status = $request->input('filter.is_active')) {
        $query->where('is_active', $status);
    }

    // Apply created_at filter
    if ($createdAt = $request->input('filter.created_at')) {
        $query->whereDate('created_at', $createdAt);
    }

    // Paginate the results
    $users = $query->paginate(10);

    return view('users.index', compact('users'));

    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'password' => Hash::make($request->password),
            'is_super_admin' => $request->is_super_admin,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'branch_id' => 'nullable|exists:branches,id',
            'is_super_admin' => 'required|in:Yes,No',
            'is_active' => 'required|in:Yes,No',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'is_super_admin' => $request->is_super_admin,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}