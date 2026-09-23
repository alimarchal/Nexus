<?php

namespace App\Services;

use App\Models\Aksic;
use App\Models\AksicBudget;
use App\Models\AksicBudgetAllocation;
use App\Models\AksicRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * AKSIC markup budget: position, limit checks at approval, and every change
 * to the budget (always written to the revisions ledger).
 *
 * Utilisation = markup (aksics.total_interest) of approved cases.
 * Limits are checked at three levels for the case's district:
 *   1. district allocation
 *   2. gender bucket   = allocation x rule % (Male 48 / Female 48 / Special 2 / Transgender 2)
 *   3. business nature = allocation x budget % (Existing 25 / New 75)
 */
class AksicBudgetService
{
    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'special' => 'Special Person',
        'transgender' => 'Transgender',
    ];

    public const NATURES = [
        'existing' => 'Existing',
        'new' => 'New',
    ];

    // ------------------------------------------------------------------ position

    /**
     * District-wise budget position.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, totals: array<string, mixed>}
     */
    public function position(AksicBudget $budget, ?string $excludeAksicId = null): array
    {
        $budget->loadMissing(['allocations.district', 'allocations.rule']);
        $usage = $this->usage($excludeAksicId);

        $rows = $budget->allocations
            ->sortByDesc('allocated_amount')
            ->map(function (AksicBudgetAllocation $allocation) use ($budget, $usage): array {
                $allocated = (float) $allocation->allocated_amount;
                $rule = $allocation->rule ?? AksicRule::query()->where('district_id', $allocation->district_id)->first();
                $used = $usage->get($allocation->district_id, []);

                $genderPct = [
                    'male' => (float) ($rule?->male_percentage ?? 48),
                    'female' => (float) ($rule?->female_percentage ?? 48),
                    'special' => (float) ($rule?->special_person_percentage ?? 2),
                    'transgender' => (float) ($rule?->transgender_percentage ?? 2),
                ];
                $naturePct = [
                    'existing' => (float) $budget->existing_business_percentage,
                    'new' => (float) $budget->new_business_percentage,
                ];

                $bucket = fn (float $limit, float $spent): array => [
                    'allocated' => round($limit, 2),
                    'used' => round($spent, 2),
                    'remaining' => round($limit - $spent, 2),
                    'percent' => $limit > 0 ? round($spent / $limit * 100, 2) : ($spent > 0 ? 100.0 : 0.0),
                ];

                $districtUsed = (float) ($used['total'] ?? 0);

                return [
                    'allocation' => $allocation,
                    'district' => $allocation->district?->name ?? $rule?->district_name ?? '—',
                    'population_percentage' => (float) ($rule?->population_percentage ?? 0),
                    'loans' => (int) ($used['loans'] ?? 0),
                    'total' => $bucket($allocated, $districtUsed),
                    'genders' => collect($genderPct)->map(fn ($pct, $key) => $bucket($allocated * $pct / 100, (float) ($used['gender'][$key] ?? 0)))->all(),
                    'natures' => collect($naturePct)->map(fn ($pct, $key) => $bucket($allocated * $pct / 100, (float) ($used['nature'][$key] ?? 0)))->all(),
                ];
            })
            ->values();

        $sum = fn (string $path) => round($rows->sum(fn ($row) => data_get($row, $path)), 2);
        $allocatedTotal = $sum('total.allocated');

        return [
            'rows' => $rows,
            'totals' => [
                'budget' => (float) $budget->total_amount,
                'allocated' => $allocatedTotal,
                'unallocated' => round((float) $budget->total_amount - $allocatedTotal, 2),
                'used' => $sum('total.used'),
                'remaining' => round($allocatedTotal - $sum('total.used'), 2),
                'loans' => (int) $rows->sum('loans'),
                'genders' => collect(self::GENDERS)->map(fn ($l, $k) => ['allocated' => $sum("genders.$k.allocated"), 'used' => $sum("genders.$k.used"), 'remaining' => $sum("genders.$k.remaining")])->all(),
                'natures' => collect(self::NATURES)->map(fn ($l, $k) => ['allocated' => $sum("natures.$k.allocated"), 'used' => $sum("natures.$k.used"), 'remaining' => $sum("natures.$k.remaining")])->all(),
            ],
        ];
    }

    /**
     * Markup of approved cases per district, gender bucket and business nature.
     *
     * @return Collection<int, array{total: float, loans: int, gender: array<string, float>, nature: array<string, float>}>
     */
    private function usage(?string $excludeAksicId = null): Collection
    {
        $rows = Aksic::query()
            ->where('status', 'Approved')
            ->whereNotNull('total_interest')
            ->when($excludeAksicId, fn ($q, $id) => $q->whereKeyNot($id))
            ->selectRaw('district_id, quota, business_type, count(*) as loans, sum(total_interest) as markup')
            ->groupBy('district_id', 'quota', 'business_type')
            ->get();

        return $rows->groupBy('district_id')->map(function (Collection $group): array {
            $out = ['total' => 0.0, 'loans' => 0, 'gender' => [], 'nature' => []];

            foreach ($group as $row) {
                $markup = (float) $row->markup;
                $gender = self::genderKey($row->quota);
                $nature = self::natureKey($row->business_type);
                $out['total'] += $markup;
                $out['loans'] += (int) $row->loans;
                $out['gender'][$gender] = ($out['gender'][$gender] ?? 0) + $markup;
                if ($nature) {
                    $out['nature'][$nature] = ($out['nature'][$nature] ?? 0) + $markup;
                }
            }

            return $out;
        });
    }

    public static function genderKey(?string $quota): string
    {
        return match ($quota) {
            'Female' => 'female',
            'Disabled', 'Special Person' => 'special',
            'Transgender' => 'transgender',
            default => 'male',
        };
    }

    public static function natureKey(?string $businessType): ?string
    {
        return match ($businessType) {
            'Existing' => 'existing',
            'New' => 'new',
            default => null,
        };
    }

    // ------------------------------------------------------------------ approval check

    /**
     * Check whether approving $aksic with $markup of schedule markup stays within
     * the active budget. The case's own previous markup is excluded, so a
     * regenerated schedule is judged on its new figure only.
     *
     * @return array{budget: ?AksicBudget, enforcement: string, breaches: array<int, array<string, mixed>>}
     */
    public function check(Aksic $aksic, float $markup): array
    {
        $budget = AksicBudget::active();

        if (! $budget) {
            return ['budget' => null, 'enforcement' => 'report', 'breaches' => []];
        }

        $position = $this->position($budget, $aksic->getKey());
        $row = $position['rows']->first(fn ($r) => (int) $r['allocation']->district_id === (int) $aksic->district_id);
        $breaches = [];

        if (! $row) {
            $breaches[] = ['scope' => ($aksic->district?->name ?? 'This district').' has no budget allocation', 'limit' => 0.0, 'used' => 0.0, 'requested' => $markup, 'shortfall' => $markup];

            return ['budget' => $budget, 'enforcement' => $budget->enforcement, 'breaches' => $breaches];
        }

        $gender = self::genderKey($aksic->quota);
        $nature = self::natureKey($aksic->business_type);
        $levels = [
            $row['district'].' — district allocation' => $row['total'],
            $row['district'].' — '.self::GENDERS[$gender].' share' => $row['genders'][$gender],
        ];
        if ($nature) {
            $levels[$row['district'].' — '.self::NATURES[$nature].' business share'] = $row['natures'][$nature];
        }

        foreach ($levels as $scope => $bucket) {
            if ($bucket['used'] + $markup > $bucket['allocated'] + 0.005) {
                $breaches[] = [
                    'scope' => $scope,
                    'limit' => $bucket['allocated'],
                    'used' => $bucket['used'],
                    'requested' => round($markup, 2),
                    'shortfall' => round($bucket['used'] + $markup - $bucket['allocated'], 2),
                ];
            }
        }

        return ['budget' => $budget, 'enforcement' => $budget->enforcement, 'breaches' => $breaches];
    }

    // ------------------------------------------------------------------ changes

    /**
     * Create a budget version with allocations = total x population % of each
     * active rule. Optionally activate it (deactivating the current one).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, bool $activate): AksicBudget
    {
        return DB::transaction(function () use ($data, $activate): AksicBudget {
            $budget = AksicBudget::create($data + ['is_active' => false]);

            foreach (AksicRule::query()->where('is_active', true)->get() as $rule) {
                $amount = round((float) $budget->total_amount * (float) $rule->population_percentage / 100, 2);
                $budget->allocations()->create([
                    'district_id' => $rule->district_id,
                    'aksic_rule_id' => $rule->id,
                    'allocated_amount' => $amount,
                ]);
                $this->log($budget, 'initial', $rule->district_id, 0, $amount, $data['reference_no'] ?? null, $data['reference_date'] ?? null, 'Initial allocation = total x population %.');
            }

            if ($activate) {
                $this->activate($budget, $data['reference_no'] ?? null, $data['reference_date'] ?? null, 'Activated on creation.');
            }

            return $budget->fresh();
        });
    }

    public function activate(AksicBudget $budget, ?string $referenceNo = null, ?string $referenceDate = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($budget, $referenceNo, $referenceDate, $reason): void {
            AksicBudget::query()->whereKeyNot($budget->getKey())->where('is_active', true)->update(['is_active' => false]);
            $budget->update(['is_active' => true]);
            $this->log($budget, 'activation', null, null, null, $referenceNo, $referenceDate, $reason ?? 'Budget activated.');
        });
    }

    /**
     * Update budget settings; total and percentage changes are ledgered.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSettings(AksicBudget $budget, array $data): void
    {
        DB::transaction(function () use ($budget, $data): void {
            $oldTotal = (float) $budget->total_amount;
            $before = $budget->only(['existing_business_percentage', 'new_business_percentage', 'enforcement', 'title']);

            $budget->update($data);

            if (abs($oldTotal - (float) $budget->total_amount) > 0.004) {
                $this->log($budget, 'total_change', null, $oldTotal, (float) $budget->total_amount, $data['reference_no'] ?? null, $data['reference_date'] ?? null, $data['change_reason'] ?? 'Total budget changed.');
            }

            $changes = collect($before)->filter(fn ($value, $key) => (string) $value !== (string) $budget->{$key})
                ->map(fn ($value, $key) => "{$key}: {$value} → {$budget->{$key}}")->implode('; ');
            if ($changes !== '') {
                $this->log($budget, 'settings_change', null, null, null, $data['reference_no'] ?? null, $data['reference_date'] ?? null, trim(($data['change_reason'] ?? '').' ('.$changes.')'));
            }
        });
    }

    /**
     * Enhance or reduce one district's allocation. A reduction may not take the
     * allocation below what is already used. When $adjustTotal is true the
     * budget total moves by the same amount (a fresh sanction / surrender).
     */
    public function revise(AksicBudgetAllocation $allocation, string $action, float $amount, bool $adjustTotal, ?string $referenceNo, ?string $referenceDate, string $reason): void
    {
        if (! in_array($action, ['enhancement', 'reduction'], true) || $amount <= 0) {
            throw new InvalidArgumentException('Choose enhancement or reduction with a positive amount.');
        }

        DB::transaction(function () use ($allocation, $action, $amount, $adjustTotal, $referenceNo, $referenceDate, $reason): void {
            $allocation = AksicBudgetAllocation::query()->lockForUpdate()->findOrFail($allocation->getKey());
            $budget = $allocation->budget;
            $previous = (float) $allocation->allocated_amount;
            $delta = $action === 'enhancement' ? $amount : -$amount;
            $new = round($previous + $delta, 2);

            $used = (float) ($this->usage()->get($allocation->district_id)['total'] ?? 0);
            if ($new < $used - 0.004) {
                throw new InvalidArgumentException('Reduction would take the allocation ('.number_format($new, 2).') below markup already used ('.number_format($used, 2).').');
            }

            $allocation->update(['allocated_amount' => $new]);
            $this->log($budget, $action, $allocation->district_id, $previous, $new, $referenceNo, $referenceDate, $reason);

            if ($adjustTotal) {
                $oldTotal = (float) $budget->total_amount;
                $budget->update(['total_amount' => round($oldTotal + $delta, 2)]);
                $this->log($budget, 'total_change', null, $oldTotal, (float) $budget->total_amount, $referenceNo, $referenceDate, 'Total adjusted with '.$action.' of '.($allocation->district?->name ?? 'district').'. '.$reason);
            }
        });
    }

    /**
     * Re-split the budget total across districts by population %.
     */
    public function redistribute(AksicBudget $budget, ?string $referenceNo, ?string $referenceDate, string $reason): int
    {
        return DB::transaction(function () use ($budget, $referenceNo, $referenceDate, $reason): int {
            $usage = $this->usage();
            $changed = 0;
            $problems = [];

            foreach ($budget->allocations()->with('rule', 'district')->lockForUpdate()->get() as $allocation) {
                $rule = $allocation->rule ?? AksicRule::query()->where('district_id', $allocation->district_id)->first();
                if (! $rule) {
                    continue;
                }
                $new = round((float) $budget->total_amount * (float) $rule->population_percentage / 100, 2);
                $used = (float) ($usage->get($allocation->district_id)['total'] ?? 0);
                if ($new < $used - 0.004) {
                    $problems[] = ($allocation->district?->name ?? $rule->district_name).' would fall below its used markup';

                    continue;
                }
                $previous = (float) $allocation->allocated_amount;
                if (abs($previous - $new) > 0.004) {
                    $allocation->update(['allocated_amount' => $new]);
                    $this->log($budget, 'redistribution', $allocation->district_id, $previous, $new, $referenceNo, $referenceDate, $reason);
                    $changed++;
                }
            }

            if ($problems) {
                throw new InvalidArgumentException('Redistribution stopped: '.implode('; ', $problems).'.');
            }

            return $changed;
        });
    }

    private function log(AksicBudget $budget, string $action, ?int $districtId, ?float $previous, ?float $new, ?string $referenceNo, ?string $referenceDate, ?string $reason): void
    {
        $budget->revisions()->create([
            'district_id' => $districtId,
            'action' => $action,
            'previous_amount' => $previous,
            'new_amount' => $new,
            'change_amount' => ($previous !== null && $new !== null) ? round($new - $previous, 2) : null,
            'reference_no' => $referenceNo,
            'reference_date' => $referenceDate,
            'reason' => $reason,
        ]);
    }
}
