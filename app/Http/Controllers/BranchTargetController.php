<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchTargetRequest;
use App\Http\Requests\UpdateBranchTargetRequest;
use App\Models\BranchTarget;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchTargetController extends Controller
{
    /**
     * Display paginated branch targets with relationships.
     */
    public function index(): View
    {
        $branchTargets = BranchTarget::with(['branch', 'createdByUser'])
            ->latest()
            ->paginate(10);

        return view('branch-targets.index', compact('branchTargets'));
    }

    /**
     * Show create form with required data.
     */
    public function create(): View
    {
        $branches = Branch::pluck('name', 'id');
        return view('branch-targets.create', compact('branches'));
    }

    /**
     * Store validated branch target.
     */
    public function store(StoreBranchTargetRequest $request): RedirectResponse
    {
        $branchTarget = BranchTarget::create($request->validated());

        return redirect()
            ->route('branch-targets.show', $branchTarget)
            ->with('success', 'Branch target created successfully.');
    }

    /**
     * Display branch target details with relationships.
     */
    public function show(BranchTarget $branchTarget): View
    {
        $branchTarget->load(['branch', 'createdByUser', 'updatedByUser']);
        return view('branch-targets.show', compact('branchTarget'));
    }

    /**
     * Show edit form with current data.
     */
    public function edit(BranchTarget $branchTarget): View
    {
        $branches = Branch::pluck('name', 'id');
        return view('branch-targets.edit', compact('branchTarget', 'branches'));
    }

    /**
     * Update branch target with validated data.
     */
    public function update(UpdateBranchTargetRequest $request, BranchTarget $branchTarget): RedirectResponse
    {
        $branchTarget->update($request->validated());

        return redirect()
            ->route('branch-targets.show', $branchTarget)
            ->with('success', 'Branch target updated successfully.');
    }

    /**
     * Soft delete branch target.
     */
    public function destroy(BranchTarget $branchTarget): RedirectResponse
    {
        $branchTarget->delete();

        return redirect()
            ->route('branch-targets.index')
            ->with('success', 'Branch target deleted successfully.');
    }
}
