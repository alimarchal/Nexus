<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAksicApplicationRequest;
use App\Http\Requests\UpdateAksicApplicationRequest;
use App\Models\AksicApplication;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AksicApplicationController extends Controller
{
    /**
     * Display a listing of AKSIC applications with filtering capabilities
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Build query with filters using Spatie QueryBuilder
        $applications = QueryBuilder::for(AksicApplication::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),                    // Filter by status
                AllowedFilter::exact('fee_status'),               // Filter by fee status
                AllowedFilter::partial('name'),                   // Search by name
                AllowedFilter::partial('cnic'),                   // Search by CNIC
                AllowedFilter::partial('application_no'),         // Search by application number
                AllowedFilter::partial('businessName'),           // Search by business name
                AllowedFilter::partial('businessType'),           // Search by business type
                AllowedFilter::partial('district_name'),          // Filter by district
                AllowedFilter::partial('tehsil_name'),           // Filter by tehsil
                AllowedFilter::exact('tier'),                     // Filter by tier
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('amount_min', function ($query, $value) {
                    $query->where('amount', '>=', $value);
                }),
                AllowedFilter::callback('amount_max', function ($query, $value) {
                    $query->where('amount', '<=', $value);
                })
            ])
            ->with(['educations', 'statusLogs'])              // Eager load relationships
            ->latest()                                        // Order by newest first
            ->paginate(10);                                   // Paginate results

        return view('aksic-applications.index', compact('applications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAksicApplicationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAksicApplicationRequest $request, AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AksicApplication $aksicApplication)
    {
        //
    }
}
