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
    ) {}

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view aksics', only: ['index', 'show']),
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

    public function show(Aksic $aksic): View
    {
        $aksic->load(['amortizations' => fn ($query) => $query->orderBy('installment_no'), 'branch', 'district', 'aksicRule', 'businessCategory', 'businessSubCategory', 'creator', 'updater']);

        return view('aksics.show', compact('aksic'));
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
        if ($aksic->status === 'Approved' && $aksic->amortizations()->exists()) {
            return redirect()->route('aksic.index')
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
            return redirect()->route('aksic.index')
                ->withErrors(['approve' => 'AKSIC record is missing required loan fields for schedule generation.']);
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

        return redirect()->route('aksic.index')
            ->with('success', 'AKSIC record approved and amortization schedule generated successfully.');
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
