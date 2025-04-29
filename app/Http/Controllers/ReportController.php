<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyPosition;
use App\Models\Division;
use App\Models\PrintedStationery;
use App\Models\Region;
use App\Models\Report;
use App\Models\StationeryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportController extends Controller
{
    // Main report index
    public function index(Request $request)
    {
        $branches = Branch::all(); // Fetch all branches from database
        $reports = Report::query();

        if ($request->filled('date')) {
            $reports->where('date', $request->date);
        }

        if ($request->filled('branch_id')) {
            $reports->where('branch_id', $request->branch_id);
        }

        if ($request->filled('branch_code')) {
            $reports->where('branch_code', $request->branch_code);
        }
        if ($request->filled('branch_name')) {
            $reports->where('branch_name', $request->branch_code);
        }

        return view('reports.index', [
            'reports' => $reports->get(),
            'branches' => $branches,
        ]);
    }

    // Display the daily position report
    public function dailyPositionReport()
    {
        $date = request('filter.date', Carbon::now()->format('Y-m-d'));
        $data = [];
        $i = 1;
        foreach (Branch::all() as $branch) {

            $data[$i] = [
                'branch_id' => $branch->id,
                'date' => Carbon::parse($date)->format('d-M-Y'),
                'branchName' => $branch->name,
                'branchCode' => $branch->code,
                'status' => 'Missing',
            ];
            $daily_position_status = DailyPosition::where('branch_id', $branch->id)->where('date', $date)->get();
            if ($daily_position_status->isNotEmpty()) {
                $data[$i]['status'] = 'OK';
            }
            $i++;
        }

        return view('reports.daily-position-reports', compact('data'));
    }

    public function depositadvancesPositionReport(Request $request)
    {
        // Fetch regions with branches and aggregate sums of deposit and advances, and count of branches
        $regions = Region::withCount('branches') // Add branch count
            ->with(['branches' => function($query) use ($request) {
                // Apply filters (if any)
                if ($request->has('filter.branch_id')) {
                    $query->where('id', $request->input('filter.branch_id'));
                }

                // Optionally filter by date if needed
                if ($request->has('filter.date')) {
                    $query->whereDate('created_at', $request->input('filter.date'));
                }
            }])
            ->get();  // Get all regions with the required relationships

        // Process the data for the view
        $dailyPositions = $regions->map(function ($region) {
            // Aggregate data for each region
            $region->deposit_sum = $region->branches->sum('deposit');
            $region->advances_sum = $region->branches->sum('advances');
            return $region;
        });

        return view('reports.deposit-advances-reports-region', compact('dailyPositions'));
    }

    public function depositadvancesregionPositionReport()
    {
        return view('reports.deposit-advances-reports-branch'); // Render the branch settings view
    }

    public function accountsbranchwisePositionReport()
    {
        return view('reports.accounts-branchwise-reports'); // Render the branch settings view

    }
    public function accountsregionwisePositionReport()
    {
        return view('reports.accounts-regionwise-reports'); // Render the branch settings view
    }
    public function stationarybranchwisereport()
    {

        $transactions = QueryBuilder::for(StationeryTransaction::class)
        ->allowedFilters([
            AllowedFilter::exact('printed_stationery_id'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('stock_out_to'),
            AllowedFilter::exact('branch_id'),   // Added filter for branch
            AllowedFilter::exact('region_id'),   // Added filter for region
            AllowedFilter::exact('division_id'), // Added filter for division
            AllowedFilter::callback('date_from', function ($query, $value) {
                $query->whereDate('transaction_date', '>=', $value);
            }),
            AllowedFilter::callback('date_to', function ($query, $value) {
                $query->whereDate('transaction_date', '<=', $value);
            }),
            AllowedFilter::callback('min_quantity', function ($query, $value) {
                $query->where('quantity', '>=', $value);
            }),
            AllowedFilter::callback('max_quantity', function ($query, $value) {
                $query->where('quantity', '<=', $value);
            }),
            AllowedFilter::callback('reference', function ($query, $value) {
                $query->where('reference_number', 'like', "%{$value}%");
            }),
        ])
        ->defaultSort('-transaction_date')
        ->allowedSorts(['transaction_date', 'quantity', 'balance_after_transaction', 'type'])
        ->with(['printedStationery', 'creator', 'branch', 'region', 'division'])
        ->paginate(10)
        ->withQueryString();

    // Get data for filter dropdowns
    $stationeries = PrintedStationery::orderBy('item_code')->get();
    $branches = Branch::orderBy('name')->get();
    $regions = Region::orderBy('name')->get();
    $divisions = Division::orderBy('name')->get();

    return view('reports.stationary-branchwise-reports', compact(
        'transactions',
        'stationeries',
        'branches',
        'regions',
        'divisions'
    ));


    }
}
