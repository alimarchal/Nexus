<?php

namespace App\Http\Controllers;

use App\Models\DailyPosition;
use App\Models\Division;
use App\Models\PrintedStationery;
use App\Models\Region;
use App\Models\StationeryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
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

    }public function accountsregionwisePositionReport()
    {
        return view('reports.accounts-regionwise-reports'); // Render the branch settings view
    }


    public function printedStationeries(Request $request)
    {
        // Get all stationeries
        $stationeries = PrintedStationery::orderBy('id')->get();

        // Get branches, regions, and divisions for filters
        $branches = Branch::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        // Get filter parameters
        $year = $request->input('filter.year', Carbon::now()->year);
        $distributionType = $request->input('filter.distribution_type', 'branch');

        // Get date range filter parameters
        $startMonth = 1; // Default to January (full year)
        $endMonth = 12;  // Default to December (full year)
        $quarter = $request->input('filter.quarter', null);
        $dateRangeType = $request->input('filter.date_range_type', 'full_year');

        // Apply date range filters based on selection
        if ($dateRangeType == 'quarter' && $quarter) {
            // If quarter is selected, set appropriate months
            switch ($quarter) {
                case '1':
                    $startMonth = 1; // January
                    $endMonth = 3; // March
                    break;
                case '2':
                    $startMonth = 4; // April
                    $endMonth = 6; // June
                    break;
                case '3':
                    $startMonth = 7; // July
                    $endMonth = 9; // September
                    break;
                case '4':
                    $startMonth = 10; // October
                    $endMonth = 12; // December
                    break;
            }
        } elseif ($dateRangeType == 'custom') {
            // For custom range, use the provided start and end months
            $startMonth = $request->input('filter.start_month', 1);
            $endMonth = $request->input('filter.end_month', 12);

            // Make sure start month is not greater than end month
            if ($startMonth > $endMonth) {
                $temp = $startMonth;
                $startMonth = $endMonth;
                $endMonth = $temp;
            }
        }
        // else: for full_year, we use the defaults (1-12)

        // Get entity filter if specified
        $entityId = null;
        $entityColumn = null;

        if ($distributionType === 'branch' && $request->has('filter.branch_id')) {
            $entityId = $request->input('filter.branch_id');
            $entityColumn = 'branch_id';
        } elseif ($distributionType === 'region' && $request->has('filter.region_id')) {
            $entityId = $request->input('filter.region_id');
            $entityColumn = 'region_id';
        } elseif ($distributionType === 'division' && $request->has('filter.division_id')) {
            $entityId = $request->input('filter.division_id');
            $entityColumn = 'division_id';
        }

        // Get all months for display
        $allMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // Get months in range for the report (for display)
        $monthsInRange = [];
        for ($i = $startMonth; $i <= $endMonth; $i++) {
            $monthsInRange[$i] = $allMonths[$i];
        }

        // Get short month names for table header
        $shortMonthNames = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AUG',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC'
        ];

        // Generate date range description
        $dateRangeText = $allMonths[$startMonth] . ' 1, ' . $year;
        if ($startMonth != $endMonth) {
            $lastDay = Carbon::create($year, $endMonth)->endOfMonth()->day;
            $dateRangeText .= ' - ' . $allMonths[$endMonth] . ' ' . $lastDay . ', ' . $year;
        } else {
            $lastDay = Carbon::create($year, $endMonth)->endOfMonth()->day;
            $dateRangeText .= ' - ' . $allMonths[$endMonth] . ' ' . $lastDay . ', ' . $year;
        }

        // Prepare monthly distribution data
        $monthlyData = [];

        // First initialize all stationeries with zeros for all months
        foreach ($stationeries as $stationery) {
            $monthlyData[$stationery->id] = [
                'id' => $stationery->id,
                'name' => $stationery->name,
                'item_code' => $stationery->item_code,
                'distribution_entity' => $distributionType === 'branch' ? 'Branch' :
                    ($distributionType === 'region' ? 'Region' : 'Division'),
                'monthly_data' => []
            ];

            // Initialize months in the selected range with zeros
            for ($i = $startMonth; $i <= $endMonth; $i++) {
                $monthlyData[$stationery->id]['monthly_data'][$i] = 0;
            }
        }

        try {
            // Base query for fetching monthly distribution data
            $query = StationeryTransaction::select(
                'printed_stationery_id',
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(quantity) as quantity')
            )
                ->where('type', 'out')
                ->where('stock_out_to', ucfirst($distributionType))
                ->whereYear('transaction_date', $year);

            // Apply month range filter
            $query->whereRaw('MONTH(transaction_date) >= ?', [$startMonth])
                ->whereRaw('MONTH(transaction_date) <= ?', [$endMonth]);

            // Apply entity filter if specified
            if ($entityId && $entityColumn) {
                $query->where($entityColumn, $entityId);
            }

            // Group by stationery and month
            $monthlyDistribution = $query->groupBy('printed_stationery_id', DB::raw('MONTH(transaction_date)'))
                ->get();

            // Update the monthly data with actual distribution values
            foreach ($monthlyDistribution as $item) {
                if (isset($monthlyData[$item->printed_stationery_id]) &&
                    $item->month >= $startMonth && $item->month <= $endMonth) {
                    $monthlyData[$item->printed_stationery_id]['monthly_data'][$item->month] = (int)$item->quantity;
                }
            }
        } catch (\Exception $e) {
            // Log the error
            // \Log::error('Error fetching stationery distribution data: ' . $e->getMessage());
        }

        // Get entity name for header display
        $selectedEntityName = "All";
        if ($entityId) {
            if ($distributionType === 'branch' && $branch = Branch::find($entityId)) {
                $selectedEntityName = $branch->name;
            } elseif ($distributionType === 'region' && $region = Region::find($entityId)) {
                $selectedEntityName = $region->name;
            } elseif ($distributionType === 'division' && $division = Division::find($entityId)) {
                $selectedEntityName = $division->name;
            }
        }

        return view('reports.stationeries.printed-stationeries', compact(
            'stationeries',
            'monthlyData',
            'branches',
            'regions',
            'divisions',
            'year',
            'distributionType',
            'selectedEntityName',
            'startMonth',
            'endMonth',
            'quarter',
            'dateRangeText',
            'shortMonthNames',
            'monthsInRange',
            'allMonths',
            'dateRangeType'
        ));
    }

}