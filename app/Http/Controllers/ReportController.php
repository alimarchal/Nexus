<?php

namespace App\Http\Controllers;

use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Report;
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
            new Middleware('role_or_permission:generate reports', only: ['generate']),
            new Middleware('role_or_permission:export reports', only: ['export']),
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
        // Applications = every live case (the district / quota caps count these);
        // loans done, amount and markup = approved cases only.
        $approved = fn ($query) => $query->where('status', 'Approved');
        $rules = AksicRule::query()
            ->where('is_active', true)
            ->withCount(['aksics', 'aksics as approved_count' => $approved])
            ->withSum(['aksics as principal_amount_sum' => $approved], 'principal_amount')
            ->withSum(['aksics as interest_amount_sum' => $approved], 'total_interest')
            ->orderBy('district_name')
            ->get();

        $actualQuotaCountsByRule = DB::table('aksics')
            ->select('aksic_rule_id', 'quota', 'gender', DB::raw('COUNT(*) as loans_count'))
            ->whereNotNull('aksic_rule_id')
            ->whereNull('deleted_at')
            ->groupBy('aksic_rule_id', 'quota', 'gender')
            ->get()
            ->groupBy('aksic_rule_id');

        $reportRows = $rules->map(function (AksicRule $rule) use ($actualQuotaCountsByRule): array {
            $principalAmountCents = $this->decimalAmountToCents($rule->principal_amount_sum ?? 0);
            // total_interest keeps 6 decimals: round to paisa (not truncate) so totals match the budget page.
            $interestAmountCents = $this->decimalAmountToCents(number_format((float) ($rule->interest_amount_sum ?? 0), 2, '.', ''));
            $principalAmount = $this->centsToDecimal($principalAmountCents);
            $interestAmount = $this->centsToDecimal($interestAmountCents);
            $applications = (int) $rule->aksics_count;
            $loansDone = (int) $rule->approved_count;
            $remaining = max(0, $rule->proposed_beneficiaries - $applications);
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
                'applications' => $applications,
                'loans_done' => $loansDone,
                'pending' => $applications - $loansDone,
                'remaining' => $remaining,
                'male_percentage' => (float) $rule->male_percentage,
                'female_percentage' => (float) $rule->female_percentage,
                'special_percentage' => (float) $rule->special_person_percentage,
                'transgender_percentage' => (float) $rule->transgender_percentage,
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
            'applications' => $reportRows->sum('applications'),
            'loans_done' => $reportRows->sum('loans_done'),
            'pending' => $reportRows->sum('pending'),
            'remaining' => $reportRows->sum('remaining'),
            'principal_amount' => $financialTotals['principal_amount'],
            'interest_amount' => $financialTotals['interest_amount'],
            'total_payable' => $financialTotals['total_payable'],
        ];

        $chartData = [
            'districts' => $reportRows->pluck('district')->values(),
            'proposed' => $reportRows->pluck('proposed_beneficiaries')->values(),
            'applications' => $reportRows->pluck('applications')->values(),
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
}
