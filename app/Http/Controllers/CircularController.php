<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Circular;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCircularRequest;
use App\Http\Requests\UpdateCircularRequest;

class CircularController extends Controller
{

    public function index(Request $request)
    {
        $circulars = QueryBuilder::for(Circular::class)
            ->allowedFilters([
                AllowedFilter::exact('division_id'),
                AllowedFilter::partial('circular_no'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                })
            ])
            ->with(['user', 'division', 'updatedBy'])
            ->latest()
            ->paginate(10);

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


public function store(StoreCircularRequest $request)
{
    $validated = $request->validated();

    // Handle file upload
    if ($request->hasFile('attachment')) {
        try {
            $validated['attachment'] = $request->file('attachment')->store('circulars', 'public');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'File upload failed: ' . $e->getMessage());
        }
    }

    // Create circular
    try {
        $circular = Circular::create($validated);
        
        return redirect()
            ->route('circulars.show', $circular->id)
            ->with('success', 'Circular "' . $circular->title . '" created successfully.');
            
    } catch (\Illuminate\Database\QueryException $e) {
        // Handle specific database errors
        if ($e->getCode() === '23000') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Circular number already exists. Please use a different number.');
        }
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Database error: ' . $e->getMessage());
            
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create circular: ' . $e->getMessage());
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