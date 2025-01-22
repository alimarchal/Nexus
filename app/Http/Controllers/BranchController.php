<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Region;
use App\Models\District;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */public function index()
{
    // Paginate the branches, e.g., 10 per page

    $branchTargets = QueryBuilder::for(Branch::class)
    ->allowedFilters([
        AllowedFilter::exact('name'),
        AllowedFilter::exact('district_id'),
        AllowedFilter::exact('region_id'),
        AllowedFilter::exact('updated_by_user_id'),
        AllowedFilter::exact('annual_target_amount'),
    ])
    ->paginate(10);
    $branches = Branch::with(['region', 'district'])->paginate(10);

    // Return the view with paginated branches
    return view('branches.index', compact('branches'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all regions and districts for the dropdown options
        $regions = Region::all();
        $districts = District::all();

        // Return the view to create a new branch
        return view('branches.create', compact('regions', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'code' => 'required|string|unique:branches,code',
            'name' => 'required|string',
            'address' => 'required|string',
        ]);

        // Create the new branch using validated data
        Branch::create($request->all());

        // Redirect back to the branches list with a success message
        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        // Return the view to show the branch details
        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        // Get all regions and districts for editing the branch
        $regions = Region::all();
        $districts = District::all();

        // Return the view to edit the branch with pre-filled data
        return view('branches.edit', compact('branch', 'regions', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        // Validate the incoming request
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'code' => 'required|string|unique:branches,code,' . $branch->id,
            'name' => 'required|string',
            'address' => 'required|string',
        ]);

        // Update the branch using the validated data
        $branch->update($request->all());

        // Redirect back to the branches list with a success message
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        // Delete the branch
        $branch->delete();

        // Redirect back to the branches list with a success message
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}