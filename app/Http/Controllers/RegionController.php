<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;


class RegionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view regions', only: ['index', 'show']),
            new Middleware('role_or_permission:create regions', only: ['create', 'store']),
            new Middleware('role_or_permission:edit regions', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete regions', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $regions = Region::query()->withCount(['districts', 'branches', 'users'])->orderBy('name');

        if ($name = $request->input('filter.name')) {
            $regions->where('name', 'like', '%'.$name.'%');
        }

        if ($createdAt = $request->input('filter.created_at')) {
            $regions->whereDate('created_at', $createdAt);
        }

        $regions = $regions->paginate(25)->withQueryString();

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
        if ($region->districts()->exists() || $region->branches()->exists()) {
            return redirect()->route('regions.index')->with('error', 'This region still has districts or branches. Move them first.');
        }

        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Region deleted successfully.');
    }
}