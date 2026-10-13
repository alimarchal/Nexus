{{--
    Account Opening (AOF) list: /product/account-openings
    Same layout as the Settings > Users list: one primary action, KPI cards that double as filters,
    status tabs with counts, one search box with removable filter chips, a sortable dense table
    (comfortable / compact) and a pager with rows-per-page. Only the user's own office is listed
    (AccountOpeningRequest::visibleTo). Query parameters stay filter[...], sort, per_page.
--}}
@php
    $user = auth()->user();
    $filters = array_filter((array) request('filter', []), fn ($v) => $v !== null && $v !== '');
    $sort = (string) request('sort', '-request_date');
    $tab = $filters['status'] ?? '';
    $chips = \Illuminate\Support\Arr::except($filters, 'status');

    $withFilters = fn (array $f) => request()->fullUrlWithQuery(['filter' => $f ?: null, 'page' => null]);
    $tabUrl = function (string $key) use ($filters, $withFilters) {
        $f = \Illuminate\Support\Arr::except($filters, 'status');
        if ($key !== '') {
            $f['status'] = $key;
        }

        return $withFilters($f);
    };
    $sortUrl = fn (string $field) => request()->fullUrlWithQuery(['sort' => $sort === $field ? '-'.$field : $field, 'page' => null]);
    $ariaSort = fn (string $field) => $sort === $field ? 'ascending' : ($sort === '-'.$field ? 'descending' : 'none');
    $sortMark = fn (string $field) => $sort === $field ? "\u{25B2}" : ($sort === '-'.$field ? "\u{25BC}" : '');

    $branchNames = $branches->mapWithKeys(fn ($b) => [$b->id => trim(($b->code ? $b->code.' - ' : '').$b->name)]);
    $formNames = ['individual_joint_sole' => 'Individual / Joint / Sole proprietor', 'entity' => 'Government / Company / NGO'];
    $editionNames = ['new' => 'New form', 'old' => 'Old form'];
    $statusNames = ['draft' => 'Draft', 'submitted' => 'Submitted', 'under_review' => 'Under review', 'approved' => 'Approved', 'rejected' => 'Rejected'];
    $statusTone = ['draft' => 'slate', 'submitted' => 'blue', 'under_review' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
    $chipLabels = [
        'search' => 'Search', 'aof_form_type' => 'Form', 'form_edition' => 'Old / New', 'branch_id' => 'Branch', 'request_date_from' => 'Dated from',
        'request_date_to' => 'Dated to', 'request_number' => 'Request no', 'customer' => 'Customer',
    ];
    $chipValue = fn (string $key, $value) => match ($key) {
        'aof_form_type' => $formNames[$value] ?? $value,
        'form_edition' => $editionNames[$value] ?? $value,
        'branch_id' => $branchNames[$value] ?? $value,
        default => $value,
    };
    $advancedKeys = ['request_date_from', 'request_date_to', 'branch_id'];
    $advancedCount = collect($advancedKeys)->filter(fn ($k) => isset($filters[$k]))->count();
    $sortNames = ['request_date' => 'Request date', 'request_number' => 'Request no', 'status' => 'Status', 'created_at' => 'Date added'];
    $sortField = ltrim($sort, '-');
    $customerName = fn ($request) => $request->customer?->displayName() ?: 'Not captured yet';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span><span>Account opening</span>
                </nav>
                <h1 class="ak-title">Account Opening (AOF)</h1>
                <p class="ak-sub">Customer information, account details and due diligence for every new account.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('product.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back</a>
                <button type="button" class="ak-btn ak-btn-outline" onclick="window.print()">Print</button>
                @can('create account openings')
                    <a href="{{ route('account-openings.create') }}" class="ak-btn ak-btn-primary"><span aria-hidden="true">＋</span> New account opening</a>
                    <a href="{{ route('account-openings.create', ['edition' => 'old']) }}" class="ak-btn ak-btn-primary"><span aria-hidden="true">＋</span> Old account opening</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>@page { size: A4 landscape; margin: 10mm; }</style>

    <div class="ak-page" x-data="{ confirm: { open: false, url: '', number: '' } }"
        @open-delete-aof-modal.window="confirm = { open: true, url: $event.detail.url, number: $event.detail.number }">
        <div class="ak-print-head">
            <div class="ak-print-bank">The Bank of Azad Jammu &amp; Kashmir</div>
            <div class="ak-print-title">Account Opening Requests</div>
            <table class="ak-print-meta">
                <tr><th>Filters</th><td>{{ $filters ? collect($filters)->map(fn ($v, $k) => $k === 'status' ? 'Status: '.($statusNames[$v] ?? $v) : ($chipLabels[$k] ?? \Illuminate\Support\Str::headline($k)).': '.$chipValue($k, $v))->values()->implode(' · ') : 'None (all requests)' }}</td>
                    <th>Printed</th><td>{{ now()->format('d.m.Y H:i') }} by {{ $user?->name }}</td></tr>
            </table>
        </div>

        <x-status-message />
        @if ($errors->any())
            <div class="ak-alert ak-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- KPI cards (also filters) --}}
        <section class="ak-kpis" aria-label="Summary for the current filters">
            <a href="{{ $tabUrl('') }}" class="ak-kpi{{ $tab === '' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Requests</span>
                    <span class="ak-kpi-value">{{ number_format($stats['total']) }}</span>
                    <span class="ak-kpi-hint">{{ number_format($stats['this_month']) }} started this month</span>
                </span>
            </a>
            <a href="{{ $tabUrl('draft') }}" class="ak-kpi{{ $tab === 'draft' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">In progress</span>
                    <span class="ak-kpi-value">{{ number_format($stats['by_status']['draft']) }}</span>
                    <span class="ak-kpi-hint">drafts still being filled</span>
                </span>
            </a>
            <a href="{{ $tabUrl('submitted') }}" class="ak-kpi{{ $stats['awaiting'] ? ' ak-kpi-action' : '' }}{{ $tab === 'submitted' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Awaiting approval</span>
                    <span class="ak-kpi-value">{{ number_format($stats['awaiting']) }}</span>
                    <span class="ak-kpi-hint">{{ $stats['awaiting'] ? 'Submitted, needs a decision →' : 'Nothing waiting' }}</span>
                </span>
            </a>
            <a href="{{ $tabUrl('approved') }}" class="ak-kpi{{ $tab === 'approved' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Approved</span>
                    <span class="ak-kpi-value">{{ number_format($stats['by_status']['approved']) }}</span>
                    <span class="ak-kpi-hint">{{ number_format($stats['by_status']['rejected']) }} rejected</span>
                </span>
            </a>
        </section>

        <section class="ak-card" aria-label="Account opening requests">
            <div class="ak-tabs" role="tablist">
                <a href="{{ $tabUrl('') }}" role="tab" aria-selected="{{ $tab === '' ? 'true' : 'false' }}" class="ak-tab {{ $tab === '' ? 'is-active' : '' }}">All <span class="ak-count">{{ number_format($stats['total']) }}</span></a>
                @foreach ($statusNames as $key => $label)
                    <a href="{{ $tabUrl($key) }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" class="ak-tab {{ $tab === $key ? 'is-active' : '' }}">
                        {{ $label }} <span class="ak-count">{{ number_format($stats['by_status'][$key]) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('account-openings.index') }}" class="ak-filters"
                x-data="{ advanced: {{ $advancedCount ? 'true' : 'false' }}, busy: false }" @submit="busy = true">
                @if ($tab !== '') <input type="hidden" name="filter[status]" value="{{ $tab }}"> @endif
                @if (request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if (request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                <div class="ak-filter-row">
                    <div class="ak-field ak-field-search">
                        <label for="f_search">Search</label>
                        <div class="ak-search">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                            <input id="f_search" type="search" name="filter[search]" value="{{ $filters['search'] ?? '' }}" autocomplete="off"
                                placeholder="Request no, CIF, customer name or account no" x-ref="search"
                                @keydown.window.slash="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus(); }">
                            <kbd aria-hidden="true">/</kbd>
                        </div>
                    </div>
                    <div class="ak-field">
                        <label for="f_form">Form</label>
                        <select id="f_form" name="filter[aof_form_type]">
                            <option value="">All forms</option>
                            @foreach ($formNames as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['aof_form_type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-field">
                        <label for="f_edition">Old / New</label>
                        <select id="f_edition" name="filter[form_edition]">
                            <option value="">Old and new</option>
                            @foreach ($editionNames as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['form_edition'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-filter-buttons">
                        <button type="button" class="ak-btn ak-btn-ghost" @click="advanced = !advanced" :aria-expanded="advanced" aria-controls="aof-advanced">
                            More filters @if ($advancedCount)<span class="ak-count ak-count-dark">{{ $advancedCount }}</span>@endif
                        </button>
                        <button type="submit" class="ak-btn ak-btn-primary" :disabled="busy">
                            <span x-show="!busy">Apply</span><span x-show="busy" x-cloak>Searching…</span>
                        </button>
                    </div>
                </div>

                <div id="aof-advanced" class="ak-filter-advanced" x-show="advanced" x-cloak x-transition>
                    <div class="ak-field">
                        <label for="f_from">Request dated from</label>
                        <input id="f_from" type="date" name="filter[request_date_from]" value="{{ $filters['request_date_from'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_to">Request dated to</label>
                        <input id="f_to" type="date" name="filter[request_date_to]" value="{{ $filters['request_date_to'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_branch">Branch</label>
                        <select id="f_branch" name="filter[branch_id]">
                            <option value="">All branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branchNames[$branch->id] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if ($chips)
                <div class="ak-chips" aria-label="Active filters">
                    <span class="ak-chips-label">Filtered by</span>
                    @foreach ($chips as $key => $value)
                        <a href="{{ $withFilters(\Illuminate\Support\Arr::except($filters, $key)) }}" class="ak-chip" aria-label="Remove filter {{ $chipLabels[$key] ?? $key }}">
                            <b>{{ $chipLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</b> {{ $chipValue($key, $value) }} <span aria-hidden="true">×</span>
                        </a>
                    @endforeach
                    <a href="{{ $withFilters($tab !== '' ? ['status' => $tab] : []) }}" class="ak-chips-clear">Clear all</a>
                </div>
            @endif

            @if ($accountOpeningRequests->count() > 0)
                <div class="ak-table-wrap" x-data="{
                        compact: (() => { try { return localStorage.getItem('aof-density') === 'compact'; } catch (e) { return false; } })(),
                        setDensity(v) { this.compact = v; try { localStorage.setItem('aof-density', v ? 'compact' : 'comfortable'); } catch (e) {} }
                    }">
                    <div class="ak-dt-toolbar">
                        <p>
                            <b>{{ number_format($accountOpeningRequests->total()) }}</b> {{ \Illuminate\Support\Str::plural('request', $accountOpeningRequests->total()) }}
                            &middot; sorted by <b>{{ $sortNames[$sortField] ?? 'Request date' }}</b> ({{ str_starts_with($sort, '-') ? 'newest / Z–A first' : 'oldest / A–Z first' }})
                        </p>
                        <div class="ak-seg ak-seg-sm" role="group" aria-label="Row density">
                            <button type="button" @click="setDensity(false)" :aria-pressed="!compact" :class="!compact && 'is-on'">Comfortable</button>
                            <button type="button" @click="setDensity(true)" :aria-pressed="compact" :class="compact && 'is-on'">Compact</button>
                        </div>
                    </div>
                    <div class="ak-dt-scroll" :class="compact && 'is-compact'" tabindex="0" aria-label="Account opening table, scrolls sideways on small screens">
                        <table class="ak-dt">
                            <caption class="sr-only">Account opening requests, {{ $accountOpeningRequests->total() }} results</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="ak-c ak-sticky-1">#</th>
                                    <th scope="col" class="ak-sticky-2" aria-sort="{{ $ariaSort('request_number') }}"><a href="{{ $sortUrl('request_number') }}">Request <span aria-hidden="true">{{ $sortMark('request_number') }}</span></a></th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Form / category</th>
                                    <th scope="col">Account</th>
                                    <th scope="col" class="ak-c" aria-sort="{{ $ariaSort('request_date') }}"><a href="{{ $sortUrl('request_date') }}">Dated <span aria-hidden="true">{{ $sortMark('request_date') }}</span></a></th>
                                    <th scope="col" aria-sort="{{ $ariaSort('status') }}"><a href="{{ $sortUrl('status') }}">Status <span aria-hidden="true">{{ $sortMark('status') }}</span></a></th>
                                    <th scope="col" class="ak-c ak-sticky-end print:hidden"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accountOpeningRequests as $accountOpeningRequest)
                                    <tr>
                                        <td class="ak-c ak-sticky-1 ak-muted" data-label="#">{{ $accountOpeningRequests->firstItem() + $loop->index }}</td>
                                        <td class="ak-sticky-2" data-label="Request">
                                            <a href="{{ route('account-openings.show', $accountOpeningRequest) }}" class="ak-primary-link ak-mono" style="white-space:nowrap">{{ $accountOpeningRequest->request_number }}</a>
                                            <div class="ak-muted ak-hide-compact">{{ $accountOpeningRequest->branch?->code }} · {{ $accountOpeningRequest->branch?->name }}</div>
                                        </td>
                                        <td data-label="Customer">
                                            <div>{{ $customerName($accountOpeningRequest) }}</div>
                                            <div class="ak-muted ak-hide-compact ak-mono">CIF {{ $accountOpeningRequest->customer?->cif_number ?? '—' }}</div>
                                        </td>
                                        <td data-label="Form / category">
                                            <div>{{ $accountOpeningRequest->aof_form_type === 'entity' ? 'Entity' : 'Individual' }} <span class="ak-muted" data-form-edition>({{ $accountOpeningRequest->editionLabel() }} form)</span></div>
                                            <div class="ak-muted ak-hide-compact">{{ $accountOpeningRequest->customer?->customerCategory?->name ?? '—' }}</div>
                                        </td>
                                        <td data-label="Account">
                                            @if ($accountOpeningRequest->account)
                                                <div class="ak-mono">{{ $accountOpeningRequest->account->account_number }}</div>
                                                <div class="ak-muted ak-hide-compact">{{ $accountOpeningRequest->account->product?->code }}</div>
                                            @else
                                                <span class="ak-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="ak-c ak-mono" data-label="Dated">{{ $accountOpeningRequest->request_date?->format('d.m.Y') ?? '—' }}</td>
                                        <td data-label="Status">
                                            <span class="ak-status ak-status-{{ $statusTone[$accountOpeningRequest->status] ?? 'slate' }}"><i aria-hidden="true"></i>{{ $statusNames[$accountOpeningRequest->status] ?? \Illuminate\Support\Str::headline($accountOpeningRequest->status) }}</span>
                                        </td>
                                        <td class="ak-sticky-end print:hidden" data-label="" style="min-width:160px">
                                            <div class="ak-actions" style="grid-template-columns: repeat(4, 32px)">
                                                <a href="{{ route('account-openings.show', $accountOpeningRequest) }}" class="ak-icon ak-icon-view" title="View request" aria-label="View {{ $accountOpeningRequest->request_number }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                </a>
                                                <span class="ak-slot">
                                                    @can('edit account openings')
                                                        <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, 'cif']) }}" class="ak-icon" title="Continue form" aria-label="Continue {{ $accountOpeningRequest->request_number }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg>
                                                        </a>
                                                    @endcan
                                                </span>
                                                <span class="ak-slot">
                                                    <a href="{{ route('account-openings.print', $accountOpeningRequest) }}" target="_blank" class="ak-icon" title="Print form" aria-label="Print {{ $accountOpeningRequest->request_number }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                                                    </a>
                                                </span>
                                                <span class="ak-slot">
                                                    @can('delete account openings')
                                                        <button type="button" class="ak-icon ak-icon-danger" title="Delete request" aria-label="Delete {{ $accountOpeningRequest->request_number }}"
                                                            @click="$dispatch('open-delete-aof-modal', { url: @js(route('account-openings.destroy', $accountOpeningRequest)), number: @js($accountOpeningRequest->request_number) })">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67A2.25 2.25 0 0 1 15.92 21.75H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0" /></svg>
                                                        </button>
                                                    @endcan
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ak-pager">
                    <p>Showing <b>{{ number_format($accountOpeningRequests->firstItem()) }}–{{ number_format($accountOpeningRequests->lastItem()) }}</b> of <b>{{ number_format($accountOpeningRequests->total()) }}</b> requests</p>
                    <div class="ak-pager-right">
                        <form method="GET" action="{{ route('account-openings.index') }}" class="ak-perpage">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $k => $v) <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}"> @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()">
                                @foreach (\App\Http\Controllers\AccountOpeningController::PER_PAGE as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $accountOpeningRequests->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            @else
                <div class="ak-empty">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    @if ($filters)
                        <h2>No requests match these filters</h2>
                        <p>Remove a filter or search with part of a request number, CIF or name.</p>
                        <a href="{{ route('account-openings.index') }}" class="ak-btn ak-btn-primary">Clear all filters</a>
                    @else
                        <h2>No account opening requests yet</h2>
                        <p>Start the first one and the wizard will take you through every section.</p>
                        @can('create account openings') <a href="{{ route('account-openings.create') }}" class="ak-btn ak-btn-primary">＋ New account opening</a> <a href="{{ route('account-openings.create', ['edition' => 'old']) }}" class="ak-btn ak-btn-primary">＋ Old account opening</a> @endcan
                    @endif
                </div>
            @endif
        </section>

        {{-- Delete confirmation --}}
        <div class="uf-modal" x-show="confirm.open" x-cloak style="display:none" @keydown.escape.window="confirm.open = false" role="dialog" aria-modal="true" aria-labelledby="aof-del-title">
            <div class="uf-modal-bg" x-show="confirm.open" x-transition.opacity @click="confirm.open = false"></div>
            <div class="uf-modal-box" x-show="confirm.open" x-transition>
                <form method="POST" :action="confirm.url">
                    @csrf
                    @method('DELETE')
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon ak-pill-red" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg>
                        </span>
                        <div>
                            <h3 id="aof-del-title" x-text="'Delete ' + confirm.number + '?'"></h3>
                            <p style="margin:8px 0 0; font-size:14px; color:#334155">The captured CIF, account and due diligence records linked to this request will no longer be reachable from here. This cannot be undone.</p>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn ak-btn-outline" @click="confirm.open = false">Cancel</button>
                        <button type="submit" class="ak-btn ak-btn-danger-outline">Delete request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
