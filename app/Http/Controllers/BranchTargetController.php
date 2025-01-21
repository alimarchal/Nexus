<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchTargetRequest;
use App\Http\Requests\UpdateBranchTargetRequest;
use App\Models\BranchTarget;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;  // Added this import for date handling

class BranchTargetController extends Controller
{
    /**
     * Display paginated branch targets with relationships.
     */
    public function index(Request $request)
    {
        $query = BranchTarget::query(); // Start query

        // Apply filters if available
        if ($request->has('filter')) {
            $filters = $request->filter;

            if (!empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }

            if (!empty($filters['fiscal_year'])) {
                $query->where('fiscal_year', $filters['fiscal_year']);
            }

            if (!empty($filters['target_date_range'])) {
                $dates = explode(' - ', $filters['target_date_range']);
                if (count($dates) === 2) {
                    $query->whereBetween('target_start_date', [
                        Carbon::parse($dates[0]),
                        Carbon::parse($dates[1]),
                    ]);
                }
            }
        }

        $branchTargets = $query->paginate(10);
        return view('branch-targets.index', compact('branchTargets'));
    }

    /**
     * How create form with required data.
     */
    public function create(): View
    {
        $branches = Branch::all()->pluck('code', 'id')->combine(Branch::all()->pluck('name', 'id'))->toArray();

        return view('branch-targets.create', compact('branches'));
    }

    /**
     * Store validated branch target.
     */
    public function store(StoreBranchTargetRequest $request)
    {
        // Store the branch target
        BranchTarget::create([
            'annual_target_amount' => $request->annual_target_amount,
            'target_start_date' => $request->target_start_date,
            'fiscal_year' => $request->fiscal_year,
            'branch_id' => $request->branch_id,
            'created_by_user_id' => auth()->id(),
        ]);

        // Flash the status message to session
        session()->flash('success', 'Record successfully added!');

        return redirect()->route('branch-targets.index');
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
        $branches = Branch::all()->pluck('code', 'id')->combine(Branch::all()->pluck('name', 'id'))->toArray();

        return view('branch-targets.edit', compact('branchTarget', 'branches'));
    }

    /**
     * Update branch target with validated data.
     */
    public function update(UpdateBranchTargetRequest $request, BranchTarget $branchTarget)
    {
        // Validate and update the branch target
        $branchTarget->update([
            'annual_target_amount' => $request->annual_target_amount,
            'target_start_date' => $request->target_start_date,
            'fiscal_year' => $request->fiscal_year,
            'branch_id' => $request->branch_id,
            'updated_by_user_id' => auth()->id(),  // Example user ID, assuming you're using authentication
        ]);

        // Flash the success message to session
        session()->flash('success', 'Record successfully updated!');

        // Redirect back to the edit page with a success message
        return redirect()->route('branch-targets.edit', $branchTarget->id);
    }

    /**
     * Soft delete branch target.
     */
    public function destroy(BranchTarget $branchTarget)
    {
        $branchTarget->delete(); // Delete the record
        return redirect()->route('branch-targets.index')->with('deleted', 'Target deleted successfully!');
    }
}