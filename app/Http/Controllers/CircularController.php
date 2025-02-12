<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCircularRequest;
use App\Http\Requests\UpdateCircularRequest;
use App\Models\Circular;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CircularController extends Controller
{
    public function index(Request $request)
    {
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $query = Circular::with(['user', 'division', 'updatedBy']);

        // Apply filters
        if ($request->has('filter')) {
            $filters = $request->filter;

            if (!empty($filters['division_id'])) {
                $query->where('division_id', $filters['division_id']);
            }

            if (!empty($filters['circular_no'])) {
                $query->where('circular_no', 'like', '%' . $filters['circular_no'] . '%');
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }
        }

        $circulars = $query->latest()->paginate(10);
        $divisions = Division::all();

        return view('circulars.index', compact('circulars', 'divisions'));
    }

    public function create()
{
    // Manually check if user is authenticated
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Please login to create a circular.');
    }

    $divisions = Division::all(); // Fetch all divisions
    return view('circulars.create', compact('divisions'));
}


public function store(Request $request)
{
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Please login to create a circular.');
    }

    // Validate the incoming request
    $validated = $request->validate([
        'circular_no' => 'required|string|max:255|unique:circulars,circular_no',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'division_id' => 'required|exists:divisions,id',
        'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
    ], [
        'circular_no.unique' => 'This circular number is already taken. Please use a different circular number.',
    ]);

    Log::debug('Validated data for store:', $validated);

    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')->store('circulars', 'public');
        $validated['attachment'] = $path;
    }

    $validated['user_id'] = Auth::id();
    $validated['update_by'] = Auth::id();

    try {
        Circular::create($validated);
        return redirect()->route('circulars.index')->with('success', 'Circular created successfully.');
    } catch (\Exception $e) {
        Log::error("Circular store error: " . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to create Circular. Please try again.');
    }
}



    public function show(Circular $circular)
    {
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view the circular.');
        }

        $circular->load(['user', 'division', 'updatedBy']);
        return view('circulars.show', compact('circular'));
    }

    public function edit(Circular $circular)
    {
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to edit the circular.');
        }

        $divisions = Division::all();
        $users = User::all();
        return view('circulars.edit', compact('circular', 'divisions', 'users'));
    }

    public function update(Request $request, Circular $circular)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to update the circular.');
        }

        // Validate the incoming request
        $validated = $request->validate([
            'circular_no' => 'required|string|max:255|unique:circulars,circular_no,' . $circular->id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'division_id' => 'required|exists:divisions,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ], [
            'circular_no.unique' => 'This circular number is already taken. Please use a different circular number.',
        ]);

        Log::debug('Form data received:', $validated);

        if ($request->hasFile('attachment')) {
            if ($circular->attachment && Storage::disk('public')->exists($circular->attachment)) {
                Storage::disk('public')->delete($circular->attachment);
            }
            $path = $request->file('attachment')->store('circulars', 'public');
            $validated['attachment'] = $path;
        }

        $validated['update_by'] = Auth::id();

        try {
            $isUpdated = $circular->update($validated);
            if ($isUpdated) {
                return redirect()->route('circulars.index')->with('success', 'Circular updated successfully.');
            } else {
                return redirect()->back()->with('error', 'No changes were made to the circular.');
            }
        } catch (\Exception $e) {
            Log::error("Circular update error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Circular. Please try again.');
        }
    }




}