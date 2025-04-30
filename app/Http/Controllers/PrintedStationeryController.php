<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintedStationeryRequest;
use App\Http\Requests\UpdatePrintedStationeryRequest;
use App\Models\PrintedStationery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PrintedStationeryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $stationeries = QueryBuilder::for(PrintedStationery::class)
            ->allowedFilters([
                AllowedFilter::partial('item_code'),
                AllowedFilter::partial('name'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(['item_code', 'name', 'created_at'])
            ->with(['creator']) // Eager load relationships
            ->paginate(100)
            ->withQueryString();

        return view('printed-stationeries.index', compact('stationeries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('printed-stationeries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrintedStationeryRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            PrintedStationery::create($request->validated());

            DB::commit();

            return redirect()->route('printed-stationeries.index')
                ->with('success', 'Printed stationery created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while creating the stationery: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PrintedStationery $printedStationery): View
    {
        return view('printed-stationeries.show', compact('printedStationery'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrintedStationery $printedStationery)
    {
        return view('printed-stationeries.edit', compact('printedStationery'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrintedStationeryRequest $request, PrintedStationery $printedStationery): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $printedStationery->update($request->validated());

            DB::commit();

            return redirect()->route('printed-stationeries.index')
                ->with('success', 'Printed stationery updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while updating the stationery: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PrintedStationery $printedStationery): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $printedStationery->delete();

            DB::commit();

            return redirect()->route('printed-stationeries.index')
                ->with('success', 'Printed stationery deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'An error occurred while deleting the stationery: ' . $e->getMessage(),
            ]);
        }
    }
}
