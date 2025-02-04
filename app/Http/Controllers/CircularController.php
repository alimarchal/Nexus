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
    // Manually check if user is authenticated
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Please login to create a circular.');
    }

    // Validate the incoming request
    $validated = $request->validate([
        'circular_no' => 'required|string|max:255',
        'title' => 'required|string|max:255', // Validate title
        'description' => 'nullable|string', // Validate description
        'division_id' => 'required|exists:divisions,id',
        'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
    ]);

    // Log the validated data (optional, for debugging)
    Log::debug('Validated data for store:', $validated);

    // Handle file upload if exists
    if ($request->hasFile('attachment')) {
        // Store the uploaded file in the 'public' disk under 'circulars' directory
        $path = $request->file('attachment')->store('circulars', 'public');
        $validated['attachment'] = $path; // Save the file path in the validated data
        Log::debug('File uploaded and saved at:', ['path' => $path]);
    }

    // Add user and update_by details
    $validated['user_id'] = Auth::id();
    $validated['update_by'] = Auth::id();

    // Log the final data that will be saved
    Log::debug('Data being saved to the database:', $validated);

    // Try to create a new circular
    try {
        $circular = Circular::create($validated); // Store the new circular

        // Log the successful creation (optional, for debugging)
        Log::debug('Circular created successfully:', $circular->toArray());

        return redirect()->route('circulars.index')->with('success', 'Circular created successfully.');
    } catch (\Exception $e) {
        // Log the error and return back with error message
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
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to update the circular.');
        }

        // Validate the incoming request
        $validated = $request->validate([
            'circular_no' => 'required|string|max:255',
            'title' => 'nullable|string|max:255', // Validate the title (if provided)
            'description' => 'nullable|string', // Validate the description (if provided)
            'division_id' => 'required|exists:divisions,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // Log the validated data
        Log::debug('Form data received:', $validated);

        // Handle file upload if there is a new attachment
        if ($request->hasFile('attachment')) {
            // Delete the old file if it exists
            if ($circular->attachment && Storage::disk('public')->exists($circular->attachment)) {
                Storage::disk('public')->delete($circular->attachment);
            }

            // Store the new file and set the attachment path
            $path = $request->file('attachment')->store('circulars', 'public');
            $validated['attachment'] = $path;
        }

        // Set the 'update_by' field to the currently authenticated user
        $validated['update_by'] = Auth::id();

        // Log the old and updated data for debugging
        Log::debug('Old data before update:', $circular->toArray());
        Log::debug('Updated data to be saved:', $validated);

        // Check if data has changed before updating
        if ($circular->circular_no === $validated['circular_no'] &&
            $circular->title === $validated['title'] &&
            $circular->description === $validated['description'] &&
            $circular->division_id === $validated['division_id'] &&
            $circular->attachment === $validated['attachment']) {
            return redirect()->back()->with('error', 'No changes detected.');
        }

        try {
            // Update the circular with validated data
            $isUpdated = $circular->update($validated);

            // Log the update result
            Log::debug('Is updated:', ['success' => $isUpdated]);

            // Refresh the model and log the result to check if it was updated
            $circular->refresh();
            Log::debug('Circular after update:', $circular->toArray());

            if ($isUpdated) {
                return redirect()->route('circulars.index')->with('success', 'Circular updated successfully.');
            } else {
                return redirect()->back()->with('error', 'No changes were made to the circular.');
            }
        } catch (\Exception $e) {
            // Log the error and return back with error message
            Log::error("Circular update error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Circular. Please try again.');
        }
    }




}