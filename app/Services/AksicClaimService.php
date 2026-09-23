<?php

namespace App\Services;

use App\Models\Aksic;
use App\Models\AksicClaim;
use App\Models\AksicClaimItem;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AKSIC claim lodging (Portal Change #5).
 *
 * A claim covers a period and, optionally, one district / region / branch /
 * gender. Each approved loan with schedule instalments falling due inside the
 * period contributes the markup of those instalments. Loans already sitting in
 * a live (not rejected, not deleted) claim for an overlapping period are left
 * out so the same markup is never claimed twice.
 */
class AksicClaimService
{
    /**
     * @param  array{period_from: string, period_to: string, district_id?: ?int, region_id?: ?int, branch_id?: ?int, gender?: ?string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function eligible(array $filters): Collection
    {
        $from = $filters['period_from'];
        $to = $filters['period_to'];

        $loans = Aksic::query()
            ->where('status', 'Approved')
            ->whereHas('amortizations', fn ($q) => $this->dueWithin($q, $from, $to))
            ->when($filters['district_id'] ?? null, fn ($q, $id) => $q->where('district_id', $id))
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->whereHas('branch', fn ($b) => $b->where('region_id', $id)))
            ->when($filters['gender'] ?? null, fn ($q, $gender) => $q->where('gender', $gender))
            ->whereDoesntHave('claimItems', fn ($q) => $q->whereHas('claim', fn ($c) => $c
                ->where('status', '!=', 'Rejected')
                ->where('period_from', '<=', $to)
                ->where('period_to', '>=', $from)))
            ->with([
                'branch:id,code,name,region_id',
                'district:id,name',
                'amortizations' => fn ($q) => $this->dueWithin($q, $from, $to)->orderBy('installment_no'),
            ])
            ->orderBy('application_no')
            ->get();

        return $loans->map(function (Aksic $aksic): array {
            $rows = $aksic->amortizations;

            return [
                'aksic' => $aksic,
                'aksic_id' => $aksic->getKey(),
                'district_id' => $aksic->district_id,
                'region_id' => $aksic->branch?->region_id,
                'branch_id' => $aksic->branch_id,
                'gender' => $aksic->gender,
                'installments_count' => $rows->count(),
                'principal_outstanding' => round((float) $rows->first()->principal_amount_os, 2),
                'markup_amount' => round((float) $rows->sum('total_interest'), 2),
            ];
        })->values();
    }

    /**
     * Instalments whose due date falls inside the claim period (inclusive,
     * compared as dates so a stored time part can never drop the last day).
     */
    private function dueWithin($query, string $from, string $to)
    {
        return $query->whereDate('due_date', '>=', $from)->whereDate('due_date', '<=', $to);
    }

    /**
     * @param  array<string, mixed>  $data  validated claim data (period + filters + claim_date + remarks)
     */
    public function lodge(array $data): ?AksicClaim
    {
        return DB::transaction(function () use ($data): ?AksicClaim {
            $lines = $this->eligible($data);

            if ($lines->isEmpty()) {
                return null;
            }

            $claim = AksicClaim::create([
                'claim_no' => $this->nextClaimNumber($data['claim_date']),
                'claim_date' => $data['claim_date'],
                'period_from' => $data['period_from'],
                'period_to' => $data['period_to'],
                'district_id' => $data['district_id'] ?? null,
                'region_id' => $data['region_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'gender' => $data['gender'] ?? null,
                'total_loans' => $lines->count(),
                'total_principal_outstanding' => round($lines->sum('principal_outstanding'), 2),
                'total_markup' => round($lines->sum('markup_amount'), 2),
                'status' => 'Lodged',
                'remarks' => $data['remarks'] ?? null,
            ]);

            $claim->items()->createMany(
                $lines->map(fn (array $line): array => collect($line)->except('aksic')->all())->all()
            );

            return $claim;
        });
    }

    /**
     * CLM-YYYYMM-0001, sequential per claim month.
     */
    private function nextClaimNumber(string $claimDate): string
    {
        $prefix = 'CLM-'.date('Ym', strtotime($claimDate)).'-';

        $last = AksicClaim::withTrashed()
            ->where('claim_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('claim_no')
            ->value('claim_no');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Aggregate claim lines for MIS: grouped by district, region, branch or gender.
     *
     * @param  array<string, mixed>  $filters  status / date_from / date_to / district_id / region_id / branch_id / gender
     * @return Collection<int, object{label: string, claims: int, loans: int, principal: float, markup: float}>
     */
    public function summary(string $groupBy, array $filters): Collection
    {
        $column = match ($groupBy) {
            'region' => 'aksic_claim_items.region_id',
            'branch' => 'aksic_claim_items.branch_id',
            'gender' => 'aksic_claim_items.gender',
            default => 'aksic_claim_items.district_id',
        };

        $rows = AksicClaimItem::query()
            ->join('aksic_claims', 'aksic_claims.id', '=', 'aksic_claim_items.aksic_claim_id')
            ->whereNull('aksic_claims.deleted_at')
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('aksic_claims.status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('aksic_claims.claim_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('aksic_claims.claim_date', '<=', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('aksic_claim_items.district_id', $v))
            ->when($filters['region_id'] ?? null, fn ($q, $v) => $q->where('aksic_claim_items.region_id', $v))
            ->when($filters['branch_id'] ?? null, fn ($q, $v) => $q->where('aksic_claim_items.branch_id', $v))
            ->when($filters['gender'] ?? null, fn ($q, $v) => $q->where('aksic_claim_items.gender', $v))
            ->selectRaw("{$column} as group_key")
            ->selectRaw('count(distinct aksic_claim_items.aksic_claim_id) as claims')
            ->selectRaw('count(*) as loans')
            ->selectRaw('sum(aksic_claim_items.principal_outstanding) as principal')
            ->selectRaw('sum(aksic_claim_items.markup_amount) as markup')
            ->groupBy('group_key')
            ->get();

        $names = match ($groupBy) {
            'region' => Region::query()->pluck('name', 'id'),
            'branch' => Branch::query()->get(['id', 'code', 'name'])->mapWithKeys(fn ($b) => [$b->id => $b->code.' - '.$b->name]),
            'gender' => collect(),
            default => District::query()->pluck('name', 'id'),
        };

        return $rows->map(fn ($row) => (object) [
            'label' => $groupBy === 'gender'
                ? ($row->group_key ?: 'Not recorded')
                : ($names[$row->group_key] ?? 'Not recorded'),
            'claims' => (int) $row->claims,
            'loans' => (int) $row->loans,
            'principal' => (float) $row->principal,
            'markup' => (float) $row->markup,
        ])->sortBy('label')->values();
    }
}
