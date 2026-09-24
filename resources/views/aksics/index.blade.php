{{--
    AKSIC loan cases list: /product/aksic

    UI/UX principles used on this screen (reuse them on other list pages):
      1. One primary action (New case); secondary actions grouped in menus.
      2. Numbers the user acts on first: KPI cards double as filters.
      3. Filters sit right above the results they change; visible labels,
         active filters shown as removable chips, one-click reset.
      4. Table built for scanning: text left, money right, units in headers,
         fewer and denser columns, status as dot + word (not colour alone).
      5. Each row has one clear next step (Approve / View) plus quiet icons.
      6. Keyboard and screen-reader friendly: "/" focuses search, aria-sort,
         aria-labels on icon buttons, visible focus rings.
      7. Scales to 1M rows: indexed filters, max 100 rows a page, cached totals.
    Styles are plain CSS (ak- prefix) so nothing depends on a Tailwind rebuild.
--}}
@php
    $ui = \App\Support\Ui::class;
    $user = auth()->user();
    $filters = array_filter((array) request('filter', []), fn ($v) => $v !== null && $v !== '');
    $sort = (string) request('sort', '-created_at');
    $tab = $filters['schedule'] ?? '';
    $chips = \Illuminate\Support\Arr::except($filters, 'schedule');

    // Pakistani units for headline figures; exact value shown on hover.
    $compact = function ($v): string {
        $v = (float) $v;

        return match (true) {
            $v >= 10000000 => number_format($v / 10000000, 2).' Cr',
            $v >= 100000 => number_format($v / 100000, 2).' Lac',
            default => number_format($v, 0),
        };
    };
    $money = fn ($v) => number_format((float) $v, 0);

    // URLs that keep every other query parameter (sort, per_page ...).
    $withFilters = fn (array $f) => request()->fullUrlWithQuery(['filter' => $f ?: null, 'page' => null]);
    $tabUrl = function (string $key) use ($filters, $withFilters) {
        $f = \Illuminate\Support\Arr::except($filters, 'schedule');
        if ($key !== '') {
            $f['schedule'] = $key;
        }

        return $withFilters($f);
    };
    $sortUrl = fn (string $field) => request()->fullUrlWithQuery(['sort' => $sort === $field ? '-'.$field : $field, 'page' => null]);
    $ariaSort = fn (string $field) => $sort === $field ? 'ascending' : ($sort === '-'.$field ? 'descending' : 'none');
    // Arrow only on the sorted column (U+25B2/U+25BC render as text, not emoji).
    $sortMark = fn (string $field) => $sort === $field ? "\u{25B2}" : ($sort === '-'.$field ? "\u{25BC}" : '');

    $chipLabels = [
        'search' => 'Search', 'quota' => 'Quota', 'district_id' => 'District', 'business_name' => 'Business',
        'date_from' => 'Entered from', 'date_to' => 'Entered to', 'amount_min' => 'Min principal',
        'amount_max' => 'Max principal', 'status' => 'Status', 'name' => 'Name', 'cnic' => 'CNIC',
        'application_no' => 'Application', 'district_name' => 'District',
    ];
    $districtNames = $districts->pluck('name', 'id');
    $advancedKeys = ['business_name', 'date_from', 'date_to', 'amount_min', 'amount_max'];
    $advancedCount = collect($advancedKeys)->filter(fn ($k) => isset($filters[$k]))->count();

    $pagePrincipal = $aksics->sum(fn ($a) => (float) $a->principal_amount);
    $pageMarkup = $aksics->sum(fn ($a) => (float) $a->total_interest);
    $isSuperAdmin = $user?->hasRole('super-admin');
    // Feature switches from config/aksic.php (.env AKSIC_EXCEL_IMPORT / AKSIC_EXCEL_EXPORT / AKSIC_DEMO_DATA, off by default).
    $canImport = config('aksic.excel_import') && $user?->can('import aksics');
    $canExport = (bool) config('aksic.excel_export');
    $canDemo = config('aksic.demo_data') && app()->isLocal() && $isSuperAdmin;
    $stats += ['male' => 0, 'female' => 0, 'other' => 0, 'average' => 0];
    $womenShare = $stats['cases'] ? round(($stats['female'] + $stats['other']) / $stats['cases'] * 100) : 0;
@endphp

