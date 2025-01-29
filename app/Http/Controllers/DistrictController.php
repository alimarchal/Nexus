<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $districts = QueryBuilder::for(District::class)
            ->allowedFilters([
                'id',
                'name',
                'district', // Add this to the allowed filters
            ])
            ->paginate(10);

        return view('districts.index', compact('districts'));
    }

    // ... other methods (create, store, edit, etc.)
    public function create()
    {
        $regions = Region::all();
        return view('districts.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255|unique:districts,name',
        ]);

        District::create($request->all());
        return redirect()->route('districts.index')->with('success', 'District created successfully.');
    }

    public function edit(District $district)
    {
        $regions = Region::all();
        return view('districts.edit', compact('district', 'regions'));
    }

    public function update(Request $request, District $district)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255|unique:districts,name,' . $district->id,
        ]);

        $district->update($request->all());
        return redirect()->route('districts.index')->with('success', 'District updated successfully.');
    }

    public function destroy(District $district)
    {
        $district->delete();
        return redirect()->route('districts.index')->with('success', 'District deleted successfully.');
    }
}