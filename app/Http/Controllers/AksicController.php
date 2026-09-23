<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAksicRequest;
use App\Http\Requests\UpdateAksicRequest;
use App\Models\Aksic;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\District;
use App\Services\AksicAmortizationScheduleGenerator;
use App\Services\AksicBudgetService;
use App\Services\AksicExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AksicController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AksicAmortizationScheduleGenerator $scheduleGenerator,
        private readonly AksicExcelService $excelService,
        private readonly AksicBudgetService $budgetService,
    ) {}

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view aksics', only: ['index', 'show', 'print']),
            new Middleware('role_or_permission:create aksics', only: ['create', 'store']),
            new Middleware('role_or_permission:edit aksics', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete aksics', only: ['destroy']),
            new Middleware('role_or_permission:approve aksics', only: ['approve']),
            new Middleware('role_or_permission:import aksics', only: ['downloadTemplate', 'import']),
        ];
    }

    public function index(Request $request): View
    {
        $aksics = QueryBuilder::for(Aksic::class)
            ->allowedFilters(Aksic::getAllowedFilters())
            ->allowedSorts(['application_no', 'name', 'cnic', 'principal_amount', 'status', 'created_at'])
            ->withCount('amortizations')
            ->with(['branch', 'district', 'aksicRule', 'businessCategory'])
            ->defaultSort('-created_at')
            ->orderByDesc('id') // stable order for bulk imports sharing a created_at (matches Previous / Next on the case page)
            ->paginate(10)
            ->withQueryString();
        $subCategoriesByParent = AksicBusinessCategory::query()
            ->where('parent_id', '!=', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id'])
            ->groupBy('parent_id')
            ->map(fn ($categories) => $categories->values());

        return view('aksics.index', compact('aksics', 'subCategoriesByParent'));
    }

    public function create(): View
    {
        return view('aksics.create', $this->formData());
    }

    public function store(StoreAksicRequest $request): RedirectResponse
    {
        $aksic = DB::transaction(function () use ($request): Aksic {
            $data = $request->validated();
            $data['gender'] = $this->resolveGender($data);
            $data['aksic_rule_id'] = AksicRule::query()
                ->where('district_id', $data['district_id'])
                ->where('is_active', true)
                ->value('id');
            $data['total_rate'] = bcadd((string) $data['kibor_rate'], (string) $data['spread_rate'], 2);
            $data['total_interest'] = null;
            $data['status'] = 'Pending';

            return Aksic::create($data);
        });

        return redirect()->route('aksic.show', $aksic)
            ->with('success', 'AKSIC record created as pending. Approve it to generate amortization schedule.');
    }

    public function show(Request $request, Aksic $aksic): View
    {
        $aksic->load(['amortizations' => fn ($query) => $query->orderBy('installment_no'), 'branch', 'district', 'aksicRule', 'businessCategory', 'businessSubCategory', 'creator', 'updater']);

        $pendingOnly = $request->query('nav') === 'pending';
        $navigation = $this->neighbours($aksic, $pendingOnly);
        $subCategoriesByParent = AksicBusinessCategory::query()
            ->where('parent_id', '!=', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id'])
            ->groupBy('parent_id')
            ->map(fn ($categories) => $categories->values());

        return view('aksics.show', compact('aksic', 'navigation', 'pendingOnly', 'subCategoriesByParent'));
    }

    /**
     * Printable AKSIC loan case sheet and repayment schedule.
     *
     * Renders a standalone document (no app chrome) so the browser prints the
     * sheet exactly as shown -- same pattern as the Account Opening Form print.
     */
    public function print(Aksic $aksic): View
    {
        $aksic->load([
            'amortizations' => fn ($query) => $query->orderBy('installment_no'),
            'branch', 'district', 'aksicRule', 'businessCategory', 'businessSubCategory',
            'creator', 'updater',
        ]);

        return view('aksics.print', compact('aksic'));
    }

    public function edit(Aksic $aksic): View
    {
        abort_if(! $this->canModify($aksic), 403);

        return view('aksics.edit', ['aksic' => $aksic] + $this->formData());
    }

    public function update(UpdateAksicRequest $request, Aksic $aksic): RedirectResponse
    {
        abort_if(! $this->canModify($aksic), 403);

        DB::transaction(function () use ($request, $aksic): void {
            $data = $request->validated();
            $data['gender'] = $this->resolveGender($data);
            $data['aksic_rule_id'] = AksicRule::query()
                ->where('district_id', $data['district_id'])
                ->where('is_active', true)
                ->value('id');
            $data['total_rate'] = bcadd((string) $data['kibor_rate'], (string) $data['spread_rate'], 2);
            unset($data['status'], $data['total_interest']);

            $aksic->update($data);
        });

        return redirect()->route('aksic.show', $aksic)
            ->with('success', 'AKSIC record updated successfully.');
    }

    public function approve(Request $request, Aksic $aksic): RedirectResponse
    {
        // Only a super-admin may regenerate an approved schedule (e.g. to apply the
        // revised first-instalment / markup rules of Portal Change #3 and #10).
        if ($aksic->status === 'Approved' && $aksic->amortizations()->exists() && ! $request->user()?->hasRole('super-admin')) {
            return $this->afterApprove($request, $aksic)
                ->with('success', 'AKSIC record is already approved.');
        }

        $validated = $request->validate([
            'business_sub_category_id' => [
                'required',
                'integer',
                Rule::exists('aksic_business_categories', 'id')
                    ->where(fn ($query) => $query
                        ->where('parent_id', $aksic->business_category_id)
                        ->where('parent_id', '!=', 0)),
            ],
        ]);

        if (! $this->canGenerateSchedule($aksic)) {
            return $this->afterApprove($request, $aksic)
                ->withErrors(['approve' => 'AKSIC record is missing required loan fields for schedule generation.']);
        }

        // Markup budget check: project this case's markup and test it against the
        // active budget (district, gender share, Existing/New share).
        $projectedMarkup = (float) collect($this->scheduleGenerator->generate(
            (string) $aksic->principal_amount,
            (int) $aksic->tenure,
            $aksic->disbursement_date->toDateString(),
            (string) $aksic->kibor_rate,
            (string) $aksic->spread_rate,
        ))->sum('total_interest');

        $budgetCheck = $this->budgetService->check($aksic, $projectedMarkup);
        $breachText = collect($budgetCheck['breaches'])
            ->map(fn (array $b): string => $b['scope'].': limit '.number_format($b['limit'], 2)
                .', used '.number_format($b['used'], 2).', this case '.number_format($b['requested'], 2)
                .' (short by '.number_format($b['shortfall'], 2).')')
            ->implode(' | ');

        if ($breachText !== '' && $budgetCheck['enforcement'] === 'block') {
            return $this->afterApprove($request, $aksic)->with('error',
                'Not approved -- markup budget exceeded. '.$breachText
                .'. Enhance the allocation on the AKSIC Budget page, then approve again.');
        }

        DB::transaction(function () use ($aksic, $validated): void {
            $aksic->update([
                'business_sub_category_id' => $validated['business_sub_category_id'],
            ]);

            $totalInterest = $this->syncSchedule($aksic);

            $aksic->update([
                'status' => 'Approved',
                'total_interest' => $totalInterest,
            ]);
        });

        $redirect = $this->afterApprove($request, $aksic)
            ->with('success', 'AKSIC record approved and amortization schedule generated successfully.');

        return $breachText !== '' && $budgetCheck['enforcement'] === 'warn'
            ? $redirect->with('warning', 'Approved over the markup budget. '.$breachText)
            : $redirect;
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $template = $this->excelService->createTemplate();

        return response()->download($template['path'], $template['filename'])->deleteFileAfterSend();
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        $result = $this->excelService->import($validated['file']);

        return redirect()->route('aksic.index')
            ->with('success', "{$result['imported']} AKSIC rows imported. {$result['skipped']} rows skipped.")
            ->with('import_errors', $result['errors']);
    }

    public function destroy(Aksic $aksic): RedirectResponse
    {
        abort_if(! $this->canModify($aksic), 403);

        $aksic->delete();

        return redirect()->route('aksic.index')
            ->with('success', 'AKSIC record deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name', 'code']),
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
            'rulesByDistrict' => AksicRule::query()
                ->where('is_active', true)
                ->orderBy('district_name')
                ->get(['id', 'district_id', 'district_name', 'population_percentage', 'proposed_beneficiaries'])
                ->keyBy('district_id'),
            'categories' => AksicBusinessCategory::query()->where('parent_id', 0)->orderBy('name')->get(['id', 'name', 'parent_id']),
            'subCategoriesByParent' => AksicBusinessCategory::query()
                ->where('parent_id', '!=', 0)
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->groupBy('parent_id')
                ->map(fn ($categories) => $categories->values()),
        ];
    }

    private function syncSchedule(Aksic $aksic): string
    {
        $rows = $this->scheduleGenerator->generate(
            (string) $aksic->principal_amount,
            (int) $aksic->tenure,
            $aksic->disbursement_date->toDateString(),
            (string) $aksic->kibor_rate,
            (string) $aksic->spread_rate,
        );

        $aksic->amortizations()->forceDelete();
        $aksic->amortizations()->createMany($rows);

        return collect($rows)->reduce(
            fn (string $carry, array $row): string => bcadd($carry, (string) $row['total_interest'], 6),
            '0.000000',
        );
    }

    /**
     * Where to land after the Approve modal: back on the case page (with its
     * Previous / Next context) when approved from there, otherwise the index.
     */
    private function afterApprove(Request $request, Aksic $aksic): RedirectResponse
    {
        if ($request->input('return_to') === 'show') {
            return redirect()->route('aksic.show', array_filter([
                'aksic' => $aksic,
                'nav' => $request->input('nav') === 'pending' ? 'pending' : null,
            ]));
        }

        return redirect()->route('aksic.index');
    }

    /**
     * Previous / next case in the same order as the index (newest first), with
     * id as tie-breaker so bulk-imported rows sharing a created_at still step
     * one by one. With $pendingOnly only cases awaiting approval are visited.
     *
     * @return array{previous: ?Aksic, next: ?Aksic, position: int, total: int}
     */
    private function neighbours(Aksic $aksic, bool $pendingOnly): array
    {
        $scope = fn () => Aksic::query()
            ->when($pendingOnly, fn ($query) => $query
                ->where('status', 'Pending')
                ->whereDoesntHave('amortizations'));

        $createdAt = $aksic->created_at;
        $id = $aksic->getKey();

        $newer = fn ($query) => $query->where(fn ($q) => $q
            ->where('created_at', '>', $createdAt)
            ->orWhere(fn ($q) => $q->where('created_at', $createdAt)->where('id', '>', $id)));

        $previous = $newer($scope())->orderBy('created_at')->orderBy('id')->first(['id', 'application_no']);
        $next = $scope()
            ->where(fn ($q) => $q
                ->where('created_at', '<', $createdAt)
                ->orWhere(fn ($q) => $q->where('created_at', $createdAt)->where('id', '<', $id)))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->first(['id', 'application_no']);

        return [
            'previous' => $previous,
            'next' => $next,
            'position' => $newer($scope())->count() + 1,
            'total' => $scope()->count(),
        ];
    }

    private function canGenerateSchedule(Aksic $aksic): bool
    {
        return $aksic->principal_amount !== null
            && $aksic->tenure !== null
            && $aksic->disbursement_date !== null
            && $aksic->kibor_rate !== null
            && $aksic->spread_rate !== null;
    }

    private function canModify(Aksic $aksic): bool
    {
        if (! $aksic->amortizations()->exists()) {
            return true;
        }

        return request()->user()?->hasRole('super-admin') === true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveGender(array $data): string
    {
        if (($data['quota'] ?? null) === 'Disabled') {
            return (string) $data['gender'];
        }

        return (string) $data['quota'];
    }
}