<x-app-layout>
    {{-- ============================ Page header ============================ --}}
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span><span>AKSIC</span>
                </nav>
                <h1 class="ak-title">AKSIC Loan Cases</h1>
                <p class="ak-sub">
                    PM Youth Loan Scheme &middot;
                    <span class="ak-scope {{ $office['level'] === 'all' ? '' : 'ak-scope-limited' }}" title="What you can see is based on your office">
                        Viewing: {{ $office['label'] }}
                    </span>
                </p>
            </div>

            <div class="ak-head-actions">
                <a href="{{ route('product.index') }}" class="ak-btn ak-btn-outline" title="Back to Product">
                    <span aria-hidden="true">←</span> Back
                </a>

                {{-- Secondary: reports & modules --}}
                <div class="ak-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="ak-btn ak-btn-outline" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                        Reports <span aria-hidden="true">▾</span>
                    </button>
                    <div class="ak-menu-list" x-show="open" x-cloak x-transition.opacity>
                        <a href="{{ route('reports.aksic-rules-report') }}">Rules &amp; loans report</a>
                        @can('view aksic budget') <a href="{{ route('aksic-budgets.index') }}">Markup budget</a> @endcan
                        @can('view aksic claims') <a href="{{ route('aksic-claims.index') }}">Claims</a> @endcan
                    </div>
                </div>

                {{-- Secondary: Excel --}}
                @if ($canImport || $canDemo)
                    <div class="ak-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                        <button type="button" class="ak-btn ak-btn-outline" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                            Import <span aria-hidden="true">▾</span>
                        </button>
                        <div class="ak-menu-list" x-show="open" x-cloak x-transition.opacity>
                            @if ($canImport)
                                <button type="button" @click="open = false; $dispatch('open-import-aksic-modal')">Import from Excel…</button>
                                <a href="{{ route('aksic.template') }}">Download Excel template</a>
                            @endif
                            @if ($canDemo)
                                <form method="POST" action="{{ route('aksic.demo-data') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Creating demo cases…';">
                                    @csrf
                                    <input type="hidden" name="count" value="520">
                                    <button type="submit" style="border-top:1px solid #e2e8f0; margin-top:4px">Local only: replace demo cases (520)</button>
                                </form>
                                <form method="POST" action="{{ route('aksic.demo-data') }}">
                                    @csrf
                                    <input type="hidden" name="count" value="0">
                                    <button type="submit">Local only: remove demo cases</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Output: export the filtered list / print this page as a table --}}
                <div class="ak-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="ak-btn ak-btn-outline" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                        {{ $canExport ? 'Export / Print' : 'Print' }} <span aria-hidden="true">▾</span>
                    </button>
                    <div class="ak-menu-list" x-show="open" x-cloak x-transition.opacity>
                        @if ($canExport)
                            <a href="{{ route('aksic.export', request()->only(['filter', 'sort'])) }}">Export to Excel (CSV) — all {{ number_format($aksics->total()) }} filtered cases</a>
                        @endif
                        <button type="button" @click="open = false; $nextTick(() => window.print())">Print this page ({{ $aksics->count() }} rows)</button>
                    </div>
                </div>

                {{-- Primary --}}
                @can('create aksics')
                    <a href="{{ route('aksic.create') }}" class="ak-btn ak-btn-primary">
                        <span aria-hidden="true">＋</span> New case
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')
    <style>@page { size: A4 landscape; margin: 10mm; }</style>

    <div class="ak-page">
        {{-- Printed report header (Ctrl+P): title, office, filters and totals; hidden on screen --}}
        <div class="ak-print-head">
            <div class="ak-print-bank">The Bank of Azad Jammu &amp; Kashmir</div>
            <div class="ak-print-title">AKSIC Loan Cases &mdash; PM Youth Loan Scheme</div>
            <table class="ak-print-meta">
                <tr><th>Office</th><td>{{ $office['label'] }}</td><th>Printed</th><td>{{ now()->format('d.m.Y H:i') }} by {{ $user?->name }}</td></tr>
                <tr><th>Filters</th><td colspan="3">
                    @php
                        $printFilters = collect($filters)->map(function ($value, $key) use ($chipLabels, $districtNames) {
                            if ($key === 'schedule') {
                                return 'Status: '.($value === 'generated' ? 'Approved' : 'Pending approval');
                            }
                            $shown = $key === 'district_id' ? ($districtNames[$value] ?? $value) : $value;

                            return ($chipLabels[$key] ?? \Illuminate\Support\Str::headline($key)).': '.$shown;
                        })->values();
                    @endphp
                    {{ $printFilters->isEmpty() ? 'None (all cases)' : $printFilters->implode(' · ') }}
                </td></tr>
                <tr><th>Rows</th><td>{{ number_format($aksics->firstItem() ?? 0) }}–{{ number_format($aksics->lastItem() ?? 0) }} of {{ number_format($aksics->total()) }} cases</td>
                    <th>Sorted by</th><td>{{ ltrim($sort, '-') === 'created_at' ? 'Date entered' : \Illuminate\Support\Str::headline(ltrim($sort, '-')) }} ({{ str_starts_with($sort, '-') ? 'descending' : 'ascending' }})</td></tr>
            </table>
        </div>

        {{-- ============================ Messages ============================ --}}
        <x-status-message />
        @if ($errors->any())
            <div class="ak-alert ak-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        @if (session('import_errors'))
            <div class="ak-alert ak-alert-warn" role="status">
                <b>Import skipped {{ count(session('import_errors')) }} row(s).</b>
                <ul>
                    @foreach (array_slice(session('import_errors'), 0, 10) as $importError)
                        <li>{{ $importError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================ KPI cards (also filters) ============================ --}}
        {{-- KPI cards: only figures that are not already on screen (case counts live in
             the tabs). One actionable card (pending) links to its list. --}}
        <section class="ak-kpis" aria-label="Summary for the current filters">
            <a href="{{ $tabUrl('pending') }}" class="ak-kpi{{ $stats['pending'] ? ' ak-kpi-action' : '' }}{{ $tab === 'pending' ? ' is-active' : '' }}" aria-current="{{ $tab === 'pending' ? 'true' : 'false' }}">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Pending approval</span>
                    <span class="ak-kpi-value">{{ number_format($stats['pending']) }}</span>
                    <span class="ak-kpi-hint">{{ $stats['pending'] ? 'Review and approve →' : 'Nothing waiting' }}</span>
                </span>
            </a>
            <div class="ak-kpi" title="Rs {{ $money($stats['principal']) }}">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75h19.5M3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V7.5A1.5 1.5 0 0 1 3.75 6ZM15 11.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Loan amount</span>
                    <span class="ak-kpi-value">Rs {{ $compact($stats['principal']) }}</span>
                    <span class="ak-kpi-hint">avg Rs {{ $compact($stats['average']) }} per case</span>
                </span>
            </div>
            <div class="ak-kpi" title="Rs {{ $money($stats['markup']) }}">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.3 4.3a11.95 11.95 0 0 1 5.8-5.8l2.65-1.2m0 0-5.94-2.28m5.94 2.28-2.28 5.94" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Scheduled markup</span>
                    <span class="ak-kpi-value">Rs {{ $compact($stats['markup']) }}</span>
                    <span class="ak-kpi-hint">on {{ number_format($stats['generated']) }} approved {{ \Illuminate\Support\Str::plural('case', $stats['generated']) }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.13a9.38 9.38 0 0 0 2.63.37 9.34 9.34 0 0 0 4.12-.95 4.13 4.13 0 0 0-7.53-2.49M15 19.13v-.01a6.37 6.37 0 0 0-.97-3.4M15 19.13v.1A12.32 12.32 0 0 1 8.62 21a12.32 12.32 0 0 1-6.37-1.77v-.11a6.38 6.38 0 0 1 11.96-3.4M12 6.38a3.38 3.38 0 1 1-6.75 0 3.38 3.38 0 0 1 6.75 0Zm8.25 2.25a2.63 2.63 0 1 1-5.25 0 2.63 2.63 0 0 1 5.25 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Quota mix</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ number_format($stats['male']) }} M · {{ number_format($stats['female']) }} F · {{ number_format($stats['other']) }} other</span>
                    <span class="ak-kpi-hint">{{ $womenShare }}% female &amp; special quotas</span>
                </span>
            </div>
        </section>

        {{-- ============================ Results card ============================ --}}
        <section class="ak-card" aria-label="Loan cases">
            {{-- Tabs --}}
            <div class="ak-tabs" role="tablist">
                @foreach (['' => ['All', $stats['cases']], 'pending' => ['Pending approval', $stats['pending']], 'generated' => ['Approved', $stats['generated']]] as $key => [$label, $count])
                    <a href="{{ $tabUrl($key) }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" class="ak-tab {{ $tab === $key ? 'is-active' : '' }}">
                        {{ $label }} <span class="ak-count">{{ number_format($count) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('aksic.index') }}" class="ak-filters"
                x-data="{ advanced: {{ $advancedCount ? 'true' : 'false' }}, busy: false }" @submit="busy = true">
                @if ($tab !== '') <input type="hidden" name="filter[schedule]" value="{{ $tab }}"> @endif
                @if (request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if (request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                <div class="ak-filter-row">
                    <div class="ak-field ak-field-search">
                        <label for="f_search">Search</label>
                        <div class="ak-search">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                            <input id="f_search" type="search" name="filter[search]" value="{{ $filters['search'] ?? '' }}" autocomplete="off"
                                placeholder="CNIC, application no, account no or name" aria-describedby="f_search_help"
                                x-ref="search" @keydown.window.slash="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus(); }">
                            <kbd aria-hidden="true">/</kbd>
                        </div>
                        <p id="f_search_help" class="ak-help">Full CNIC gives an exact match; other text matches the start.</p>
                    </div>
                    <div class="ak-field">
                        <label for="f_district">District</label>
                        <select id="f_district" name="filter[district_id]">
                            <option value="">All districts</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}" @selected((string) ($filters['district_id'] ?? '') === (string) $district->id)>{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-field">
                        <label for="f_quota">Quota</label>
                        <select id="f_quota" name="filter[quota]">
                            <option value="">All quotas</option>
                            @foreach (['Male', 'Female', 'Disabled', 'Special Person', 'Transgender'] as $quota)
                                <option value="{{ $quota }}" @selected(($filters['quota'] ?? '') === $quota)>{{ $quota }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-filter-buttons">
                        <button type="button" class="ak-btn ak-btn-ghost" @click="advanced = !advanced" :aria-expanded="advanced" aria-controls="ak-advanced">
                            More filters @if ($advancedCount)<span class="ak-count ak-count-dark">{{ $advancedCount }}</span>@endif
                        </button>
                        <button type="submit" class="ak-btn ak-btn-primary" :disabled="busy">
                            <span x-show="!busy">Apply</span><span x-show="busy" x-cloak>Searching…</span>
                        </button>
                    </div>
                </div>

                <div id="ak-advanced" class="ak-filter-advanced" x-show="advanced" x-cloak x-transition>
                    <div class="ak-field">
                        <label for="f_business">Business name</label>
                        <input id="f_business" type="text" name="filter[business_name]" value="{{ $filters['business_name'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_from">Entered from</label>
                        <input id="f_from" type="date" name="filter[date_from]" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_to">Entered to</label>
                        <input id="f_to" type="date" name="filter[date_to]" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_min">Min principal (Rs)</label>
                        <input id="f_min" type="number" min="0" step="1000" inputmode="numeric" name="filter[amount_min]" value="{{ $filters['amount_min'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_max">Max principal (Rs)</label>
                        <input id="f_max" type="number" min="0" step="1000" inputmode="numeric" name="filter[amount_max]" value="{{ $filters['amount_max'] ?? '' }}">
                    </div>
                </div>
            </form>

            {{-- Active filters --}}
            @if ($chips)
                <div class="ak-chips" aria-label="Active filters">
                    <span class="ak-chips-label">Filtered by</span>
                    @foreach ($chips as $key => $value)
                        <a href="{{ $withFilters(\Illuminate\Support\Arr::except($filters, $key)) }}" class="ak-chip" aria-label="Remove filter {{ $chipLabels[$key] ?? $key }}">
                            <b>{{ $chipLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</b>
                            {{ $key === 'district_id' ? ($districtNames[$value] ?? $value) : $value }}
                            <span aria-hidden="true">×</span>
                        </a>
                    @endforeach
                    <a href="{{ $withFilters($tab !== '' ? ['schedule' => $tab] : []) }}" class="ak-chips-clear">Clear all</a>
                </div>
            @endif

            {{-- Table --}}
            @if ($aksics->count() > 0)
                @php
                    $sortNames = ['name' => 'Applicant', 'application_no' => 'Application no', 'principal_amount' => 'Loan amount',
                        'total_interest' => 'Markup', 'disbursement_date' => 'Disbursed date', 'created_at' => 'Date entered',
                        'tenure' => 'Tenure', 'status' => 'Status', 'cnic' => 'CNIC'];
                    $sortField = ltrim($sort, '-');
                @endphp
                <div class="ak-table-wrap" x-data="{
                        compact: (() => { try { return localStorage.getItem('ak-density') === 'compact'; } catch (e) { return false; } })(),
                        setDensity(v) { this.compact = v; try { localStorage.setItem('ak-density', v ? 'compact' : 'comfortable'); } catch (e) {} }
                    }">
                    <div class="ak-dt-toolbar">
                        <p>
                            <b>{{ number_format($aksics->total()) }}</b> {{ \Illuminate\Support\Str::plural('case', $aksics->total()) }}
                            &middot; sorted by <b>{{ $sortNames[$sortField] ?? 'Date entered' }}</b> ({{ str_starts_with($sort, '-') ? 'newest / highest first' : 'oldest / lowest first' }})
                        </p>
                        <div class="ak-seg ak-seg-sm" role="group" aria-label="Row density">
                            <button type="button" @click="setDensity(false)" :aria-pressed="!compact" :class="!compact && 'is-on'">Comfortable</button>
                            <button type="button" @click="setDensity(true)" :aria-pressed="compact" :class="compact && 'is-on'">Compact</button>
                        </div>
                    </div>
                    <div class="ak-dt-scroll" :class="compact && 'is-compact'" tabindex="0" aria-label="AKSIC cases table, scrolls sideways on small screens">
                        <table class="ak-dt">
                            <caption class="sr-only">AKSIC loan cases, {{ $aksics->total() }} results</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="ak-c ak-sticky-1">#</th>
                                    <th scope="col" class="ak-sticky-2" aria-sort="{{ $ariaSort('name') }}"><a href="{{ $sortUrl('name') }}">Applicant <span aria-hidden="true">{{ $sortMark('name') }}</span></a></th>
                                    <th scope="col" aria-sort="{{ $ariaSort('application_no') }}"><a href="{{ $sortUrl('application_no') }}">Application / A/c <span aria-hidden="true">{{ $sortMark('application_no') }}</span></a></th>
                                    <th scope="col">Branch / District</th>
                                    <th scope="col" class="ak-c">Quota</th>
                                    <th scope="col" class="ak-num" aria-sort="{{ $ariaSort('principal_amount') }}"><a href="{{ $sortUrl('principal_amount') }}">Loan (Rs) <span aria-hidden="true">{{ $sortMark('principal_amount') }}</span></a></th>
                                    <th scope="col" class="ak-num" aria-sort="{{ $ariaSort('total_interest') }}"><a href="{{ $sortUrl('total_interest') }}">Markup (Rs) <span aria-hidden="true">{{ $sortMark('total_interest') }}</span></a></th>
                                    <th scope="col" class="ak-c" aria-sort="{{ $ariaSort('disbursement_date') }}"><a href="{{ $sortUrl('disbursement_date') }}">Disbursed <span aria-hidden="true">{{ $sortMark('disbursement_date') }}</span></a></th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="ak-c ak-sticky-end print:hidden"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aksics as $aksic)
                                    @php
                                        $approved = $aksic->status === 'Approved';
                                        $hasSchedule = $aksic->amortizations_count > 0;
                                        $canModify = ! $hasSchedule || $isSuperAdmin;
                                    @endphp
                                    <tr>
                                        <td class="ak-c ak-sticky-1 ak-muted" data-label="#">{{ $aksics->firstItem() + $loop->index }}</td>
                                        <td data-label="Applicant" class="ak-sticky-2">
                                            <a href="{{ route('aksic.show', $aksic) }}" class="ak-primary-link">{{ $aksic->name }}</a>
                                            @if ($aksic->father_name) <div class="ak-muted ak-hide-compact">S/o, D/o {{ $aksic->father_name }}</div> @endif
                                            <div class="ak-muted ak-mono">{{ $aksic->cnic }}</div>
                                        </td>
                                        <td data-label="Application / A/c">
                                            <div class="ak-mono">{{ $aksic->application_no ?: '—' }}</div>
                                            <div class="ak-muted ak-mono">A/c {{ $aksic->account_no ?: '—' }}</div>
                                        </td>
                                        <td data-label="Branch / District">
                                            <div>{{ $aksic->branch ? $aksic->branch->code.' · '.$aksic->branch->name : '—' }}</div>
                                            <div class="ak-muted">{{ $aksic->district_name ?? $aksic->district?->name ?? '—' }}</div>
                                        </td>
                                        <td class="ak-c" data-label="Quota">
                                            {{ $aksic->quota ?? '—' }}
                                            @if ($aksic->gender && $aksic->gender !== $aksic->quota) <div class="ak-muted">{{ $aksic->gender }}</div> @endif
                                        </td>
                                        <td class="ak-num" data-label="Loan (Rs)">
                                            <div class="ak-strong">{{ number_format((float) $aksic->principal_amount, 0) }}</div>
                                            <div class="ak-muted">{{ $aksic->total_rate === null ? '—' : number_format((float) $aksic->total_rate, 2).'%' }} · {{ $aksic->tenure ? $aksic->tenure.' mo' : '—' }}</div>
                                        </td>
                                        <td class="ak-num" data-label="Markup (Rs)">{{ $aksic->total_interest === null ? '—' : number_format((float) $aksic->total_interest, 0) }}</td>
                                        <td class="ak-c ak-mono" data-label="Disbursed">{{ \App\Support\AksicDate::display($aksic->disbursement_date) }}</td>
                                        <td data-label="Status">
                                            @if ($approved)
                                                <span class="ak-status ak-status-green"><i aria-hidden="true"></i>Approved</span>
                                                <div class="ak-muted ak-hide-compact">{{ $hasSchedule ? $aksic->amortizations_count.' instalments' : 'Schedule missing' }}</div>
                                            @else
                                                <span class="ak-status ak-status-amber"><i aria-hidden="true"></i>Pending</span>
                                                <div class="ak-muted ak-hide-compact">awaiting approval</div>
                                            @endif
                                        </td>
                                        <td class="ak-sticky-end print:hidden" data-label="">
                                            {{-- Icons only: approval happens on the case page (View), after reading the case --}}
                                            <div class="ak-actions">
                                                <a href="{{ $approved ? route('aksic.show', $aksic) : route('aksic.show', ['aksic' => $aksic, 'nav' => 'pending']) }}" class="ak-icon ak-icon-view" title="{{ $approved ? 'View case' : 'View & approve case' }}" aria-label="View case of {{ $aksic->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                </a>
                                                <a href="{{ route('aksic.print', $aksic) }}" target="_blank" rel="noopener" class="ak-icon" title="Print case sheet" aria-label="Print case sheet for {{ $aksic->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z" /></svg>
                                                </a>
                                                <span class="ak-slot">
                                                    @if ($canModify)
                                                        @can('edit aksics')
                                                            <a href="{{ route('aksic.edit', $aksic) }}" class="ak-icon" title="Edit case" aria-label="Edit case of {{ $aksic->name }}">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg>
                                                            </a>
                                                        @endcan
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="ak-foot-label">This page ({{ $aksics->count() }} {{ \Illuminate\Support\Str::plural('case', $aksics->count()) }})</td>
                                    <td class="ak-num">{{ $money($pagePrincipal) }}</td>
                                    <td class="ak-num">{{ $money($pageMarkup) }}</td>
                                    <td colspan="2"></td>
                                    <td class="ak-sticky-end"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="ak-pager">
                    <p>Showing <b>{{ number_format($aksics->firstItem()) }}–{{ number_format($aksics->lastItem()) }}</b> of <b>{{ number_format($aksics->total()) }}</b> cases</p>
                    <div class="ak-pager-right">
                        <form method="GET" action="{{ route('aksic.index') }}" class="ak-perpage">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $k => $v) <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}"> @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()">
                                @foreach (\App\Http\Controllers\AksicController::PER_PAGE as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $aksics->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            @else
                <div class="ak-empty">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                    @if ($filters)
                        <h2>No cases match these filters</h2>
                        <p>Remove a filter or search with a full CNIC.</p>
                        <a href="{{ route('aksic.index') }}" class="ak-btn ak-btn-primary">Clear all filters</a>
                    @else
                        <h2>No AKSIC cases yet</h2>
                        <p>{{ $canImport ? 'Add a case, or import many at once from the Excel template.' : 'Add the first case with “New case”.' }}</p>
                        @can('create aksics') <a href="{{ route('aksic.create') }}" class="ak-btn ak-btn-primary">＋ New case</a> @endcan
                    @endif
                </div>
            @endif
        </section>
    </div>

    @push('modals')
        @if ($canImport)
            @include('aksics._import-modal')
        @endif
    @endpush
</x-app-layout>
