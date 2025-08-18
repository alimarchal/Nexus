<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintStatusTypeRequest;
use App\Http\Requests\UpdateComplaintStatusTypeRequest;
use App\Models\ComplaintStatusType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\QueryBuilder\QueryBuilder;

class ComplaintStatusTypeController extends Controller
{
    public function index(): View
    {
        $statusTypes = QueryBuilder::for(ComplaintStatusType::class)
            ->allowedFilters(ComplaintStatusType::getAllowedFilters())
            ->allowedSorts(ComplaintStatusType::getAllowedSorts())
            ->defaultSort('-created_at')
            ->paginate(15);

        return view('complaint-status-types.index', compact('statusTypes'));
    }

    public function create(): View
    {
        return view('complaint-status-types.create');
    }

    public function store(StoreComplaintStatusTypeRequest $request): RedirectResponse
    {
        ComplaintStatusType::create($request->validated());

        return redirect()->route('complaint-status-types.index')
            ->with('success', 'Status type created successfully');
    }

    public function show(ComplaintStatusType $complaintStatusType): View
    {
        return view('complaint-status-types.show', compact('complaintStatusType'));
    }

    public function edit(ComplaintStatusType $complaintStatusType): View
    {
        return view('complaint-status-types.edit', compact('complaintStatusType'));
    }

    public function update(UpdateComplaintStatusTypeRequest $request, ComplaintStatusType $complaintStatusType): RedirectResponse
    {
        $complaintStatusType->update($request->validated());

        return redirect()->route('complaint-status-types.index')
            ->with('success', 'Status type updated successfully');
    }

    public function destroy(ComplaintStatusType $complaintStatusType): RedirectResponse
    {
        $complaintStatusType->delete();

        return redirect()->route('complaint-status-types.index')
            ->with('success', 'Status type deleted successfully');
    }
}