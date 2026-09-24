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
use App\Support\AksicDate;
use App\Support\OfficeAccess;
use Database\Seeders\AksicDemoCasesSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            new Middleware('role_or_permission:view aksics', only: ['index', 'show', 'print', 'export']),
            new Middleware('role_or_permission:create aksics', only: ['create', 'store']),
            new Middleware('role_or_permission:edit aksics', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete aksics', only: ['destroy']),
            new Middleware('role_or_permission:approve aksics', only: ['approve']),
            new Middleware('role_or_permission:import aksics', only: ['downloadTemplate', 'import']),
        ];
    }

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : 10;

        $aksics = $this->listQuery($request)
            ->withCount('amortizations')
            ->with(['branch', 'district', 'aksicRule', 'businessCategory'])
            ->paginate($perPage)
            ->withQueryString();

        // Remember the list (filters, sort, page) so "Back" on the case pages returns to it.
        $request->session()->put('aksic.list_url', $request->fullUrl());
        $stats = $this->indexStats($request);
        $districts = District::orderBy('name')->get(['id', 'name']);
        $office = OfficeAccess::for($request->user());

        return view('aksics.index', compact('aksics', 'stats', 'districts', 'perPage', 'office'));
    }

    /** Rows per page offered on the list (max 500 keeps a page light even at 1M rows). */
    public const PER_PAGE = [10, 25, 50, 100, 300, 500];

    /**
     * One Spatie QueryBuilder for the list, its export and its totals: office
     * scope (branch / region), every allowed filter and the allowed sorts.
     */
    private function listQuery(Request $request): QueryBuilder
    {
        // Branch users see their branch, region users their region's branches (OfficeAccess).
        return QueryBuilder::for(OfficeAccess::scope(Aksic::query(), $request->user()), $request)
            ->allowedFilters(Aksic::getAllowedFilters())
            ->allowedSorts(['application_no', 'name', 'cnic', 'principal_amount', 'total_interest', 'tenure', 'disbursement_date', 'status', 'created_at'])
            ->defaultSort('-created_at')
            ->orderByDesc('id'); // stable order for bulk imports sharing a created_at (matches Previous / Next on the case page)
    }

    /**
     * Export the filtered list (same filters and sort as the screen) as CSV that
     * opens in Excel. Streamed in chunks, so 1M rows do not exhaust memory.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless(config('aksic.excel_export'), 404);

        $query = $this->listQuery($request)->with(['branch:id,code,name', 'district:id,name', 'businessCategory:id,name']);
        $filename = 'aksic-cases-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Urdu / special characters correctly
            fputcsv($out, ['Application No', 'Account No', 'Applicant', 'Father / Husband', 'CNIC', 'Phone', 'Quota', 'Gender',
                'District', 'Branch Code', 'Branch', 'Business Category', 'Business Nature', 'Principal (Rs)', 'KIBOR %', 'Spread %',
                'Total Rate %', 'Tenure (months)', 'Disbursement Date', 'Markup (Rs)', 'Status', 'Entered On']);

            foreach ($query->lazy(1000) as $aksic) {
                fputcsv($out, [
                    $aksic->application_no, $aksic->account_no, $aksic->name, $aksic->father_name, $aksic->cnic, $aksic->phone,
                    $aksic->quota, $aksic->gender, $aksic->district_name ?? $aksic->district?->name, $aksic->branch?->code, $aksic->branch?->name,
                    $aksic->businessCategory?->name, $aksic->business_type, $aksic->principal_amount, $aksic->kibor_rate,
                    $aksic->spread_rate, $aksic->total_rate, $aksic->tenure,
                    AksicDate::display($aksic->disbursement_date, ''),
                    $aksic->total_interest === null ? '' : round((float) $aksic->total_interest, 2),
                    $aksic->status, optional($aksic->created_at)->format('d.m.Y'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Local development only: (re)create random demo cases so the list, filters,
     * export and print can be tried with realistic volumes. Refused on any
     * environment other than "local" and for anyone but a super admin.
     */
    public function demoData(Request $request): RedirectResponse
    {
        abort_unless(config('aksic.demo_data'), 404);
        abort_unless(app()->isLocal() && $request->user()?->hasRole('super-admin'), 403);

        $count = max(0, min(5000, (int) $request->input('count', 520)));
        putenv('AKSIC_DEMO_COUNT='.$count);
        $_ENV['AKSIC_DEMO_COUNT'] = $_SERVER['AKSIC_DEMO_COUNT'] = (string) $count;
        set_time_limit(600);

        Artisan::call('db:seed', ['--class' => AksicDemoCasesSeeder::class, '--force' => true]);

        return redirect()->route('aksic.index')
            ->with('success', $count ? "{$count} demo AKSIC cases created (application no DEMO-00001 onwards)." : 'Demo AKSIC cases removed.');
    }

    /**
     * Totals for the KPI strip and the schedule tabs. Uses every active filter
     * except the schedule tab itself, so the tab counts always add up.
     *
     * @return array<string, int|float>
     */
    private function indexStats(Request $request): array
    {
        $filters = (array) $request->query('filter', []);
        unset($filters['schedule']);
        $statsRequest = Request::create($request->url(), 'GET', ['filter' => $filters]);

        // One aggregate query (indexed status column, no EXISTS on the schedule
        // table), cached briefly per office + filters so large tables (10k - 1M
        // rows) are not re-scanned on every page change or sort.
        $user = $request->user();
        $key = 'aksic-index-stats:v2:'.Cache::get(Aksic::STATS_VERSION_KEY, 0).':'.md5(json_encode([OfficeAccess::branchIds($user), $filters]));

        return Cache::remember($key, now()->addSeconds(60), function () use ($statsRequest, $user): array {
            $totals = QueryBuilder::for(OfficeAccess::scope(Aksic::query(), $user), $statsRequest)
                ->allowedFilters(Aksic::getAllowedFilters())
                ->toBase()
                ->selectRaw("COUNT(*) as cases,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as generated,
                    COALESCE(SUM(principal_amount), 0) as principal,
                    COALESCE(SUM(total_interest), 0) as markup,
                    SUM(CASE WHEN quota = 'Male' THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN quota = 'Female' THEN 1 ELSE 0 END) as female,
                    COALESCE(AVG(principal_amount), 0) as average")
                ->first();

            return [
                'cases' => (int) $totals->cases,
                'principal' => (float) $totals->principal,
                'markup' => (float) $totals->markup,
                'generated' => (int) $totals->generated,
                'pending' => (int) $totals->cases - (int) $totals->generated,
                'male' => (int) $totals->male,
                'female' => (int) $totals->female,
                'other' => (int) $totals->cases - (int) $totals->male - (int) $totals->female,
                'average' => (float) $totals->average,
            ];
        });
    }

    public function create(): View
    {
        return view('aksics.create', $this->formData());
    }

    public function store(StoreAksicRequest $request): RedirectResponse
    {
        $aksic = DB::transaction(function () use ($request): Aksic {
            $data = $request->validated();
            $data['branch_id'] = $this->officeBranch($data['branch_id'] ?? null);
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
        $this->ensureVisible($aksic);
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
        $this->ensureVisible($aksic);
        $aksic->load([
            'amortizations' => fn ($query) => $query->orderBy('installment_no'),
            'branch', 'district', 'aksicRule', 'businessCategory', 'businessSubCategory',
            'creator', 'updater',
        ]);

        return view('aksics.print', compact('aksic'));
    }

    public function edit(Aksic $aksic): View
    {
        $this->ensureVisible($aksic);
        abort_if(! $this->canModify($aksic), 403);

        return view('aksics.edit', ['aksic' => $aksic] + $this->formData());
    }

    public function update(UpdateAksicRequest $request, Aksic $aksic): RedirectResponse
    {
        $this->ensureVisible($aksic);
        abort_if(! $this->canModify($aksic), 403);

        DB::transaction(function () use ($request, $aksic): void {
            $data = $request->validated();
            $data['branch_id'] = $this->officeBranch($data['branch_id'] ?? null);
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
        $this->ensureVisible($aksic);

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

        // Scheme rules (site visit, business nature, loan fields) must be met first.
        if ($blockers = $aksic->approvalBlockers()) {
            return $this->afterApprove($request, $aksic)
                ->with('error', 'Not approved -- complete the case first: '.implode(' ', $blockers));
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
        abort_unless(config('aksic.excel_import'), 404);

        $template = $this->excelService->createTemplate();

        return response()->download($template['path'], $template['filename'])->deleteFileAfterSend();
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(config('aksic.excel_import'), 404);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        $result = $this->excelService->import($validated['file'], OfficeAccess::branchIds($request->user()));

        return redirect()->route('aksic.index')
            ->with('success', "{$result['imported']} AKSIC rows imported. {$result['skipped']} rows skipped.")
            ->with('import_errors', $result['errors']);
    }

    public function destroy(Aksic $aksic): RedirectResponse
    {
        $this->ensureVisible($aksic);
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
            'branches' => OfficeAccess::scope(Branch::query(), request()->user(), 'id')->orderBy('name')->get(['id', 'name', 'code']),
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

        // Back to the same list page (keeps filters, page and sort).
        $previous = url()->previous();
        if (str_starts_with($previous, route('aksic.index'))) {
            $path = parse_url($previous, PHP_URL_PATH);
            if (rtrim((string) $path, '/') === rtrim((string) parse_url(route('aksic.index'), PHP_URL_PATH), '/')) {
                return redirect()->to($previous);
            }
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
        $scope = fn () => OfficeAccess::scope(Aksic::query(), request()->user())
            ->when($pendingOnly, fn ($query) => $query->where('status', 'Pending'));

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

    /**
     * A case outside the user's branch/region is treated as not found, so other
     * branches' case numbers are not even confirmed to exist.
     */
    private function ensureVisible(Aksic $aksic): void
    {
        abort_unless(OfficeAccess::allowsBranch(request()->user(), $aksic->branch_id), 404);
    }

    /**
     * Branch a case is booked at: branch users always book at their own branch;
     * region users may pick only a branch of their region.
     */
    private function officeBranch(mixed $branchId): ?int
    {
        $user = request()->user();
        $access = OfficeAccess::for($user);

        if ($access['level'] === OfficeAccess::ALL) {
            return $branchId === null || $branchId === '' ? null : (int) $branchId;
        }

        if ($access['level'] === OfficeAccess::BRANCH) {
            return (int) $access['branch_ids'][0];
        }

        if (! OfficeAccess::allowsBranch($user, $branchId)) {
            throw ValidationException::withMessages(['branch_id' => 'Select a branch of your region ('.$access['label'].').']);
        }

        return (int) $branchId;
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
