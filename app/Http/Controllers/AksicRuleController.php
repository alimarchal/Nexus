<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAksicRuleRequest;
use App\Http\Requests\UpdateAksicRuleRequest;
use App\Models\AksicRule;
use App\Models\District;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\QueryBuilder\QueryBuilder;

class AksicRuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view aksic rules', only: ['index', 'show']),
            new Middleware('role_or_permission:create aksic rules', only: ['create', 'store']),
            new Middleware('role_or_permission:edit aksic rules', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete aksic rules', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $districts = District::query()->orderBy('name')->get(['id', 'name']);

        $query = QueryBuilder::for(AksicRule::class)
            ->allowedFilters(AksicRule::getAllowedFilters())
            ->allowedSorts(['district_name', 'population_percentage', 'proposed_beneficiaries', 'is_active', 'created_at']);

        $totalRuleIds = (clone $query)->pluck('id');
        $totals = [
            'population_percentage' => (float) (clone $query)->sum('population_percentage'),
            'proposed_beneficiaries' => (int) (clone $query)->sum('proposed_beneficiaries'),
            'used' => AksicRule::query()
                ->whereIn('id', $totalRuleIds)
                ->withCount('aksics')
                ->get()
                ->sum('aksics_count'),
        ];

        $aksicRules = $query
            ->with('district')
            ->withCount('aksics')
            ->defaultSort('district_name')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return view('aksic-rules.index', compact('aksicRules', 'districts', 'totals'));
    }

    public function create(): View
    {
        return view('aksic-rules.create', $this->formData());
    }

    public function store(StoreAksicRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['district_name'] = District::findOrFail($data['district_id'])->name;

        AksicRule::create($data);

        return redirect()->route('aksic-rules.index')->with('success', 'AKSIC rule created successfully.');
    }

    public function show(AksicRule $aksicRule): View
    {
        $aksicRule->load(['district', 'creator', 'updater'])->loadCount('aksics');

        return view('aksic-rules.show', compact('aksicRule'));
    }

    public function edit(AksicRule $aksicRule): View
    {
        return view('aksic-rules.edit', ['aksicRule' => $aksicRule] + $this->formData());
    }

    public function update(UpdateAksicRuleRequest $request, AksicRule $aksicRule): RedirectResponse
    {
        $data = $request->validated();
        $data['district_name'] = District::findOrFail($data['district_id'])->name;

        $aksicRule->update($data);

        return redirect()->route('aksic-rules.index')->with('success', 'AKSIC rule updated successfully.');
    }

    public function destroy(AksicRule $aksicRule): RedirectResponse
    {
        if ($aksicRule->aksics()->exists()) {
            return back()->withErrors(['aksic_rule' => 'This rule is used by AKSIC records and cannot be deleted. Deactivate it instead.']);
        }

        $aksicRule->delete();

        return redirect()->route('aksic-rules.index')->with('success', 'AKSIC rule deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
