{{--
    File management list: /product/file-management-systems
    Same layout and principles as the AKSIC case list (aksics/index): one
    primary action, KPI cards, status tabs with counts, filters with removable
    chips, a sortable scan-friendly table, quiet row icons and a print layout.
    Only the user's own office's files are listed (FileManagementSystem::visibleTo).
--}}
@php
    $user = auth()->user();
    $filters = array_filter((array) request('filter', []), fn ($v) => $v !== null && $v !== '');
    $sort = (string) request('sort', '-document_date');
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

    $categoryNames = $fileCategories->pluck('category_name', 'id');
    $branchNames = $branches->mapWithKeys(fn ($b) => [$b->id => trim(($b->code ? $b->code.' - ' : '').$b->name)]);
    $regionNames = $regions->pluck('name', 'id');
    $divisionNames = $divisions->pluck('name', 'id');
    $chipLabels = [
        'search' => 'Search', 'file_category_id' => 'Category', 'branch_id' => 'Branch', 'region_id' => 'Region',
        'division_id' => 'Division', 'document_date_from' => 'Dated from', 'document_date_to' => 'Dated to',
        'box_number' => 'Box', 'digital_id' => 'Digital ID', 'file_no' => 'File no', 'title' => 'Title',
    ];
    $chipValue = fn ($key, $value) => match ($key) {
        'file_category_id' => $categoryNames[$value] ?? $value,
        'branch_id' => $branchNames[$value] ?? $value,
        'region_id' => $regionNames[$value] ?? $value,
        'division_id' => $divisionNames[$value] ?? $value,
        default => $value,
    };
    $advancedKeys = ['document_date_from', 'document_date_to', 'box_number', 'branch_id', 'region_id', 'division_id'];
    $advancedCount = collect($advancedKeys)->filter(fn ($k) => isset($filters[$k]))->count();
    $sortNames = ['document_date' => 'Document date', 'created_at' => 'Date added', 'digital_id' => 'Digital ID', 'file_no' => 'File no', 'title' => 'Title'];
    $canExport = $user?->can('export file management systems');
    $pageIds = $fileManagementSystems->pluck('id')->values();
    $officeName = fn ($file) => $file->fileable_type === 'division'
        ? ($file->fileable?->short_name ?: $file->fileable_name)
        : trim((($file->fileable?->code ?? null) ? $file->fileable->code.' · ' : '').($file->fileable_name ?? ''));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span><span>File management</span>
                </nav>
                <h1 class="ak-title">File Management</h1>
                <p class="ak-sub">
                    Digitised records, archive boxes and transfers &middot;
                    <span class="ak-scope" title="What you can see is based on your office">Viewing: {{ $officeLabel }}</span>
                </p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('product.index') }}" class="ak-btn ak-btn-outline" title="Back to Product"><span aria-hidden="true">←</span> Back</a>

                <div class="ak-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="ak-btn ak-btn-outline" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                        More <span aria-hidden="true">▾</span>
                    </button>
                    <div class="ak-menu-list" x-show="open" x-cloak x-transition.opacity>
                        @can('manage boxes') <a href="{{ route('file-management-systems.boxes') }}">Archive boxes</a> @endcan
                        @can('view file categories') <a href="{{ route('file-categories.index') }}">File categories</a> @endcan
                    </div>
                </div>

                {{-- Output: print this page / export the filtered list and its scanned files --}}
                <div class="ak-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="ak-btn ak-btn-outline" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                        {{ $canExport ? 'Export / Print' : 'Print' }} <span aria-hidden="true">▾</span>
                    </button>
                    <div class="ak-menu-list" x-show="open" x-cloak x-transition.opacity style="min-width:300px">
                        <button type="button" @click="open = false; $nextTick(() => window.print())">Print this page ({{ $fileManagementSystems->count() }} rows)</button>
                        @if ($canExport && $fileManagementSystems->total() > 0)
                            <a href="{{ route('file-management-systems.export-csv', request()->only(['filter', 'sort'])) }}">Export list to Excel (CSV) &mdash; all {{ number_format($fileManagementSystems->total()) }} filtered files</a>
                            <a href="{{ route('file-management-systems.export-zip', request()->only(['filter', 'sort'])) }}"
                                @click="open = false; $dispatch('fms-zip-started')">Download scanned files (ZIP) &mdash; all {{ number_format($fileManagementSystems->total()) }} filtered files</a>
                            <span style="display:block;padding:6px 14px;font-size:11px;color:#64748b">ZIP: one folder per file with all its pages and documents, plus an index. Tick rows in the table to download only those.</span>
                        @endif
                    </div>
                </div>

                @can('create file management systems')
                    <a href="{{ route('file-management-systems.create') }}" class="ak-btn ak-btn-primary"><span aria-hidden="true">＋</span> New file</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        .fm-pill { display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; background: var(--ak-navy-tint); color: var(--ak-navy); white-space: nowrap; }
        .fm-pill.box { background: #ede9fe; color: #5b21b6; }
        .ak-status-slate { background: #e2e8f0; color: #334155; }
        .ak-status-blue { background: #dbeafe; color: #1e3a8a; }
        .fm-check { width: 16px; height: 16px; border-radius: 4px; border: 1px solid #64748b; color: var(--ak-navy); cursor: pointer; vertical-align: middle; }
        .fm-rowno { display: block; font-size: 11px; margin-top: 2px; }
        .fm-selbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0 16px 12px; padding: 10px 14px; border: 1px solid #c7d2fe; border-radius: 10px; background: #eef2ff; }
        .fm-selbar b { margin-right: auto; color: var(--ak-navy); font-size: 14px; }
        .fm-toast { position: fixed; right: 20px; bottom: 20px; z-index: 60; max-width: 360px; padding: 12px 16px; border-radius: 10px; background: #0f172a; color: #fff; font-size: 13px; box-shadow: 0 10px 25px rgba(0,0,0,.2); }
        @media print { .fm-check, .fm-selbar, .fm-toast { display: none !important; } .fm-rowno { font-size: inherit; margin: 0; } }
    </style>
    {{-- Building a large ZIP takes a few seconds: tell the user it is on its way --}}
    <div x-data="{ show: false }" @fms-zip-started.window="show = true; setTimeout(() => show = false, 8000)" x-show="show" x-cloak x-transition class="fm-toast" role="status">
        Preparing the ZIP&hellip; the download starts by itself when it is ready.
    </div>

    <div class="ak-page">
        <div class="ak-print-head">
            <div class="ak-print-bank">The Bank of Azad Jammu &amp; Kashmir</div>
            <div class="ak-print-title">File Management &mdash; Document Records</div>
            <table class="ak-print-meta">
                <tr><th>Office</th><td>{{ $officeLabel }}</td><th>Printed</th><td>{{ now()->format('d.m.Y H:i') }} by {{ $user?->name }}</td></tr>
                <tr><th>Filters</th><td colspan="3">
                    {{ collect($filters)->map(fn ($v, $k) => $k === 'status' ? 'Status: '.($v === 'archived' ? 'Archived' : 'In circulation') : ($chipLabels[$k] ?? \Illuminate\Support\Str::headline($k)).': '.$chipValue($k, $v))->values()->implode(' · ') ?: 'None (all files)' }}
                </td></tr>
                <tr><th>Rows</th><td>{{ number_format($fileManagementSystems->firstItem() ?? 0) }}–{{ number_format($fileManagementSystems->lastItem() ?? 0) }} of {{ number_format($fileManagementSystems->total()) }} files</td>
                    <th>Sorted by</th><td>{{ $sortNames[ltrim($sort, '-')] ?? 'Document date' }} ({{ str_starts_with($sort, '-') ? 'descending' : 'ascending' }})</td></tr>
            </table>
        </div>

        <x-status-message />
        @if ($errors->any())
            <div class="ak-alert ak-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- KPI cards --}}
        <section class="ak-kpis" aria-label="Summary for the current filters">
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Files</span>
                    <span class="ak-kpi-value">{{ number_format($stats['files']) }}</span>
                    <span class="ak-kpi-hint">{{ number_format($stats['this_month']) }} added this month</span>
                </span>
            </div>
            <a href="{{ $tabUrl('active') }}" class="ak-kpi{{ $tab === 'active' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">In circulation</span>
                    <span class="ak-kpi-value">{{ number_format($stats['active']) }}</span>
                    <span class="ak-kpi-hint">not yet archived in a box</span>
                </span>
            </a>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Scanned pages</span>
                    <span class="ak-kpi-value">{{ number_format($stats['pages']) }}</span>
                    <span class="ak-kpi-hint">{{ $stats['files'] ? number_format($stats['pages'] / $stats['files'], 1) : 0 }} per file</span>
                </span>
            </div>
            <div class="ak-kpi{{ $stats['incoming'] ? ' ak-kpi-action' : '' }}">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Incoming transfers</span>
                    <span class="ak-kpi-value">{{ number_format($stats['incoming']) }}</span>
                    <span class="ak-kpi-hint">{{ $stats['incoming'] ? 'Awaiting a decision' : 'None waiting' }}</span>
                </span>
            </div>
        </section>

        {{-- Results card --}}
        <section class="ak-card" aria-label="Files">
            <div class="ak-tabs" role="tablist">
                @foreach (['' => ['All', $stats['files']], 'active' => ['In circulation', $stats['active']], 'archived' => ['Archived', $stats['archived']]] as $key => [$label, $count])
                    <a href="{{ $tabUrl($key) }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" class="ak-tab {{ $tab === $key ? 'is-active' : '' }}">
                        {{ $label }} <span class="ak-count">{{ number_format($count) }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('file-management-systems.index') }}" class="ak-filters"
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
                                placeholder="Digital ID, file no or title" aria-describedby="f_search_help"
                                x-ref="search" @keydown.window.slash="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus(); }">
                            <kbd aria-hidden="true">/</kbd>
                        </div>
                        <p id="f_search_help" class="ak-help">Digital ID and file no match from the start; title matches anywhere.</p>
                    </div>
                    <div class="ak-field">
                        <label for="f_category">Category</label>
                        <select id="f_category" name="filter[file_category_id]">
                            <option value="">All categories</option>
                            @foreach ($fileCategories as $category)
                                <option value="{{ $category->id }}" @selected(($filters['file_category_id'] ?? '') === (string) $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-filter-buttons">
                        <button type="button" class="ak-btn ak-btn-ghost" @click="advanced = !advanced" :aria-expanded="advanced" aria-controls="fm-advanced">
                            More filters @if ($advancedCount)<span class="ak-count ak-count-dark">{{ $advancedCount }}</span>@endif
                        </button>
                        <button type="submit" class="ak-btn ak-btn-primary" :disabled="busy">
                            <span x-show="!busy">Apply</span><span x-show="busy" x-cloak>Searching…</span>
                        </button>
                    </div>
                </div>

                <div id="fm-advanced" class="ak-filter-advanced" x-show="advanced" x-cloak x-transition>
                    <div class="ak-field">
                        <label for="f_from">Document dated from</label>
                        <input id="f_from" type="date" name="filter[document_date_from]" value="{{ $filters['document_date_from'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_to">Document dated to</label>
                        <input id="f_to" type="date" name="filter[document_date_to]" value="{{ $filters['document_date_to'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_box">Box number</label>
                        <input id="f_box" type="text" name="filter[box_number]" value="{{ $filters['box_number'] ?? '' }}">
                    </div>
                    @if ($isSuperAdmin)
                        <div class="ak-field">
                            <label for="f_branch">Branch</label>
                            <select id="f_branch" name="filter[branch_id]">
                                <option value="">All branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branchNames[$branch->id] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ak-field">
                            <label for="f_region">Region</label>
                            <select id="f_region" name="filter[region_id]">
                                <option value="">All regions</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected((string) ($filters['region_id'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ak-field">
                            <label for="f_division">Division</label>
                            <select id="f_division" name="filter[division_id]">
                                <option value="">All divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $division->id)>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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

            @if ($fileManagementSystems->count() > 0)
                {{-- Row density: same switch (and saved choice) as the AKSIC list --}}
                <div class="ak-table-wrap" x-data="{
                        compact: (() => { try { return localStorage.getItem('ak-density') === 'compact'; } catch (e) { return false; } })(),
                        setDensity(v) { this.compact = v; try { localStorage.setItem('ak-density', v ? 'compact' : 'comfortable'); } catch (e) {} },
                        pageIds: @js($pageIds),
                        selected: [],
                        get allOnPage() { return this.selected.length === this.pageIds.length; },
                        toggleAll(on) { this.selected = on ? [...this.pageIds] : []; },
                    }">
                    @if ($canExport)
                        {{-- Selection bar: appears when rows are ticked --}}
                        <div class="fm-selbar" x-show="selected.length" x-cloak x-transition role="region" aria-label="Selected files">
                            <b x-text="selected.length + (selected.length === 1 ? ' file selected' : ' files selected')"></b>
                            <form method="POST" action="{{ route('file-management-systems.export-zip') }}" @submit="$dispatch('fms-zip-started')">
                                @csrf
                                <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                                <button type="submit" class="ak-btn ak-btn-primary">Download selected (ZIP)</button>
                            </form>
                            <form method="POST" action="{{ route('file-management-systems.export-csv') }}">
                                @csrf
                                <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                                <button type="submit" class="ak-btn ak-btn-outline">Export selected (CSV)</button>
                            </form>
                            <button type="button" class="ak-btn ak-btn-ghost" @click="selected = []">Clear selection</button>
                        </div>
                    @endif
                    <div class="ak-dt-toolbar">
                        <p>
                            <b>{{ number_format($fileManagementSystems->total()) }}</b> {{ \Illuminate\Support\Str::plural('file', $fileManagementSystems->total()) }}
                            &middot; sorted by <b>{{ $sortNames[ltrim($sort, '-')] ?? 'Document date' }}</b> ({{ str_starts_with($sort, '-') ? 'newest first' : 'oldest first' }})
                        </p>
                        <div class="ak-seg ak-seg-sm" role="group" aria-label="Row density">
                            <button type="button" @click="setDensity(false)" :aria-pressed="!compact" :class="!compact && 'is-on'">Comfortable</button>
                            <button type="button" @click="setDensity(true)" :aria-pressed="compact" :class="compact && 'is-on'">Compact</button>
                        </div>
                    </div>
                    <div class="ak-dt-scroll" :class="compact && 'is-compact'" tabindex="0" aria-label="Files table, scrolls sideways on small screens">
                        <table class="ak-dt">
                            <caption class="sr-only">Document records, {{ $fileManagementSystems->total() }} results</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="ak-c ak-sticky-1">
                                        @if ($canExport)
                                            <input type="checkbox" class="fm-check" :checked="allOnPage" @change="toggleAll($event.target.checked)" aria-label="Select all files on this page">
                                        @else
                                            #
                                        @endif
                                    </th>
                                    <th scope="col" class="ak-sticky-2" aria-sort="{{ $ariaSort('title') }}"><a href="{{ $sortUrl('title') }}">Document <span aria-hidden="true">{{ $sortMark('title') }}</span></a></th>
                                    <th scope="col" aria-sort="{{ $ariaSort('digital_id') }}"><a href="{{ $sortUrl('digital_id') }}">Digital ID / File no <span aria-hidden="true">{{ $sortMark('digital_id') }}</span></a></th>
                                    <th scope="col">Office</th>
                                    <th scope="col" class="ak-c" aria-sort="{{ $ariaSort('document_date') }}"><a href="{{ $sortUrl('document_date') }}">Dated <span aria-hidden="true">{{ $sortMark('document_date') }}</span></a></th>
                                    <th scope="col" class="ak-num">Pages</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="ak-c ak-sticky-end print:hidden"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fileManagementSystems as $file)
                                    <tr>
                                        <td class="ak-c ak-sticky-1 ak-muted" data-label="#">
                                            @if ($canExport)
                                                <input type="checkbox" class="fm-check" value="{{ $file->id }}" x-model="selected" aria-label="Select file {{ $file->digital_id }}">
                                            @endif
                                            <span class="fm-rowno">{{ $fileManagementSystems->firstItem() + $loop->index }}</span>
                                        </td>
                                        <td class="ak-sticky-2" data-label="Document">
                                            <a href="{{ route('file-management-systems.show', $file) }}" class="ak-primary-link">{{ $file->title ?: ($file->file_no ?: $file->digital_id) }}</a>
                                            <div class="ak-muted ak-hide-compact">{{ $file->fileCategory?->category_name ?? 'Uncategorised' }}</div>
                                        </td>
                                        <td data-label="Digital ID / File no">
                                            <div class="ak-mono">{{ $file->digital_id }}</div>
                                            <div class="ak-muted ak-mono">{{ $file->file_no ?: '—' }}</div>
                                        </td>
                                        <td data-label="Office">
                                            <div>{{ $officeName($file) ?: '—' }}</div>
                                            <div class="ak-muted ak-hide-compact">{{ $file->fileable_label }}{{ $file->currentCustodian ? ' · with '.$file->currentCustodian->name : '' }}</div>
                                        </td>
                                        <td class="ak-c ak-mono" data-label="Dated">{{ $file->document_date?->format('d.m.Y') ?? '—' }}</td>
                                        <td class="ak-num" data-label="Pages"><span class="fm-pill">{{ $file->pages_count }}</span></td>
                                        <td data-label="Status">
                                            @if ($file->pending_transfers_count)
                                                <span class="ak-status ak-status-amber"><i aria-hidden="true"></i>Transfer pending</span>
                                            @elseif ($file->is_archived)
                                                <span class="ak-status ak-status-slate"><i aria-hidden="true"></i>Archived</span>
                                                <div class="ak-muted ak-hide-compact">{{ $file->box?->box_number }}{{ $file->position_in_box ? ' · pos '.$file->position_in_box : '' }}</div>
                                            @else
                                                <span class="ak-status ak-status-blue"><i aria-hidden="true"></i>In circulation</span>
                                            @endif
                                        </td>
                                        <td class="ak-sticky-end print:hidden" data-label="">
                                            <div class="ak-actions">
                                                <a href="{{ route('file-management-systems.show', $file) }}" class="ak-icon ak-icon-view" title="View file" aria-label="View file {{ $file->digital_id }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                </a>
                                                <span class="ak-slot">
                                                    @can('edit file management systems')
                                                        <a href="{{ route('file-management-systems.edit', $file) }}" class="ak-icon" title="Edit file" aria-label="Edit file {{ $file->digital_id }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg>
                                                        </a>
                                                    @endcan
                                                </span>
                                                <span class="ak-slot">
                                                    @can('transfer file management systems')
                                                        @unless ($file->is_archived)
                                                            <a href="{{ route('file-management-systems.transfer', $file) }}" class="ak-icon" title="Transfer file" aria-label="Transfer file {{ $file->digital_id }}">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                                            </a>
                                                        @endunless
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
                    <p>Showing <b>{{ number_format($fileManagementSystems->firstItem()) }}–{{ number_format($fileManagementSystems->lastItem()) }}</b> of <b>{{ number_format($fileManagementSystems->total()) }}</b> files</p>
                    <div class="ak-pager-right">
                        <form method="GET" action="{{ route('file-management-systems.index') }}" class="ak-perpage">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $k => $v) <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}"> @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()">
                                @foreach (\App\Http\Controllers\FileManagementSystemController::PER_PAGE as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $fileManagementSystems->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            @else
                <div class="ak-empty">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                    @if ($filters)
                        <h2>No files match these filters</h2>
                        <p>Remove a filter or search by digital ID.</p>
                        <a href="{{ route('file-management-systems.index') }}" class="ak-btn ak-btn-primary">Clear all filters</a>
                    @else
                        <h2>No files yet</h2>
                        <p>Record the first document and upload its scanned pages.</p>
                        @can('create file management systems') <a href="{{ route('file-management-systems.create') }}" class="ak-btn ak-btn-primary">＋ New file</a> @endcan
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
