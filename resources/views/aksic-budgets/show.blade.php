@php
    use App\Services\AksicBudgetService;

    $t = $position['totals'];
    $rows = $position['rows'];
    $m = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($used, $limit) => $limit > 0 ? round($used / $limit * 100, 1) : ($used > 0 ? 100 : 0);
    $barClass = fn ($p) => $p > 100 ? 'over' : ($p >= 85 ? 'warn' : '');
    $usedPct = $pct($t['used'], $t['allocated']);
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $enfBadge = ['block' => 'bg-red-100 text-red-800', 'warn' => 'bg-amber-100 text-amber-800', 'report' => 'bg-gray-100 text-gray-700'];
    $canManage = auth()->user()?->can('manage aksic budget');
    $btn = 'inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head print:hidden">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('aksic.index') }}">AKSIC</a><span aria-hidden="true">›</span><span>Markup budget</span>
                </nav>
                <h1 class="ak-title">AKSIC Markup Budget</h1>
                <p class="ak-sub">
                    {{ $budget->title }}
                    @if ($budget->reference_no) &middot; {{ $budget->reference_no }} @endif
                    @if ($budget->reference_date) &middot; {{ $budget->reference_date->format('d.m.Y') }} @endif
                </p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ session('aksic.list_url', route('aksic.index')) }}" class="ak-btn ak-btn-outline" title="Back to AKSIC cases"><span aria-hidden="true">←</span> Back</a>
                <button type="button" onclick="window.print()" class="ak-btn ak-btn-outline" title="Print this view (Ctrl+P)">Print view</button>
                <button type="button" onclick="document.documentElement.classList.add('bgt-print-all'); window.print();" class="ak-btn ak-btn-outline" title="Print summary, all three views and the revision history">Print full report</button>
                @if ($canManage)
                    <a href="{{ route('aksic-budgets.edit', $budget) }}" class="ak-btn ak-btn-outline">Edit settings</a>
                    <a href="{{ route('aksic-budgets.create') }}" class="ak-btn ak-btn-primary">＋ New version</a>
                @endif
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')

    @include('aksics._grid-style')

    <style>
        @page { size: A4 landscape; margin: 10mm; }
        .bgt-h { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin: 0 0 8px; }
        .bgt-h h3 { font-size: 15px; font-weight: 700; color: #111827; }
        .bgt-h p { font-size: 12px; color: #4b5563; }
        .bgt-alt, .bgt-print-only { display: none; }
        .bgt-rev-btn { font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 6px; border: 1px solid transparent; }
        .bgt-rev-btn.up { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
        .bgt-rev-btn.down { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
        .bgt-rev-btn:hover { filter: brightness(.95); }
        .bgt-sign { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-top: 36px; font-size: 11px; color: #000; }
        .bgt-sign div { border-top: 1px solid #000; padding-top: 4px; text-align: center; }

        @media print {
            html, body { background: #fff !important; }
            .min-h-screen { min-height: 0 !important; }
            .bgt-screen-only { display: none !important; }
            .bgt-print-only { display: block !important; }
            html.bgt-print-all .bgt-alt { display: block !important; }
            html.bgt-print-all .bgt-view + .bgt-view { break-before: page; }
            .bgt-page { max-width: none !important; padding: 0 !important; margin: 0 !important; }
            .bgt-page > * + * { margin-top: 10px !important; }
            .bgt-section { box-shadow: none !important; border: 0 !important; padding: 0 !important; border-radius: 0 !important; background: #fff !important; }
            .bgt-section, .bgt-view { break-inside: auto; }
            .bgt-h { margin-bottom: 4px; }
            .bgt-h h3 { font-size: 11.5px; color: #000; }
            .bgt-h p { font-size: 9px; color: #000; }
            .overflow-x-auto { overflow: visible !important; }

            /* Keep the screen look on paper: black header / total rows and black grid lines. */
            table.aksic-grid { width: 100% !important; border-collapse: collapse !important; }
            table.aksic-grid th, table.aksic-grid td { border: 1px solid #000 !important; color: #000; }
            table.aksic-grid thead th, table.aksic-grid tfoot td {
                background: #000 !important; color: #fff !important;
                -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
            }
            table.aksic-grid thead th { border-color: #fff #000 #fff #000 !important; }
            table.aksic-grid tfoot td.neg { color: #fca5a5 !important; }
            table.aksic-grid .neg { color: #b91c1c !important; }
            table.aksic-grid .bar, table.aksic-grid .bar > span, .bgt-badge { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            table.aksic-grid tfoot { display: table-row-group; }
        }
    </style>

    <div class="py-6 print:py-0" x-data="{ revise: null, redistribute: false }">
        <div class="bgt-page mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="bgt-screen-only space-y-2">
                <x-status-message />
                <x-validation-errors />
            </div>

            {{-- Print header (paper only) ------------------------------------------ --}}
            <div class="bgt-print-only" style="color:#000">
                <p style="font-size:16px;font-weight:800;text-align:center">The Bank of Azad Jammu &amp; Kashmir</p>
                <p style="font-size:12px;font-weight:700;text-align:center;margin:2px 0 8px">AKSIC PM Youth Loan Scheme &mdash; Markup Budget Position</p>
                <table class="aksic-grid" style="font-size:9.5px">
                    <tbody>
                        <tr>
                            <td style="width:14%"><b>Budget</b></td><td>{{ $budget->title }}{{ $budget->is_active ? '' : ' (inactive version)' }}</td>
                            <td style="width:14%"><b>Letter</b></td><td>{{ $budget->reference_no ?: '—' }}{{ $budget->reference_date ? ' dated '.$budget->reference_date->format('d.m.Y') : '' }}</td>
                        </tr>
                        <tr>
                            <td><b>At approval</b></td><td>{{ \App\Models\AksicBudget::ENFORCEMENTS[$budget->enforcement] ?? $budget->enforcement }}</td>
                            <td><b>Printed</b></td><td>{{ now()->format('d.m.Y H:i') }} by {{ auth()->user()?->name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @unless ($budget->is_active)
                <div class="bgt-screen-only flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" x-data="{ sure: false }">
                    <span>This budget version is <b>inactive</b>; approvals are checked against the active budget.</span>
                    @if ($canManage)
                        <form method="POST" action="{{ route('aksic-budgets.activate', $budget) }}">
                            @csrf
                            <button type="button" x-show="! sure" @click="sure = true" class="ak-btn ak-btn-outline">Activate this version</button>
                            <span x-show="sure" x-cloak class="inline-flex items-center gap-2">
                                <span class="font-semibold">Make this the active budget?</span>
                                <button type="submit" class="ak-btn ak-btn-primary">Yes, activate</button>
                                <button type="button" @click="sure = false" class="ak-btn ak-btn-outline">Cancel</button>
                            </span>
                        </form>
                    @endif
                </div>
            @endunless

            {{-- KPI cards (screen) -------------------------------------------------- --}}
            <section class="ak-kpis" aria-label="Budget summary">
                <div class="ak-kpi">
                    <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75h19.5M3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V7.5A1.5 1.5 0 0 1 3.75 6ZM15 11.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                    <span class="ak-kpi-body">
                        <span class="ak-kpi-label">Total budget</span>
                        <span class="ak-kpi-value ak-kpi-value-sm">Rs {{ $m($t['budget']) }}</span>
                        <span class="ak-kpi-hint">{{ $t['unallocated'] == 0 ? 'Fully allocated to districts' : 'Rs '.$m($t['unallocated']).' not allocated' }}</span>
                    </span>
                </div>
                <div class="ak-kpi">
                    <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.3 4.3a11.95 11.95 0 0 1 5.8-5.8l2.65-1.2m0 0-5.94-2.28m5.94 2.28-2.28 5.94" /></svg></span>
                    <span class="ak-kpi-body">
                        <span class="ak-kpi-label">Markup used</span>
                        <span class="ak-kpi-value ak-kpi-value-sm">Rs {{ $m($t['used']) }}</span>
                        <span class="ak-kpi-hint">{{ $usedPct }}% of allocation &middot; {{ number_format($t['loans']) }} approved {{ \Illuminate\Support\Str::plural('loan', $t['loans']) }}</span>
                    </span>
                </div>
                <div class="ak-kpi">
                    <span class="ak-kpi-icon {{ $t['remaining'] < 0 ? 'ak-tone-amber' : 'ak-tone-green' }}" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" /></svg></span>
                    <span class="ak-kpi-body">
                        <span class="ak-kpi-label">Remaining</span>
                        <span class="ak-kpi-value ak-kpi-value-sm" @if ($t['remaining'] < 0) style="color:#b91c1c" @endif>Rs {{ $m($t['remaining']) }}</span>
                        <span class="ak-kpi-hint">{{ $t['remaining'] < 0 ? 'Over budget' : 'Available for new approvals' }}</span>
                    </span>
                </div>
                <div class="ak-kpi">
                    <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></span>
                    <span class="ak-kpi-body">
                        <span class="ak-kpi-label">At approval</span>
                        <span class="ak-kpi-value ak-kpi-value-sm">{{ \App\Models\AksicBudget::ENFORCEMENTS[$budget->enforcement] ?? $budget->enforcement }}</span>
                        <span class="ak-kpi-hint">{{ $budget->is_active ? 'Active version' : 'Inactive version' }}</span>
                    </span>
                </div>
            </section>

            {{-- Summary --------------------------------------------------------- --}}
            <section class="bgt-section">
                <div class="bgt-h"><h3>Budget summary</h3></div>
                <div class="overflow-x-auto">
                    <table class="aksic-grid">
                        <thead>
                            <tr>
                                <th class="num">Total budget</th>
                                <th class="num">Allocated to districts</th>
                                <th class="num">Unallocated</th>
                                <th class="num">Markup used (approved cases)</th>
                                <th class="num">Remaining</th>
                                <th class="ctr">Used</th>
                                <th class="ctr">Loans</th>
                                <th class="ctr">At approval</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="num"><b>{{ $m($t['budget']) }}</b></td>
                                <td class="num">{{ $m($t['allocated']) }}</td>
                                <td class="num {{ $t['unallocated'] < 0 ? 'neg' : '' }}">{{ $m($t['unallocated']) }}</td>
                                <td class="num">{{ $m($t['used']) }}</td>
                                <td class="num {{ $t['remaining'] < 0 ? 'neg' : '' }}"><b>{{ $m($t['remaining']) }}</b></td>
                                <td class="ctr" style="min-width:110px">{{ $usedPct }}%<div class="bar"><span class="{{ $barClass($usedPct) }}" style="width: {{ min(100, $usedPct) }}%"></span></div></td>
                                <td class="ctr">{{ number_format($t['loans']) }}</td>
                                <td class="ctr"><span class="bgt-badge rounded-full px-2 py-0.5 text-xs font-bold {{ $enfBadge[$budget->enforcement] ?? '' }}">{{ \App\Models\AksicBudget::ENFORCEMENTS[$budget->enforcement] ?? $budget->enforcement }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Position: view switch + tables ---------------------------------------- --}}
            @php
                $views = ['district' => 'District-wise', 'gender' => 'Gender-wise', 'nature' => 'Existing / New business'];
                $firstRule = optional($rows->first())['allocation']?->rule;
                $pctLabel = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
            @endphp
            <section class="bgt-section ak-card" style="padding:0">
                <div class="bgt-screen-only flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pr-4">
                    <nav class="ak-tabs" style="border-bottom:0" aria-label="Budget views">
                        @foreach ($views as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => null]) }}" class="ak-tab {{ $view === $key ? 'is-active' : '' }}" aria-current="{{ $view === $key ? 'page' : 'false' }}">{{ $label }}</a>
                        @endforeach
                    </nav>
                    @if ($canManage)
                        <button type="button" @click="redistribute = !redistribute" class="ak-btn ak-btn-outline" :aria-expanded="redistribute">Redistribute by population %</button>
                    @endif
                </div>

                @if ($canManage)
                    <form x-show="redistribute" x-cloak method="POST" action="{{ route('aksic-budgets.redistribute', $budget) }}"
                        class="bgt-screen-only grid grid-cols-1 items-end gap-4 border-b border-gray-200 bg-gray-50 p-5 md:grid-cols-4">
                        @csrf
                        <p class="text-sm text-gray-700 md:col-span-4">Sets every district allocation to <b>total &times; population %</b>. Stops if any district would fall below the markup it has already used. Every change is recorded in the revision history.</p>
                        <div><label class="{{ $lbl }}">Letter no.</label><input name="reference_no" class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Letter date</label><input type="date" name="reference_date" class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Reason <span class="text-red-600">*</span></label><input name="reason" required class="{{ $control }}"></div>
                        <div><button type="submit" class="ak-btn ak-btn-primary">Redistribute</button></div>
                    </form>
                @endif

                <div class="space-y-6 p-4 sm:p-5">
                    {{-- District-wise --}}
                    <div class="bgt-view {{ $view === 'district' ? '' : 'bgt-alt' }}">
                        <div class="bgt-h"><h3>District-wise position</h3><p>Allocation = total budget &times; district population %</p></div>
                        <div class="overflow-x-auto">
                            <table class="aksic-grid">
                                <thead>
                                    <tr>
                                        <th class="ctr">#</th><th>District</th><th class="num">Population %</th>
                                        <th class="num">Allocation</th><th class="num">Markup used</th><th class="num">Remaining</th>
                                        <th class="ctr">Used</th><th class="ctr">Loans</th>
                                        @if ($canManage) <th class="ctr bgt-screen-only">Revise</th> @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        @php $p = $row['total']['percent']; @endphp
                                        <tr>
                                            <td class="ctr">{{ $loop->iteration }}</td>
                                            <td><b>{{ $row['district'] }}</b></td>
                                            <td class="num">{{ number_format($row['population_percentage'], 2) }}</td>
                                            <td class="num">{{ $m($row['total']['allocated']) }}</td>
                                            <td class="num">{{ $m($row['total']['used']) }}</td>
                                            <td class="num {{ $row['total']['remaining'] < 0 ? 'neg' : '' }}">{{ $m($row['total']['remaining']) }}</td>
                                            <td class="ctr" style="min-width:100px">{{ $p }}%<div class="bar"><span class="{{ $barClass($p) }}" style="width: {{ min(100, $p) }}%"></span></div></td>
                                            <td class="ctr">{{ $row['loans'] }}</td>
                                            @if ($canManage)
                                                <td class="ctr bgt-screen-only" style="white-space:nowrap">
                                                    <button type="button" class="bgt-rev-btn up"
                                                        @click="revise = { action: 'enhancement', id: {{ $row['allocation']->id }}, district: @js($row['district']), allocated: @js($m($row['total']['allocated'])), used: @js($m($row['total']['used'])) }">＋ Enhance</button>
                                                    <button type="button" class="bgt-rev-btn down"
                                                        @click="revise = { action: 'reduction', id: {{ $row['allocation']->id }}, district: @js($row['district']), allocated: @js($m($row['total']['allocated'])), used: @js($m($row['total']['used'])) }">− Reduce</button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td><td>Total</td><td class="num">{{ number_format($rows->sum('population_percentage'), 2) }}</td>
                                        <td class="num">{{ $m($t['allocated']) }}</td><td class="num">{{ $m($t['used']) }}</td>
                                        <td class="num {{ $t['remaining'] < 0 ? 'neg' : '' }}">{{ $m($t['remaining']) }}</td><td class="ctr">{{ $usedPct }}%</td><td class="ctr">{{ $t['loans'] }}</td>
                                        @if ($canManage) <td class="bgt-screen-only"></td> @endif
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Gender-wise and Existing / New --}}
                    @foreach (['gender' => ['genders', AksicBudgetService::GENDERS], 'nature' => ['natures', AksicBudgetService::NATURES]] as $vKey => [$key, $buckets])
                        @php
                            $headerPct = $vKey === 'gender'
                                ? ['male' => $firstRule?->male_percentage ?? 48, 'female' => $firstRule?->female_percentage ?? 48,
                                   'special' => $firstRule?->special_person_percentage ?? 2, 'transgender' => $firstRule?->transgender_percentage ?? 2]
                                : ['existing' => $budget->existing_business_percentage, 'new' => $budget->new_business_percentage];
                        @endphp
                        <div class="bgt-view {{ $view === $vKey ? '' : 'bgt-alt' }}">
                            <div class="bgt-h">
                                <h3>{{ $views[$vKey] }} position</h3>
                                <p>{{ $vKey === 'gender' ? 'Each district allocation split by the gender quota of its AKSIC rule' : 'Each district allocation split by the Existing / New business share of this budget' }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="aksic-grid">
                                    <thead>
                                        <tr class="group">
                                            <th rowspan="2">District</th>
                                            @foreach ($buckets as $k => $label)
                                                <th colspan="3">{{ $label }} ({{ $pctLabel($headerPct[$k]) }}%)</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach ($buckets as $label)
                                                <th class="num">Allocation</th><th class="num">Used</th><th class="num">Remaining</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $row)
                                            <tr>
                                                <td><b>{{ $row['district'] }}</b></td>
                                                @foreach ($buckets as $k => $label)
                                                    @php $b = $row[$key][$k]; @endphp
                                                    <td class="num">{{ $m($b['allocated']) }}</td>
                                                    <td class="num">{{ $m($b['used']) }}</td>
                                                    <td class="num {{ $b['remaining'] < 0 ? 'neg' : '' }}">{{ $m($b['remaining']) }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Total</td>
                                            @foreach ($buckets as $k => $label)
                                                <td class="num">{{ $m($t[$key][$k]['allocated']) }}</td>
                                                <td class="num">{{ $m($t[$key][$k]['used']) }}</td>
                                                <td class="num {{ $t[$key][$k]['remaining'] < 0 ? 'neg' : '' }}">{{ $m($t[$key][$k]['remaining']) }}</td>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Revision history -------------------------------------------------- --}}
            <section class="bgt-section ak-card p-4 sm:p-5">
                <div class="bgt-h">
                    <h3>Revision history</h3>
                    <p>
                        {{ number_format($revisions->total()) }} {{ \Illuminate\Support\Str::plural('change', $revisions->total()) }}
                        @if ($revisions->lastPage() > 1) &middot; page {{ $revisions->currentPage() }} of {{ $revisions->lastPage() }} @endif
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="aksic-grid">
                        <thead>
                            <tr>
                                <th>Date</th><th>Action</th><th>District</th><th class="num">Previous</th><th class="num">Change</th>
                                <th class="num">New</th><th>Letter</th><th>Reason</th><th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($revisions as $rev)
                                <tr>
                                    <td style="white-space:nowrap">{{ $rev->created_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ $actions[$rev->action] ?? $rev->action }}</td>
                                    <td>{{ $rev->district?->name ?? 'Whole budget' }}</td>
                                    <td class="num">{{ $rev->previous_amount === null ? '—' : $m($rev->previous_amount) }}</td>
                                    <td class="num {{ (float) $rev->change_amount < 0 ? 'neg' : '' }}">{{ $rev->change_amount === null ? '—' : ((float) $rev->change_amount > 0 ? '+' : '').$m($rev->change_amount) }}</td>
                                    <td class="num">{{ $rev->new_amount === null ? '—' : $m($rev->new_amount) }}</td>
                                    <td>{{ $rev->reference_no }}{{ $rev->reference_date ? ' ('.$rev->reference_date->format('d.m.Y').')' : '' }}</td>
                                    <td>{{ $rev->reason }}</td>
                                    <td>{{ $rev->creator?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="ctr">No changes recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bgt-screen-only mt-3">{{ $revisions->links() }}</div>
            </section>

            {{-- Sign-off (paper only) ------------------------------------------------ --}}
            <div class="bgt-print-only">
                <div class="bgt-sign"><div>Prepared by</div><div>Checked by</div><div>Approved by</div></div>
            </div>

            {{-- Versions ------------------------------------------------------------ --}}
            @if ($versions->count() > 1)
                <section class="bgt-screen-only ak-card p-4 sm:p-5">
                    <div class="bgt-h"><h3>Budget versions</h3><p>Only the active version is used when cases are approved</p></div>
                    <div class="overflow-x-auto">
                        <table class="aksic-grid">
                            <thead><tr><th>Title</th><th class="num">Total</th><th>Created</th><th class="ctr">Status</th><th class="ctr">Open</th></tr></thead>
                            <tbody>
                                @foreach ($versions as $v)
                                    <tr>
                                        <td>{{ $v->title }} @if ($v->is($budget)) <span class="muted">(this page)</span> @endif</td><td class="num">{{ $m($v->total_amount) }}</td>
                                        <td>{{ $v->created_at?->format('d.m.Y') }}</td>
                                        <td class="ctr">{{ $v->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="ctr"><a class="text-blue-700 hover:underline" href="{{ route('aksic-budgets.show', $v) }}">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        {{-- Enhance / reduce modal ------------------------------------------------------- --}}
        @if ($canManage)
            <div x-show="revise" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4 print:hidden" @keydown.escape.window="revise = null">
                <form method="POST" :action="revise ? '{{ url('product/aksic-budgets/'.$budget->id.'/allocations') }}/' + revise.id + '/revise' : '#'"
                    @click.outside="revise = null" class="w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-xl">
                    @csrf
                    <input type="hidden" name="action" :value="revise?.action">
                    <div class="border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="(revise?.action === 'enhancement' ? 'Enhance' : 'Reduce') + ' allocation — ' + (revise?.district ?? '')"></h3>
                        <p class="mt-1 text-xs text-gray-500">Current allocation <b x-text="revise?.allocated"></b> &middot; used <b x-text="revise?.used"></b></p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-2">
                        <div class="md:col-span-2"><label class="{{ $lbl }}">Amount (Rs) <span class="text-red-600">*</span></label><input name="amount" type="number" step="0.01" min="0.01" required class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Letter no.</label><input name="reference_no" class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Letter date</label><input type="date" name="reference_date" class="{{ $control }}"></div>
                        <div class="md:col-span-2"><label class="{{ $lbl }}">Reason <span class="text-red-600">*</span></label><input name="reason" required class="{{ $control }}"></div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
                            <input type="checkbox" name="adjust_total" value="1" checked class="rounded border-gray-300">
                            <span x-text="revise?.action === 'enhancement' ? 'Also increase the total budget by this amount (new sanction)' : 'Also decrease the total budget by this amount (surrender)'"></span>
                        </label>
                        <p class="text-xs text-gray-500 md:col-span-2">Untick to move money within the existing total (it then shows as allocated / unallocated).</p>
                    </div>
                    <div class="flex justify-end gap-3 bg-gray-100 px-6 py-3">
                        <button type="button" @click="revise = null" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700">Cancel</button>
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-700">Save</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
    <script>
        window.addEventListener('afterprint', () => document.documentElement.classList.remove('bgt-print-all'));
    </script>
</x-app-layout>
