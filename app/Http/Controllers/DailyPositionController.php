<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyPositionRequest;
use App\Http\Requests\UpdateDailyPositionRequest;
use App\Models\DailyPosition;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Support\Carbon;

// Import Carbon for date handling
use Illuminate\Http\Request;

class DailyPositionController extends Controller
{
    // Constructor to apply authentication middleware


    // Display a list of daily positions with pagination
    public function index()
    {
        $dailyPositions = null;
        $user = auth()->user();

        // Check if user has any valid dashboard role
        if (!$user->hasAnyRole(['branch', 'region', 'division', 'head-office', 'super-admin'])) {
            abort(403); // Forbidden if no valid role
        }

        if ($user->roles->first()->name == 'branch') {

            $dailyPositions = DailyPosition::with('branch')
                ->whereIn('branch_id', [$user->branch_id])
                ->latest()
                ->paginate(10);


        } elseif($user->roles->first()->name == 'region'){


            $branches_ids = $user->branch?->region?->branches?->pluck('id')->toArray();

            $dailyPositions = DailyPosition::with('branch')
                ->whereIn('branch_id', $branches_ids)
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
    public function store(StoreDailyPositionRequest $request)
    {


        // Auto-assign branch_id and date
        $data = $request->all();
        $data['branch_id'] = auth()->user()->branch_id; // Get branch_id from the authenticated user
        $data['date'] = Carbon::today(); // Automatically set today's date
        $data['created_by_user_id'] = auth()->id(); // Assign the authenticated user as the creator

        // Convert numbers to 3 decimal places
        $data['consumer'] = number_format($data['consumer'], 3);
        $data['commercial'] = number_format($data['commercial'], 3);
        $data['micro'] = number_format($data['micro'], 3);
        $data['agri'] = number_format($data['agri'], 3);

        // Calculate total assets
        $data['totalAssets'] = number_format($data['consumer'] + $data['commercial'] + $data['micro'] + $data['agri'], 3);

        // Create the new daily position record
        DailyPosition::create($data);

        // Redirect with success message
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
    public function update(UpdateDailyPositionRequest $request, DailyPosition $dailyPosition)
    {
        // Get all the validated data from the form
        $data = $request->validated();

        // Set the branch_id and date for the update
        $data['branch_id'] = auth()->user()->branch_id; // This is for the current user
        $data['date'] = $dailyPosition->date ?? Carbon::today(); // If no date is provided, use today's date
        $data['updated_by_user_id'] = auth()->id(); // Who is updating the record?

        // Update the record with the new data
        $dailyPosition->update($data);

        // Redirect to the list with a success message
        return redirect()->route('daily-positions.index')
            ->with('success', 'Daily position updated successfully.');
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
