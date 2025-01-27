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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchTargetController extends Controller
{
    /**
     * Display paginated branch targets with relationships.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check if user has any valid dashboard role
        if (!$user->hasAnyRole(['branch', 'region', 'division', 'head-office', 'super-admin'])) {
            abort(403); // Forbidden if no valid role
        }

        $role = $user->roles->first()->name;
        $branchTargetsQuery = QueryBuilder::for(BranchTarget::class)
            ->allowedFilters([
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('fiscal_year'),
                AllowedFilter::exact('created_by_user_id'),
                AllowedFilter::exact('updated_by_user_id'),
                AllowedFilter::exact('annual_target_amount'),
            ]);

        // Apply role-specific logic to filter the data
        if ($role == 'branch') {
            $branchTargetsQuery->where('branch_id', $user->branch_id);

        } elseif ($role == 'region') {
            $branchIds = $user->branch?->region?->branches?->pluck('id')->toArray() ?? [];
            $branchTargetsQuery->whereIn('branch_id', $branchIds);

        } elseif ($role == 'division') {
            $branchIds = $user->branch?->region?->division?->branches?->pluck('id')->toArray() ?? [];
            $branchTargetsQuery->whereIn('branch_id', $branchIds);

        } elseif ($role == 'head-office') {
            $branchIds = $user->headOffice?->branches?->pluck('id')->toArray() ?? [];
            $branchTargetsQuery->whereIn('branch_id', $branchIds);

        } elseif ($role == 'super-admin') {
            // No filter applied, super admin can view everything
        }

        // Execute the query with pagination
        $branchTargets = $branchTargetsQuery->paginate(10);

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
        $user = auth()->user();

        // Ensure that the authenticated user can only create targets for their own branch
        if ($request->branch_id != $user->branch_id) {
            return redirect()->route('branch-targets.index')
                ->withErrors(['branch_id' => 'You can only add targets for your own branch.'])
                ->withInput(); // This ensures the input fields are preserved
        }

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
        $user = auth()->user();

        // Ensure the user can only edit targets for their own branch
        if ($branchTarget->branch_id != $user->branch_id) {
            abort(403, 'You can only edit targets for your own branch.');
        }

        $branches = Branch::all()->pluck('code', 'id')->combine(Branch::all()->pluck('name', 'id'))->toArray();
        return view('branch-targets.edit', compact('branchTarget', 'branches'));
    }

    /**
     * Update branch target with validated data.
     */
    public function update(UpdateBranchTargetRequest $request, BranchTarget $branchTarget)
    {
        $user = auth()->user();

        // Ensure the user can only update targets for their own branch
        if ($branchTarget->branch_id != $user->branch_id) {
            return redirect()->route('branch-targets.index')
                ->withErrors(['branch_id' => 'You can only update targets for your own branch.']);
        }

        // Validate and update the branch target
        $branchTarget->update([
            'annual_target_amount' => $request->annual_target_amount,
            'target_start_date' => $request->target_start_date,
            'fiscal_year' => $request->fiscal_year,
            'branch_id' => $request->branch_id,
            'updated_by_user_id' => auth()->id(),
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
        $user = auth()->user();

        // Ensure the user can only delete targets for their own branch
        if ($branchTarget->branch_id != $user->branch_id) {
            return redirect()->route('branch-targets.index')
                ->withErrors(['branch_id' => 'You can only delete targets for your own branch.']);
        }

        $branchTarget->delete(); // Delete the record
        return redirect()->route('branch-targets.index')->with('deleted', 'Target deleted successfully!');
    }
}
