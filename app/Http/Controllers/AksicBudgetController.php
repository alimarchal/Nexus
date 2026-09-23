<?php

namespace App\Http\Controllers;

use App\Models\AksicBudget;
use App\Models\AksicBudgetAllocation;
use App\Models\AksicBudgetRevision;
use App\Services\AksicBudgetService;
use App\Support\AksicDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * AKSIC markup budget: position dashboard, budget versions, and every
 * change (enhance / reduce / redistribute / settings) through the ledger.
 */
class AksicBudgetController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AksicBudgetService $budgets) {}

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view aksic budget', only: ['index', 'show']),
            new Middleware('role_or_permission:manage aksic budget', except: ['index', 'show']),
        ];
    }

    /**
     * Opens the active budget; falls back to the version list.
     */
    public function index(): View|RedirectResponse
    {
        if ($active = AksicBudget::active()) {
            return redirect()->route('aksic-budgets.show', $active);
        }

        return view('aksic-budgets.index', ['budgets' => AksicBudget::query()->latest('id')->get()]);
    }

    public function show(Request $request, AksicBudget $aksicBudget): View
    {
        $view = in_array($request->query('view'), ['gender', 'nature'], true) ? $request->query('view') : 'district';

        return view('aksic-budgets.show', [
            'budget' => $aksicBudget,
            'position' => $this->budgets->position($aksicBudget),
            'view' => $view,
            'revisions' => $aksicBudget->revisions()->with(['district:id,name', 'creator:id,name'])->latest('id')->paginate(15)->withQueryString(),
            'versions' => AksicBudget::query()->latest('id')->get(['id', 'title', 'total_amount', 'is_active', 'created_at']),
            'actions' => AksicBudgetRevision::ACTIONS,
        ]);
    }

    public function create(): View
    {
        return view('aksic-budgets.form', ['budget' => new AksicBudget([
            'existing_business_percentage' => 25,
            'new_business_percentage' => 75,
            'enforcement' => 'block',
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $budget = $this->budgets->create(collect($data)->except(['activate', 'change_reason'])->all(), $request->boolean('activate'));

        return redirect()->route('aksic-budgets.show', $budget)->with('success', 'Budget version created and allocated by population %.');
    }

    public function edit(AksicBudget $aksicBudget): View
    {
        return view('aksic-budgets.form', ['budget' => $aksicBudget]);
    }

    public function update(Request $request, AksicBudget $aksicBudget): RedirectResponse
    {
        $this->budgets->updateSettings($aksicBudget, $this->validated($request, false));

        return redirect()->route('aksic-budgets.show', $aksicBudget)->with('success', 'Budget settings updated and recorded in the revision history.');
    }

    public function activate(Request $request, AksicBudget $aksicBudget): RedirectResponse
    {
        $this->budgets->activate($aksicBudget, $request->input('reference_no'), AksicDate::toDatabase($request->input('reference_date')), $request->input('reason'));

        return redirect()->route('aksic-budgets.show', $aksicBudget)->with('success', 'This budget is now the active budget used at approval.');
    }

    public function revise(Request $request, AksicBudget $aksicBudget, AksicBudgetAllocation $allocation): RedirectResponse
    {
        abort_unless($allocation->aksic_budget_id === $aksicBudget->id, 404);

        $request->merge(['reference_date' => AksicDate::toDatabase($request->input('reference_date'))]);
        $data = $request->validate([
            'action' => ['required', Rule::in(['enhancement', 'reduction'])],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'adjust_total' => ['nullable', 'boolean'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'reference_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->budgets->revise($allocation, $data['action'], (float) $data['amount'], $request->boolean('adjust_total'),
                $data['reference_no'] ?? null, $data['reference_date'] ?? null, $data['reason']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', ucfirst($data['action']).' of '.number_format((float) $data['amount'], 2).' recorded for '.($allocation->district?->name ?? 'district').'.');
    }

    public function redistribute(Request $request, AksicBudget $aksicBudget): RedirectResponse
    {
        $request->merge(['reference_date' => AksicDate::toDatabase($request->input('reference_date'))]);
        $data = $request->validate([
            'reference_no' => ['nullable', 'string', 'max:100'],
            'reference_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $changed = $this->budgets->redistribute($aksicBudget, $data['reference_no'] ?? null, $data['reference_date'] ?? null, $data['reason']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Budget redistributed by population %: {$changed} district allocations changed.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $creating): array
    {
        $request->merge(['reference_date' => AksicDate::toDatabase($request->input('reference_date'))]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'reference_date' => ['nullable', 'date'],
            'total_amount' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'existing_business_percentage' => ['required', 'numeric', 'between:0,100'],
            'new_business_percentage' => ['required', 'numeric', 'between:0,100'],
            'enforcement' => ['required', Rule::in(array_keys(AksicBudget::ENFORCEMENTS))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'change_reason' => [$creating ? 'nullable' : 'required', 'string', 'max:2000'],
        ]);

        abort_if(abs((float) $data['existing_business_percentage'] + (float) $data['new_business_percentage'] - 100) > 0.001, 422,
            'Existing + New business percentages must total 100.');

        return $data;
    }
}
