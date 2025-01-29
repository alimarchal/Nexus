<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyPositionRequest;
use App\Http\Requests\UpdateDailyPositionRequest;
use App\Models\DailyPosition;
use App\Models\BranchTarget;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DailyPositionController extends Controller
{
    // Constructor to apply authentication middleware


    // Display a list of daily positions with pagination
    public function index(Request $request)
    {
        $query = DailyPosition::query()->with('branch');

    $dailyPositions = QueryBuilder::for($query)
        ->allowedFilters([
            AllowedFilter::exact('branch_id'),  // Exact filter for branch_id
            AllowedFilter::callback('date_range', function ($query, $value) {
                // Ensure the value is in the correct format "start_date,end_date"
                if ($value) {
                    // Split the range into start and end dates
                    $dates = explode(',', $value);

                    if (count($dates) === 2) {
                        $startDate = Carbon::parse(trim($dates[0]))->startOfDay(); // Parse and start at the beginning of the day
                        $endDate = Carbon::parse(trim($dates[1]))->endOfDay(); // Parse and end at the end of the day

                        // Apply the date range filter to the query
                        $query->whereBetween('date', [$startDate, $endDate]);
                    }
                }
            }),
        ])
        ->paginate(10);
        $dailyPositions = null;
        $user = auth()->user();

        // Check if user has any valid dashboard role
        if (!$user->hasAnyRole(['branch', 'region', 'division', 'head-office', 'super-admin'])) {
            abort(403); // Forbidden if no valid role
        }

        $role = $user->roles->first()->name;

        if ($role == 'branch') {

            $dailyPositions = DailyPosition::with('branch')
                ->where('branch_id', $user->branch_id)
                ->latest()
                ->paginate(10);

        } elseif ($role == 'region') {

            $branches_ids = $user->branch?->region?->branches?->pluck('id')->toArray() ?? [];

            $dailyPositions = DailyPosition::with('branch')
                ->whereIn('branch_id', $branches_ids)
                ->latest()
                ->paginate(10);

        } elseif ($role == 'division') {

            $branches_ids = $user->branch?->region?->division?->branches?->pluck('id')->toArray() ?? [];

            $dailyPositions = DailyPosition::with('branch')
                ->whereIn('branch_id', $branches_ids)
                ->latest()
                ->paginate(10);

        } elseif ($role == 'head-office') {

            $branches_ids = $user->headOffice?->branches?->pluck('id')->toArray() ?? [];

            $dailyPositions = DailyPosition::with('branch')
                ->whereIn('branch_id', $branches_ids)
                ->latest()
                ->paginate(10);

        } elseif ($role == 'super-admin') {

            $dailyPositions = DailyPosition::with('branch')
                ->latest()
                ->paginate(10);
        }




        return view('daily-positions.index', compact('dailyPositions'));
    }

    // Show the form to create a new daily position
    public function create()
    {
        $branches = Branch::all(); // Fetch all branches
        return view('daily-positions.create', compact('branches'));
    }

    // Store a newly created daily position
 // Store a newly created daily position
public function store(StoreDailyPositionRequest $request)
{
    $data = $request->all();
    $branchId = auth()->user()->branch_id; // Get branch_id from the authenticated user

    // Check if the branch target is set for the branch
    $branchTarget = BranchTarget::where('branch_id', $branchId)->first(); // Assuming BranchTarget is the model for branch target

    if (!$branchTarget) {
        // Return an error if branch target is not set
        return redirect()->back()->withErrors(['error' => 'Branch target not set. Please set the branch target first.']);
    }

    $data['branch_id'] = $branchId; // Use branch_id from the authenticated user
    $data['date'] = Carbon::today(); // Automatically set today's date
    $data['created_by_user_id'] = auth()->id(); // Assign the authenticated user as the creator

    // Check for an existing entry, including soft-deleted ones
    $existingEntry = DailyPosition::withTrashed()
        ->where('branch_id', $data['branch_id'])
        ->where('date', $data['date'])
        ->first();

    if ($existingEntry) {
        if ($existingEntry->trashed()) {
            // Permanently delete the soft-deleted record
            $existingEntry->forceDelete();
        } else {
            // If the record is not soft-deleted, return an error
            return redirect()
                ->back()
                ->withErrors(['error' => 'Data for today already exists. Delete the existing data or try again tomorrow.']);
        }
    }

    // Format numeric fields to 3 decimal places
    $data['consumer'] = number_format($data['consumer'], 3);
    $data['commercial'] = number_format($data['commercial'], 3);
    $data['micro'] = number_format($data['micro'], 3);
    $data['agri'] = number_format($data['agri'], 3);

    // Calculate total assets
    $data['totalAssets'] = number_format(
        $data['consumer'] + $data['commercial'] + $data['micro'] + $data['agri'],
        3
    );

    // Create the new daily position record
    DailyPosition::create($data);

    // Redirect to the index page with a success message
    return redirect()
        ->route('daily-positions.index')
        ->with('success', 'Daily position created successfully.');
}


    // Show the details of a single daily position
    public function show($id)
    {
        $dailyPosition = DailyPosition::findOrFail($id); // Retrieve the specific record by ID
        return view('daily-positions.view', compact('dailyPosition')); // Return the view with data
    }

    // Show the form to edit an existing daily position
    public function edit(DailyPosition $dailyPosition)
    {
        $branches = Branch::all(); // Fetch all branches
        return view('daily-positions.edit', [
            'dailyPosition' => $dailyPosition,
            'branches' => $branches
        ]);
    }

    // Update the details of a specific daily position
    public function update(Request $request, DailyPosition $dailyPosition)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'consumer' => 'required|numeric',
            'commercial' => 'required|numeric',
            'micro' => 'required|numeric',
            'agri' => 'required|numeric',
        ]);

        // Add additional data
        $data = $validatedData;
        $data['branch_id'] = auth()->user()->branch_id; // Current user's branch
        $data['date'] = $dailyPosition->date; // Date cannot be changed
        $data['updated_by_user_id'] = auth()->id(); // Track who updated the record

        // Check if updating these values will violate the unique constraint
        $existingRecord = DailyPosition::where('branch_id', $data['branch_id'])
            ->where('date', $data['date'])
            ->where('id', '!=', $dailyPosition->id) // Exclude the current record
            ->first();

        if ($existingRecord) {
            return redirect()->back()->withErrors([
               'error' => 'This record cannot be updated as more than 24 hours have passed since its creation.'


            ]);
        }

        // Convert numbers to 3 decimal places
        $data['consumer'] = number_format($data['consumer'], 3, '.', '');
        $data['commercial'] = number_format($data['commercial'], 3, '.', '');
        $data['micro'] = number_format($data['micro'], 3, '.', '');
        $data['agri'] = number_format($data['agri'], 3, '.', '');

        // Recalculate total assets
        $data['totalAssets'] = number_format(
            $data['consumer'] + $data['commercial'] + $data['micro'] + $data['agri'],
            3,
            '.',
            ''
        );

        // Update the record with the new data
        $dailyPosition->update($data);

        // Redirect to the list with a success message
        return redirect()->route('daily-positions.index')->with('success', 'Daily position updated successfully.');
    }



    // Delete a specific daily position
    public function destroy(DailyPosition $dailyPosition)
    {
        // Soft delete the record
        $dailyPosition->delete();

        // Redirect with success message
        return redirect()
            ->route('daily-positions.index')
            ->with('success', 'Daily position deleted successfully.');
    }
}