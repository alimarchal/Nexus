<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view branches', only: ['index', 'show']),
            new Middleware('role_or_permission:create branches', only: ['create', 'store']),
            new Middleware('role_or_permission:edit branches', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete branches', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $branches = QueryBuilder::for(Branch::class)
            ->allowedFilters([
                // One box searches code, name and address.
                AllowedFilter::callback('search', function ($query, $value) {
                    $value = trim((string) $value);
                    $query->where(fn ($q) => $q->where('code', 'like', "%{$value}%")
                        ->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('address', 'like', "%{$value}%"));
                }),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('region_id'),
                AllowedFilter::exact('district_id'),
                AllowedFilter::exact('id'),
            ])
            ->with(['region', 'district'])
            ->withCount('users')
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString();

        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        return view('branches.index', compact('branches', 'regions', 'districts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all regions and districts for the dropdown options
        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

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
            // The district must belong to the chosen region.
            'district_id' => ['required', Rule::exists('districts', 'id')->where('region_id', $request->input('region_id'))],
            'code' => 'required|string|unique:branches,code',
            'name' => 'required|string',
            'address' => 'required|string',
        ]);

        // Create the new branch using validated data
        Branch::create($request->only(['region_id', 'district_id', 'code', 'name', 'address']));

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
        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

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
            // The district must belong to the chosen region.
            'district_id' => ['required', Rule::exists('districts', 'id')->where('region_id', $request->input('region_id'))],
            'code' => 'required|string|unique:branches,code,'.$branch->id,
            'name' => 'required|string',
            'address' => 'required|string',
        ]);

        // Update the branch using the validated data
        $branch->update($request->only(['region_id', 'district_id', 'code', 'name', 'address']));

        // Redirect back to the branches list with a success message
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->users()->exists()) {
            return redirect()->route('branches.index')->with('error', 'Users are still posted at this branch. Move them first.');
        }

        $branch->delete();

        // Redirect back to the branches list with a success message
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}
