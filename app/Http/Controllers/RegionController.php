<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;


class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */public function index(Request $request)
{
    $regions = Region::query();

    // Apply filters, if any
    if ($request->has('filter.name')) {
        $regions->where('name', 'like', '%' . $request->input('filter.name') . '%');
    }

    if ($request->has('filter.created_at')) {
        $regions->whereDate('created_at', $request->input('filter.created_at'));
    }

    // Paginate the results
    $regions = $regions->paginate(10);

    return view('regions.index', compact('regions'));
}




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('regions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name',
        ]);

        Region::create($request->only('name'));

        return redirect()->route('regions.index')->with('success', 'Region created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Region $region)
    {
        return view('regions.edit', compact('region'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id,
        ]);

        $region->update($request->only('name'));

        return redirect()->route('regions.index')->with('success', 'Region updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Region deleted successfully.');
    }
}