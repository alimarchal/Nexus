<?php

namespace App\Http\Controllers;

use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\DailyPosition;
use App\Models\Division;
use App\Models\PrintedStationery;
use App\Models\Region;
use App\Models\Report;
use App\Models\StationeryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view reports', only: ['index', 'show', 'aksicRulesReport']),
            new Middleware('role_or_permission:generate reports', only: ['generate', 'generateDailyPositions', 'generateStationeryReport']),
            new Middleware('role_or_permission:export reports', only: ['export', 'exportDailyPositions']),
        ];
    }

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
            ->with(['branches' => function ($query) use ($request) {
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

    public function aksicRulesReport(Request $request)
    {
        $rules = AksicRule::query()
            ->where('is_active', true)
            ->withCount('aksics')
            ->withSum('aksics as principal_amount_sum', 'principal_amount')
            ->orderBy('district_name')
            ->get();

        $interestByRule = DB::table('aksic_rules')
            ->leftJoin('aksics', 'aksics.aksic_rule_id', '=', 'aksic_rules.id')
            ->leftJoin('aksic_amortizations', 'aksic_amortizations.aksic_id', '=', 'aksics.id')
            ->whereNull('aksic_rules.deleted_at')
            ->where('aksic_rules.is_active', true)
            ->groupBy('aksic_rules.id')
            ->select('aksic_rules.id', DB::raw('COALESCE(SUM(aksic_amortizations.total_interest), 0) as interest_sum'))
            ->pluck('interest_sum', 'id');

        $actualQuotaCountsByRule = DB::table('aksics')
            ->select('aksic_rule_id', 'quota', 'gender', DB::raw('COUNT(*) as loans_count'))
            ->whereNotNull('aksic_rule_id')
            ->whereNull('deleted_at')
            ->groupBy('aksic_rule_id', 'quota', 'gender')
            ->get()
            ->groupBy('aksic_rule_id');

        $reportRows = $rules->map(function (AksicRule $rule) use ($interestByRule, $actualQuotaCountsByRule): array {
            $principalAmountCents = $this->decimalAmountToCents($rule->principal_amount_sum ?? 0);
            $interestAmountCents = $this->decimalAmountToCents($interestByRule[$rule->id] ?? 0);
            $principalAmount = $this->centsToDecimal($principalAmountCents);
            $interestAmount = $this->centsToDecimal($interestAmountCents);
            $loansDone = (int) $rule->aksics_count;
            $remaining = max(0, $rule->proposed_beneficiaries - $loansDone);
            $quotaCounts = $this->allocateAksicGenderQuota($rule);
            $actualQuotaCounts = $this->actualAksicQuotaCounts($actualQuotaCountsByRule->get($rule->id, collect()));

            return [
                'district' => $rule->district_name,
                'population_basis_points' => $this->decimalPercentToBasisPoints((string) $rule->population_percentage),
                'population_percentage' => (float) $rule->population_percentage,
                'proposed_beneficiaries' => $rule->proposed_beneficiaries,
                'male_beneficiaries' => $quotaCounts['male'],
                'female_beneficiaries' => $quotaCounts['female'],
                'disabled_beneficiaries' => $quotaCounts['disabled'],
                'transgender_beneficiaries' => $quotaCounts['transgender'],
                'actual_male_loans' => $actualQuotaCounts['male'],
                'actual_female_loans' => $actualQuotaCounts['female'],
                'actual_disabled_male_loans' => $actualQuotaCounts['disabled_male'],
                'actual_disabled_female_loans' => $actualQuotaCounts['disabled_female'],
                'actual_transgender_loans' => $actualQuotaCounts['transgender'],
                'loans_done' => $loansDone,
                'remaining' => $remaining,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'total_payable' => $this->centsToDecimal($principalAmountCents + $interestAmountCents),
            ];
        });

        $reportRows = $this->normalizeAksicPopulationPercentages($reportRows);
        $populationPercentageTotal = $reportRows->sum('population_percentage');
        $financialTotals = $this->normalizeAksicFinancialTotals($reportRows);

        $totals = [
            'population_percentage' => abs($populationPercentageTotal - 100) <= 0.05 ? 100 : $populationPercentageTotal,
            'proposed_beneficiaries' => $reportRows->sum('proposed_beneficiaries'),
            'male_beneficiaries' => $reportRows->sum('male_beneficiaries'),
            'female_beneficiaries' => $reportRows->sum('female_beneficiaries'),
            'disabled_beneficiaries' => $reportRows->sum('disabled_beneficiaries'),
            'transgender_beneficiaries' => $reportRows->sum('transgender_beneficiaries'),
            'actual_male_loans' => $reportRows->sum('actual_male_loans'),
            'actual_female_loans' => $reportRows->sum('actual_female_loans'),
            'actual_disabled_male_loans' => $reportRows->sum('actual_disabled_male_loans'),
            'actual_disabled_female_loans' => $reportRows->sum('actual_disabled_female_loans'),
            'actual_transgender_loans' => $reportRows->sum('actual_transgender_loans'),
            'loans_done' => $reportRows->sum('loans_done'),
            'remaining' => $reportRows->sum('remaining'),
            'principal_amount' => $financialTotals['principal_amount'],
            'interest_amount' => $financialTotals['interest_amount'],
            'total_payable' => $financialTotals['total_payable'],
        ];

        $chartData = [
            'districts' => $reportRows->pluck('district')->values(),
            'proposed' => $reportRows->pluck('proposed_beneficiaries')->values(),
            'loansDone' => $reportRows->pluck('loans_done')->values(),
            'maleBeneficiaries' => $reportRows->pluck('male_beneficiaries')->values(),
            'femaleBeneficiaries' => $reportRows->pluck('female_beneficiaries')->values(),
            'disabledBeneficiaries' => $reportRows->pluck('disabled_beneficiaries')->values(),
            'transgenderBeneficiaries' => $reportRows->pluck('transgender_beneficiaries')->values(),
            'actualMaleLoans' => $reportRows->pluck('actual_male_loans')->values(),
            'actualFemaleLoans' => $reportRows->pluck('actual_female_loans')->values(),
            'actualDisabledMaleLoans' => $reportRows->pluck('actual_disabled_male_loans')->values(),
            'actualDisabledFemaleLoans' => $reportRows->pluck('actual_disabled_female_loans')->values(),
            'actualTransgenderLoans' => $reportRows->pluck('actual_transgender_loans')->values(),
            'principalAmounts' => $reportRows->pluck('principal_amount')->map(fn ($value) => round($value, 2))->values(),
            'interestAmounts' => $reportRows->pluck('interest_amount')->map(fn ($value) => round($value, 2))->values(),
        ];

        return view('reports.aksic-rules-report', compact('reportRows', 'totals', 'chartData'));
    }

    private function allocateAksicGenderQuota(AksicRule $rule): array
    {
        $male = (int) round($rule->proposed_beneficiaries * ((float) $rule->male_percentage / 100));
        $female = (int) round($rule->proposed_beneficiaries * ((float) $rule->female_percentage / 100));
        $disabled = (int) round($rule->proposed_beneficiaries * ((float) $rule->special_person_percentage / 100));
        $transgender = (int) round($rule->proposed_beneficiaries * ((float) $rule->transgender_percentage / 100));
        $difference = $rule->proposed_beneficiaries - ($male + $female + $disabled + $transgender);

        $transgender += $difference;

        return [
            'male' => $male,
            'female' => $female,
            'disabled' => max(0, $disabled),
            'transgender' => max(0, $transgender),
        ];
    }

    private function actualAksicQuotaCounts($rows): array
    {
        $counts = [
            'male' => 0,
            'female' => 0,
            'disabled_male' => 0,
            'disabled_female' => 0,
            'transgender' => 0,
        ];

        foreach ($rows as $row) {
            $quota = (string) $row->quota;
            $gender = (string) $row->gender;
            $count = (int) $row->loans_count;

            if ($quota === 'Male') {
                $counts['male'] += $count;
            } elseif ($quota === 'Female') {
                $counts['female'] += $count;
            } elseif ($quota === 'Transgender') {
                $counts['transgender'] += $count;
            } elseif (in_array($quota, ['Disabled', 'Special Person'], true)) {
                if ($gender === 'Female') {
                    $counts['disabled_female'] += $count;
                } else {
                    $counts['disabled_male'] += $count;
                }
            }
        }

        return $counts;
    }

    private function normalizeAksicPopulationPercentages($reportRows)
    {
        $basisPointTotal = $reportRows->sum('population_basis_points');
        $difference = 10000 - $basisPointTotal;

        if ($reportRows->isEmpty() || abs($difference) > 5 || $difference === 0) {
            return $reportRows->map(function (array $row): array {
                $row['population_percentage'] = $row['population_basis_points'] / 100;

                return $row;
            });
        }

        $lastIndex = $reportRows->keys()->last();
        $lastRow = $reportRows[$lastIndex];
        $lastRow['population_basis_points'] += $difference;
        $reportRows[$lastIndex] = $lastRow;

        return $reportRows->map(function (array $row): array {
            $row['population_percentage'] = $row['population_basis_points'] / 100;

            return $row;
        });
    }

    private function decimalPercentToBasisPoints(string $percentage): int
    {
        $normalized = trim($percentage);
        [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '0');
        $whole = preg_replace('/[^0-9-]/', '', $whole) ?: '0';
        $decimal = preg_replace('/[^0-9]/', '', $decimal) ?: '0';

        return ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');
    }

    private function decimalAmountToCents(string|int|float|null $amount): int
    {
        if ($amount === null) {
            return 0;
        }

        $normalized = is_float($amount)
            ? number_format($amount, 2, '.', '')
            : trim((string) $amount);
        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '-');
        [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '0');
        $whole = preg_replace('/[^0-9]/', '', $whole) ?: '0';
        $decimal = preg_replace('/[^0-9]/', '', $decimal) ?: '0';
        $cents = ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function centsToDecimal(int $cents): float
    {
        return $cents / 100;
    }

    private function normalizeAksicFinancialTotals($reportRows): array
    {
        if ($reportRows->isEmpty()) {
            return [
                'principal_amount' => 0.0,
                'interest_amount' => 0.0,
                'total_payable' => 0.0,
            ];
        }

        $principalCents = $reportRows->sum(fn (array $row): int => $this->decimalAmountToCents($row['principal_amount']));
        $interestCents = $reportRows->sum(fn (array $row): int => $this->decimalAmountToCents($row['interest_amount']));

        return [
            'principal_amount' => $this->centsToDecimal($principalCents),
            'interest_amount' => $this->centsToDecimal($interestCents),
            'total_payable' => $this->centsToDecimal($principalCents + $interestCents),
        ];
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
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
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
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC',
        ];

        // Generate date range description
        $dateRangeText = $allMonths[$startMonth].' 1, '.$year;
        if ($startMonth != $endMonth) {
            $lastDay = Carbon::create($year, $endMonth)->endOfMonth()->day;
            $dateRangeText .= ' - '.$allMonths[$endMonth].' '.$lastDay.', '.$year;
        } else {
            $lastDay = Carbon::create($year, $endMonth)->endOfMonth()->day;
            $dateRangeText .= ' - '.$allMonths[$endMonth].' '.$lastDay.', '.$year;
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
                'monthly_data' => [],
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
                    $monthlyData[$item->printed_stationery_id]['monthly_data'][$item->month] = (int) $item->quantity;
                }
            }
        } catch (\Exception $e) {
            // Log the error
            // \Log::error('Error fetching stationery distribution data: ' . $e->getMessage());
        }

        // Get entity name for header display
        $selectedEntityName = 'All';
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
