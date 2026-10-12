{{--
    Settings > User module > Permissions (/settings/user-module/permissions). Same list pattern as Users and Roles:
    KPI cards, one search box with module / role filters and removable chips, a sortable dense table with
    comfortable / compact density, edit / delete actions by permission with an in-page confirmation,
    rows per page and a print layout.
--}}
@php
    $filters = array_filter((array) request('filter', []), fn ($v) => $v !== null && $v !== '');
    $sort = $sort ?? 'name';
    $moduleLabels = $modules->pluck('label', 'key');
    $roleNames = $roles->pluck('name', 'id');
    $chipLabels = ['search' => 'Search', 'name' => 'Name', 'module' => 'Module', 'role_id' => 'Role', 'created_at' => 'Created', 'usage' => 'Usage'];
    $chipValue = fn (string $key, $value) => match ($key) {
        'module' => $moduleLabels[$value] ?? $value,
        'role_id' => $roleNames[$value] ?? $value,
        'usage' => $value === 'orphan' ? 'Not in any role or user' : $value,
        default => $value,
    };
    $withFilters = fn (array $f) => request()->fullUrlWithQuery(['filter' => $f ?: null, 'page' => null]);
    $sortUrl = fn (string $field) => request()->fullUrlWithQuery(['sort' => $sort === $field ? '-'.$field : $field, 'page' => null]);
    $ariaSort = fn (string $field) => $sort === $field ? 'ascending' : ($sort === '-'.$field ? 'descending' : 'none');
    $sortMark = fn (string $field) => $sort === $field ? "\u{25B2}" : ($sort === '-'.$field ? "\u{25BC}" : '');
    $advancedCount = collect(['role_id', 'created_at', 'usage'])->filter(fn ($k) => isset($filters[$k]))->count();
    $sortNames = ['name' => 'Name', 'created_at' => 'Date added', 'users_count' => 'Direct users'];
    $sortField = ltrim($sort, '-');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('user.module') }}">User module</a><span aria-hidden="true">›</span><span>Permissions</span>
                </nav>
                <h1 class="ak-title">Permissions</h1>
                <p class="ak-sub">The individual things a user can do. They are given through roles, or directly to a user.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('user.module') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back</a>
                @can('view roles')
                    <a href="{{ route('roles.index') }}" class="ak-btn ak-btn-outline">Roles</a>
                @endcan
                <button type="button" class="ak-btn ak-btn-outline" onclick="window.print()">Print</button>
                @can('create permissions')
                    <a href="{{ route('permissions.create') }}" class="ak-btn ak-btn-primary"><span aria-hidden="true">＋</span> Add permission</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>@page { size: A4 landscape; margin: 10mm; }</style>

    <div class="ak-page" x-data="{ confirm: { open: false, url: '', name: '', roles: 0, users: 0 } }"
        @open-delete-permission-modal.window="confirm = { open: true, url: $event.detail.url, name: $event.detail.name, roles: $event.detail.roles, users: $event.detail.users || 0 }">
        <div class="ak-print-head">
            <div class="ak-print-bank">The Bank of Azad Jammu &amp; Kashmir</div>
            <div class="ak-print-title">Permissions</div>
            <table class="ak-print-meta">
                <tr><th>Filters</th><td>{{ $filters ? collect($filters)->map(fn ($v, $k) => ($chipLabels[$k] ?? \Illuminate\Support\Str::headline($k)).': '.$chipValue($k, $v))->implode(' · ') : 'None (all permissions)' }}</td>
                    <th>Printed</th><td>{{ now()->format('d.m.Y H:i') }} by {{ auth()->user()->name }}</td></tr>
            </table>
        </div>

        <x-status-message />
        @if (session('error'))
            <div class="ak-alert ak-alert-error" role="alert">{{ session('error') }}</div>
        @endif

        <section class="ak-kpis" aria-label="Permission summary">
            <a href="{{ $withFilters([]) }}" class="ak-kpi{{ ! $filters ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Permissions</span><span class="ak-kpi-value">{{ number_format($stats['permissions']) }}</span><span class="ak-kpi-hint">all of them</span></span>
            </a>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Modules</span><span class="ak-kpi-value">{{ number_format($stats['modules']) }}</span><span class="ak-kpi-hint">product areas</span></span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Given by a role</span><span class="ak-kpi-value">{{ number_format($stats['in_roles']) }}</span><span class="ak-kpi-hint">in at least one role</span></span>
            </div>
            <a href="{{ $withFilters(['usage' => 'orphan']) }}" class="ak-kpi{{ $stats['orphans'] ? ' ak-kpi-action' : '' }}{{ ($filters['usage'] ?? '') === 'orphan' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Not given to anyone</span><span class="ak-kpi-value">{{ number_format($stats['orphans']) }}</span><span class="ak-kpi-hint">{{ $stats['orphans'] ? 'In no role and no user →' : 'Every permission is in use' }}</span></span>
            </a>
        </section>

        <section class="ak-card" aria-label="Permissions">
            <form method="GET" action="{{ route('permissions.index') }}" class="ak-filters" x-data="{ advanced: {{ $advancedCount ? 'true' : 'false' }}, busy: false }" @submit="busy = true">
                @if (request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if (request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
                <div class="ak-filter-row">
                    <div class="ak-field ak-field-search">
                        <label for="f_search">Search</label>
                        <div class="ak-search">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                            <input id="f_search" type="search" name="filter[search]" value="{{ $filters['search'] ?? ($filters['name'] ?? '') }}" autocomplete="off" placeholder="e.g. view aksic" x-ref="search"
                                @keydown.window.slash="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus(); }">
                            <kbd aria-hidden="true">/</kbd>
                        </div>
                    </div>
                    <div class="ak-field">
                        <label for="f_module">Module</label>
                        <select id="f_module" name="filter[module]">
                            <option value="">All modules</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module['key'] }}" @selected(($filters['module'] ?? '') === $module['key'])>{{ $module['label'] }} ({{ $module['count'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-filter-buttons">
                        <button type="button" class="ak-btn ak-btn-ghost" @click="advanced = !advanced" :aria-expanded="advanced" aria-controls="perm-advanced">
                            More filters @if ($advancedCount)<span class="ak-count ak-count-dark">{{ $advancedCount }}</span>@endif
                        </button>
                        <button type="submit" class="ak-btn ak-btn-primary" :disabled="busy"><span x-show="!busy">Apply</span><span x-show="busy" x-cloak>Searching…</span></button>
                    </div>
                </div>
                <div id="perm-advanced" class="ak-filter-advanced" x-show="advanced" x-cloak x-transition>
                    <div class="ak-field">
                        <label for="f_role">Used by role</label>
                        <select id="f_role" name="filter[role_id]">
                            <option value="">Any role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) ($filters['role_id'] ?? '') === (string) $role->id)>{{ \Illuminate\Support\Str::headline($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ak-field">
                        <label for="f_usage">Usage</label>
                        <select id="f_usage" name="filter[usage]">
                            <option value="">Any</option>
                            <option value="orphan" @selected(($filters['usage'] ?? '') === 'orphan')>Not in any role or user</option>
                        </select>
                    </div>
                    <div class="ak-field">
                        <label for="f_created">Created on</label>
                        <input id="f_created" type="date" name="filter[created_at]" value="{{ $filters['created_at'] ?? '' }}">
                    </div>
                </div>
            </form>

            @if ($filters)
                <div class="ak-chips" aria-label="Active filters">
                    <span class="ak-chips-label">Filtered by</span>
                    @foreach ($filters as $key => $value)
                        <a href="{{ $withFilters(\Illuminate\Support\Arr::except($filters, $key)) }}" class="ak-chip" aria-label="Remove filter {{ $chipLabels[$key] ?? $key }}">
                            <b>{{ $chipLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</b> {{ $chipValue($key, $value) }} <span aria-hidden="true">×</span>
                        </a>
                    @endforeach
                    <a href="{{ route('permissions.index') }}" class="ak-chips-clear">Clear all</a>
                </div>
            @endif

            @if ($permissions->count() > 0)
                <div class="ak-table-wrap" x-data="{
                        compact: (() => { try { return localStorage.getItem('permissions-density') === 'compact'; } catch (e) { return false; } })(),
                        setDensity(v) { this.compact = v; try { localStorage.setItem('permissions-density', v ? 'compact' : 'comfortable'); } catch (e) {} }
                    }">
                    <div class="ak-dt-toolbar">
                        <p><b>{{ number_format($permissions->total()) }}</b> {{ \Illuminate\Support\Str::plural('permission', $permissions->total()) }}
                            &middot; sorted by <b>{{ $sortNames[$sortField] ?? 'Name' }}</b> ({{ str_starts_with($sort, '-') ? 'Z–A / most first' : 'A–Z / fewest first' }})</p>
                        <div class="ak-seg ak-seg-sm" role="group" aria-label="Row density">
                            <button type="button" @click="setDensity(false)" :aria-pressed="!compact" :class="!compact && 'is-on'">Comfortable</button>
                            <button type="button" @click="setDensity(true)" :aria-pressed="compact" :class="compact && 'is-on'">Compact</button>
                        </div>
                    </div>
                    <div class="ak-dt-scroll" :class="compact && 'is-compact'" tabindex="0" aria-label="Permissions table">
                        <table class="ak-dt">
                            <caption class="sr-only">Permissions, {{ $permissions->total() }} results</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="ak-c ak-sticky-1">#</th>
                                    <th scope="col" class="ak-sticky-2" aria-sort="{{ $ariaSort('name') }}"><a href="{{ $sortUrl('name') }}">Permission <span aria-hidden="true">{{ $sortMark('name') }}</span></a></th>
                                    <th scope="col">Module</th>
                                    <th scope="col">Given by roles</th>
                                    <th scope="col" class="ak-c" aria-sort="{{ $ariaSort('users_count') }}"><a href="{{ $sortUrl('users_count') }}">Direct users <span aria-hidden="true">{{ $sortMark('users_count') }}</span></a></th>
                                    <th scope="col" aria-sort="{{ $ariaSort('created_at') }}"><a href="{{ $sortUrl('created_at') }}">Added <span aria-hidden="true">{{ $sortMark('created_at') }}</span></a></th>
                                    <th scope="col" class="ak-c ak-sticky-end print:hidden"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    @php
                                        $moduleKey = \App\Support\PermissionCatalog::moduleKey($permission->name);
                                        $moduleLabel = $moduleLabels[$moduleKey] ?? \Illuminate\Support\Str::headline($moduleKey);
                                    @endphp
                                    <tr>
                                        <td class="ak-c ak-sticky-1 ak-muted" data-label="#">{{ $permissions->firstItem() + $loop->index }}</td>
                                        <td class="ak-sticky-2" data-label="Permission">
                                            <span class="ak-strong">{{ $permission->name }}</span>
                                            <div class="ak-muted ak-hide-compact">{{ \App\Support\Ui::permissionModule($permission->name) }}</div>
                                        </td>
                                        <td data-label="Module">{{ $moduleLabel }}</td>
                                        <td data-label="Given by roles">
                                            @forelse ($permission->roles as $role)
                                                <span class="ak-pill">{{ \Illuminate\Support\Str::headline($role->name) }}</span>
                                            @empty
                                                <span class="ak-muted">Not in any role</span>
                                            @endforelse
                                        </td>
                                        <td class="ak-c" data-label="Direct users">{{ $permission->users_count ?: '—' }}</td>
                                        <td class="ak-mono" data-label="Added" style="white-space:nowrap">
                                            {{ $permission->created_at?->format('d M Y') ?? '—' }}
                                            <div class="ak-muted ak-hide-compact">{{ $permission->created_at?->diffForHumans() }}</div>
                                        </td>
                                        <td class="ak-sticky-end print:hidden" data-label="">
                                            <div class="ak-actions">
                                                <span class="ak-slot"></span>
                                                <span class="ak-slot">
                                                    @can('edit permissions')
                                                        <a href="{{ route('permissions.edit', $permission) }}" class="ak-icon" title="Edit permission" aria-label="Edit {{ $permission->name }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg>
                                                        </a>
                                                    @endcan
                                                </span>
                                                <span class="ak-slot">
                                                    @can('delete permissions')
                                                        <button type="button" class="ak-icon ak-icon-danger" title="Delete permission" aria-label="Delete {{ $permission->name }}"
                                                            @click="$dispatch('open-delete-permission-modal', { url: @js(route('permissions.destroy', $permission)), name: @js($permission->name), roles: {{ $permission->roles->count() }}, users: {{ (int) $permission->users_count }} })">
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
                    <p>Showing <b>{{ number_format($permissions->firstItem()) }}–{{ number_format($permissions->lastItem()) }}</b> of <b>{{ number_format($permissions->total()) }}</b> permissions</p>
                    <div class="ak-pager-right">
                        <form method="GET" action="{{ route('permissions.index') }}" class="ak-perpage">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $k => $v) <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}"> @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()">
                                @foreach (\App\Http\Controllers\PermissionController::PER_PAGE as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $permissions->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            @else
                <div class="ak-empty">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                    @if ($filters)
                        <h2>No permissions match these filters</h2>
                        <p>Remove a filter or search with part of a permission name.</p>
                        <a href="{{ route('permissions.index') }}" class="ak-btn ak-btn-primary">Clear all filters</a>
                    @else
                        <h2>No permissions yet</h2>
                        <p>Add the first one with “Add permission”.</p>
                        @can('create permissions') <a href="{{ route('permissions.create') }}" class="ak-btn ak-btn-primary">＋ Add permission</a> @endcan
                    @endif
                </div>
            @endif
        </section>

        {{-- Delete confirmation --}}
        <div class="uf-modal" x-show="confirm.open" x-cloak style="display:none" @keydown.escape.window="confirm.open = false" role="dialog" aria-modal="true" aria-labelledby="perm-del-title">
            <div class="uf-modal-bg" x-show="confirm.open" x-transition.opacity @click="confirm.open = false"></div>
            <div class="uf-modal-box" x-show="confirm.open" x-transition>
                <form method="POST" :action="confirm.url">
                    @csrf
                    @method('DELETE')
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon ak-pill-red" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg></span>
                        <div>
                            <h3 id="perm-del-title" x-text="'Delete ' + confirm.name + '?'"></h3>
                            <p style="margin:8px 0 0; font-size:14px; color:#334155" x-text="'It will be removed from ' + confirm.roles + ' role(s) and ' + confirm.users + ' user(s). Any screen that checks this permission will stop showing for them.'"></p>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn ak-btn-outline" @click="confirm.open = false">Cancel</button>
                        <button type="submit" class="ak-btn ak-btn-danger-outline">Delete permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
