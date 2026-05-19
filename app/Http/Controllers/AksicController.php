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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\QueryBuilder\QueryBuilder;

class AksicController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AksicAmortizationScheduleGenerator $scheduleGenerator) {}

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
        ];
    }

    public function index(Request $request): View
    {
        $aksics = QueryBuilder::for(Aksic::class)
            ->allowedFilters(Aksic::getAllowedFilters())
            ->allowedSorts(['application_no', 'name', 'cnic', 'principal_amount', 'status', 'created_at'])
            ->withCount('amortizations')
            ->with(['branch', 'district', 'aksicRule'])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        return view('aksics.index', compact('aksics'));
    }

    public function create(): View
    {
        return view('aksics.create', $this->formData());
    }

    public function store(StoreAksicRequest $request): RedirectResponse
    {
        $aksic = DB::transaction(function () use ($request): Aksic {
            $data = $request->validated();
            $data['gender'] = $data['quota'];
            $data['aksic_rule_id'] = AksicRule::query()
                ->where('district_id', $data['district_id'])
                ->where('is_active', true)
                ->value('id');
            $data['total_rate'] = bcadd((string) $data['kibor_rate'], (string) $data['spread_rate'], 2);

            $aksic = Aksic::create($data);
            $this->syncSchedule($aksic);

            return $aksic;
        });

        return redirect()->route('aksic.show', $aksic)
            ->with('success', 'AKSIC record created and amortization schedule generated successfully.');
    }

    public function show(Aksic $aksic): View
    {
        $aksic->load(['amortizations' => fn ($query) => $query->orderBy('installment_no'), 'branch', 'district', 'aksicRule', 'businessCategory', 'businessSubCategory', 'creator', 'updater']);

        return view('aksics.show', compact('aksic'));
    }

    public function edit(Aksic $aksic): View
    {
        return view('aksics.edit', ['aksic' => $aksic] + $this->formData());
    }

    public function update(UpdateAksicRequest $request, Aksic $aksic): RedirectResponse
    {
        DB::transaction(function () use ($request, $aksic): void {
            $data = $request->validated();
            $data['gender'] = $data['quota'];
            $data['aksic_rule_id'] = AksicRule::query()
                ->where('district_id', $data['district_id'])
                ->where('is_active', true)
                ->value('id');
            $data['total_rate'] = bcadd((string) $data['kibor_rate'], (string) $data['spread_rate'], 2);

            $aksic->update($data);
            $this->syncSchedule($aksic);
        });

        return redirect()->route('aksic.show', $aksic)
            ->with('success', 'AKSIC record updated and amortization schedule regenerated successfully.');
    }

    public function destroy(Aksic $aksic): RedirectResponse
    {
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

    private function syncSchedule(Aksic $aksic): void
    {
        $rows = $this->scheduleGenerator->generate(
            (string) $aksic->principal_amount,
            (int) $aksic->tenure,
            ($aksic->sanction_date ?? $aksic->disbursement_date)->toDateString(),
            (string) $aksic->kibor_rate,
            (string) $aksic->spread_rate,
        );

        $aksic->amortizations()->forceDelete();
        $aksic->amortizations()->createMany($rows);
    }
}
