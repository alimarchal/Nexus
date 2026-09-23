<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DistrictController extends Controller implements HasMiddleware
{
    /**
     * Districts are part of the region set-up, so they use the region
     * permissions (there are no separate "districts" permissions).
     * Previously any signed-in user could add or edit districts.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view regions', only: ['index', 'show']),
            new Middleware('role_or_permission:create regions', only: ['create', 'store']),
            new Middleware('role_or_permission:edit regions', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete regions', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = District::query()->with('region')->withCount('branches')->orderBy('name');

        if ($name = $request->input('filter.name')) {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($regionId = $request->input('filter.region_id')) {
            $query->where('region_id', $regionId);
        }

        $districts = $query->paginate(25)->withQueryString();
        $regions = Region::orderBy('name')->get();

        return view('districts.index', compact('districts', 'regions'));
    }

    public function create()
    {
        $regions = Region::orderBy('name')->get();

        return view('districts.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255|unique:districts,name',
        ]);

        District::create($validated);

        return redirect()->route('districts.index')->with('success', 'District created successfully.');
    }

    public function edit(District $district)
    {
        $regions = Region::orderBy('name')->get();

        return view('districts.edit', compact('district', 'regions'));
    }

    public function update(Request $request, District $district)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255|unique:districts,name,'.$district->id,
        ]);

        $district->update($validated);

        return redirect()->route('districts.index')->with('success', 'District updated successfully.');
    }

    public function destroy(District $district)
    {
        if ($district->branches()->exists()) {
            return redirect()->route('districts.index')->with('error', 'This district still has branches. Move them first.');
        }

        $district->delete();

        return redirect()->route('districts.index')->with('success', 'District deleted successfully.');
    }
}
