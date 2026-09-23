<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAksicClaimRequest;
use App\Models\AksicClaim;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use App\Services\AksicClaimService;
use App\Support\AksicDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * AKSIC claim lodging and MIS reporting (Portal Change #5):
 * lodge a claim for a period, optionally for one District / Region / Branch /
 * Gender, and report lodged claims grouped by any of those four.
 */
class AksicClaimController extends Controller implements HasMiddleware
{
    private const GROUPS = [
        'district' => 'District',
        'region' => 'Region',
        'branch' => 'Branch',
        'gender' => 'Gender',
    ];

    public function __construct(private readonly AksicClaimService $claims) {}

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view aksic claims', only: ['index', 'show']),
            new Middleware('role_or_permission:lodge aksic claims', only: ['create', 'store']),
            new Middleware('role_or_permission:settle aksic claims', only: ['updateStatus']),
            new Middleware('role_or_permission:delete aksic claims', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $claims = QueryBuilder::for(AksicClaim::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('district_id'),
                AllowedFilter::exact('region_id'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('gender'),
                AllowedFilter::partial('claim_no'),
                AllowedFilter::callback('date_from', fn ($q, $v) => $q->whereDate('claim_date', '>=', AksicDate::toDatabase($v))),
                AllowedFilter::callback('date_to', fn ($q, $v) => $q->whereDate('claim_date', '<=', AksicDate::toDatabase($v))),
            ])
            ->with(['district:id,name', 'region:id,name', 'branch:id,code,name', 'creator:id,name'])
            ->defaultSort('-claim_date')
            ->allowedSorts(['claim_date', 'claim_no', 'total_markup', 'total_loans'])
            ->paginate(15)
            ->withQueryString();

        $groupBy = array_key_exists($request->query('group_by'), self::GROUPS) ? $request->query('group_by') : 'district';
        $filter = (array) $request->query('filter', []);
        $summary = $this->claims->summary($groupBy, [
            'status' => $filter['status'] ?? null,
            'date_from' => AksicDate::toDatabase($filter['date_from'] ?? null),
            'date_to' => AksicDate::toDatabase($filter['date_to'] ?? null),
            'district_id' => $filter['district_id'] ?? null,
            'region_id' => $filter['region_id'] ?? null,
            'branch_id' => $filter['branch_id'] ?? null,
            'gender' => $filter['gender'] ?? null,
        ]);

        return view('aksic-claims.index', [
            'claims' => $claims,
            'summary' => $summary,
            'groupBy' => $groupBy,
            'groups' => self::GROUPS,
        ] + $this->lookups());
    }

    /**
     * Lodge form. When the period is filled in, the eligible loans are
     * previewed (district / region / branch / gender breakdown) before lodging.
     */
    public function create(Request $request): View
    {
        $preview = null;
        $criteria = null;

        if ($request->filled(['period_from', 'period_to'])) {
            $criteria = [
                'period_from' => AksicDate::toDatabase($request->query('period_from')),
                'period_to' => AksicDate::toDatabase($request->query('period_to')),
                'district_id' => $request->integer('district_id') ?: null,
                'region_id' => $request->integer('region_id') ?: null,
                'branch_id' => $request->integer('branch_id') ?: null,
                'gender' => in_array($request->query('gender'), AksicClaim::GENDERS, true) ? $request->query('gender') : null,
            ];

            $validDates = strtotime((string) $criteria['period_from']) && strtotime((string) $criteria['period_to'])
                && $criteria['period_from'] <= $criteria['period_to'];

            $preview = $validDates ? $this->claims->eligible($criteria) : collect();
        }

        return view('aksic-claims.create', [
            'preview' => $preview,
            'criteria' => $criteria,
            'groups' => self::GROUPS,
        ] + $this->lookups());
    }

    public function store(StoreAksicClaimRequest $request): RedirectResponse
    {
        $claim = $this->claims->lodge($request->validated());

        if (! $claim) {
            return back()->withInput()->withErrors([
                'period_from' => 'No approved loan has unclaimed instalments due in this period for the selected district / region / branch / gender.',
            ]);
        }

        return redirect()->route('aksic-claims.show', $claim)
            ->with('success', "Claim {$claim->claim_no} lodged for {$claim->total_loans} loans.");
    }

    public function show(AksicClaim $aksicClaim): View
    {
        $aksicClaim->load([
            'district:id,name', 'region:id,name', 'branch:id,code,name', 'creator:id,name', 'updater:id,name',
            'items' => fn ($q) => $q->orderBy('id'),
            'items.aksic:id,application_no,account_no,name,cnic',
            'items.district:id,name', 'items.region:id,name', 'items.branch:id,code,name',
        ]);

        $breakdowns = collect(self::GROUPS)->mapWithKeys(fn (string $label, string $key) => [
            $key => $aksicClaim->items
                ->groupBy(fn ($item) => match ($key) {
                    'district' => $item->district?->name ?? 'Not recorded',
                    'region' => $item->region?->name ?? 'Not recorded',
                    'branch' => $item->branch ? $item->branch->code.' - '.$item->branch->name : 'Not recorded',
                    'gender' => $item->gender ?? 'Not recorded',
                })
                ->map(fn ($items, $name) => (object) [
                    'label' => $name,
                    'loans' => $items->count(),
                    'principal' => (float) $items->sum('principal_outstanding'),
                    'markup' => (float) $items->sum('markup_amount'),
                ])
                ->sortBy('label')
                ->values(),
        ]);

        return view('aksic-claims.show', [
            'claim' => $aksicClaim,
            'breakdowns' => $breakdowns,
            'groups' => self::GROUPS,
        ]);
    }

    public function updateStatus(Request $request, AksicClaim $aksicClaim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Settled', 'Rejected'])],
            'status_date' => ['required'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_if($aksicClaim->status !== 'Lodged', 422, 'Only a lodged claim can be settled or rejected.');

        $statusDate = AksicDate::toDatabase($validated['status_date']);
        abort_unless(is_string($statusDate) && strtotime($statusDate), 422, 'Invalid date.');

        $aksicClaim->update([
            'status' => $validated['status'],
            'status_date' => $statusDate,
            'remarks' => $validated['remarks'] ?? $aksicClaim->remarks,
        ]);

        return redirect()->route('aksic-claims.show', $aksicClaim)
            ->with('success', "Claim {$aksicClaim->claim_no} marked {$validated['status']}.");
    }

    public function destroy(AksicClaim $aksicClaim): RedirectResponse
    {
        abort_if($aksicClaim->status === 'Settled', 403, 'A settled claim cannot be deleted.');

        $aksicClaim->delete();

        return redirect()->route('aksic-claims.index')
            ->with('success', "Claim {$aksicClaim->claim_no} deleted; its loans can be claimed again.");
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
            'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->orderBy('code')->get(['id', 'code', 'name', 'region_id']),
            'genders' => AksicClaim::GENDERS,
            'statuses' => AksicClaim::STATUSES,
        ];
    }
}
