{{--
    Settings > User module > Roles (/settings/user-module/roles). Same list pattern as the Users list:
    KPI cards, one search box with removable chips, a sortable dense table with comfortable / compact
    density, view / edit / delete actions by permission and rows per page. A role page shows what the
    role grants by module and who has it.
--}}
@php
    $filters = array_filter((array) request('filter', []), fn ($v) => $v !== null && $v !== '');
    $sort = $sort ?? 'name';
    $chipLabels = ['search' => 'Search', 'name' => 'Name', 'created_at' => 'Created', 'users' => 'Users'];
    $chipValue = fn (string $key, $value) => $key === 'users' && $value === 'none' ? 'Not used by anyone' : $value;
    $withFilters = fn (array $f) => request()->fullUrlWithQuery(['filter' => $f ?: null, 'page' => null]);
    $sortUrl = fn (string $field) => request()->fullUrlWithQuery(['sort' => $sort === $field ? '-'.$field : $field, 'page' => null]);
    $ariaSort = fn (string $field) => $sort === $field ? 'ascending' : ($sort === '-'.$field ? 'descending' : 'none');
    $sortMark = fn (string $field) => $sort === $field ? "\u{25B2}" : ($sort === '-'.$field ? "\u{25BC}" : '');
    $advancedCount = collect(['created_at', 'users'])->filter(fn ($k) => isset($filters[$k]))->count();
    $sortNames = ['name' => 'Name', 'created_at' => 'Date added', 'users_count' => 'Users'];
    $sortField = ltrim($sort, '-');
    // Where each built-in role sits in the bank's organogram.
    $roleLevel = [
        'super-admin' => ['System', 'Full access (IT administrators only)'],
        'president-office' => ['President Office', 'Bank-wide view, not tied to an office'],
        'head-office' => ['Head Office', 'Bank-wide access for head-office staff'],
        'division' => ['Division', 'Scoped to one head-office division'],
        'region' => ['Region', 'Scoped to one regional office and its branches'],
        'branch' => ['Branch', 'Scoped to one branch'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('user.module') }}">User module</a><span aria-hidden="true">›</span><span>Roles</span>
                </nav>
                <h1 class="ak-title">Roles</h1>
                <p class="ak-sub">A role is a named bundle of permissions. Give users roles, and extra permissions only when needed.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('user.module') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back</a>
                @can('view permissions')
                    <a href="{{ route('permissions.index') }}" class="ak-btn ak-btn-outline">Permissions</a>
                @endcan
                <button type="button" class="ak-btn ak-btn-outline" onclick="window.print()">Print</button>
                @can('create roles')
                    <a href="{{ route('roles.create') }}" class="ak-btn ak-btn-primary"><span aria-hidden="true">＋</span> Add role</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>@page { size: A4 landscape; margin: 10mm; }</style>

    <div class="ak-page" x-data="{ confirm: { open: false, url: '', name: '', users: 0 } }"
        @open-delete-role-modal.window="confirm = { open: true, url: $event.detail.url, name: $event.detail.name, users: $event.detail.users }">
        <div class="ak-print-head">
            <div class="ak-print-bank">The Bank of Azad Jammu &amp; Kashmir</div>
            <div class="ak-print-title">Roles</div>
            <table class="ak-print-meta">
                <tr><th>Filters</th><td>{{ $filters ? collect($filters)->map(fn ($v, $k) => ($chipLabels[$k] ?? \Illuminate\Support\Str::headline($k)).': '.$chipValue($k, $v))->implode(' · ') : 'None (all roles)' }}</td>
                    <th>Printed</th><td>{{ now()->format('d.m.Y H:i') }} by {{ auth()->user()->name }}</td></tr>
            </table>
        </div>

        <x-status-message />
        @if (session('error'))
            <div class="ak-alert ak-alert-error" role="alert">{{ session('error') }}</div>
        @endif

        <section class="ak-kpis" aria-label="Role summary">
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Roles</span><span class="ak-kpi-value">{{ number_format($stats['roles']) }}</span><span class="ak-kpi-hint">built-in and custom</span></span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Permissions</span><span class="ak-kpi-value">{{ number_format($stats['permissions']) }}</span><span class="ak-kpi-hint">available to roles</span></span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.93 17.93 0 0 1 12 21.75c-2.68 0-5.22-.58-7.5-1.63Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Users with a role</span><span class="ak-kpi-value">{{ number_format($stats['users_with_roles']) }}</span><span class="ak-kpi-hint">get access from roles</span></span>
            </div>
            <a href="{{ $withFilters(['users' => 'none']) }}" class="ak-kpi{{ $stats['unused'] ? ' ak-kpi-action' : '' }}{{ ($filters['users'] ?? '') === 'none' ? ' is-active' : '' }}">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Unused roles</span><span class="ak-kpi-value">{{ number_format($stats['unused']) }}</span><span class="ak-kpi-hint">{{ $stats['unused'] ? 'Nobody has them →' : 'Every role is in use' }}</span></span>
            </a>
        </section>

        <section class="ak-card" aria-label="Roles">
            <form method="GET" action="{{ route('roles.index') }}" class="ak-filters" x-data="{ advanced: {{ $advancedCount ? 'true' : 'false' }}, busy: false }" @submit="busy = true">
                @if (request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if (request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
                <div class="ak-filter-row">
                    <div class="ak-field ak-field-search">
                        <label for="f_search">Search</label>
                        <div class="ak-search">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                            <input id="f_search" type="search" name="filter[search]" value="{{ $filters['search'] ?? ($filters['name'] ?? '') }}" autocomplete="off" placeholder="Role name" x-ref="search"
                                @keydown.window.slash="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus(); }">
                            <kbd aria-hidden="true">/</kbd>
                        </div>
                    </div>
                    <div class="ak-filter-buttons">
                        <button type="button" class="ak-btn ak-btn-ghost" @click="advanced = !advanced" :aria-expanded="advanced" aria-controls="role-advanced">
                            More filters @if ($advancedCount)<span class="ak-count ak-count-dark">{{ $advancedCount }}</span>@endif
                        </button>
                        <button type="submit" class="ak-btn ak-btn-primary" :disabled="busy"><span x-show="!busy">Apply</span><span x-show="busy" x-cloak>Searching…</span></button>
                    </div>
                </div>
                <div id="role-advanced" class="ak-filter-advanced" x-show="advanced" x-cloak x-transition>
                    <div class="ak-field">
                        <label for="f_created">Created on</label>
                        <input id="f_created" type="date" name="filter[created_at]" value="{{ $filters['created_at'] ?? '' }}">
                    </div>
                    <div class="ak-field">
                        <label for="f_users">Users</label>
                        <select id="f_users" name="filter[users]">
                            <option value="">Any</option>
                            <option value="none" @selected(($filters['users'] ?? '') === 'none')>Not used by anyone</option>
                        </select>
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
                    <a href="{{ route('roles.index') }}" class="ak-chips-clear">Clear all</a>
                </div>
            @endif

            @if ($roles->count() > 0)
                <div class="ak-table-wrap" x-data="{
                        compact: (() => { try { return localStorage.getItem('roles-density') === 'compact'; } catch (e) { return false; } })(),
                        setDensity(v) { this.compact = v; try { localStorage.setItem('roles-density', v ? 'compact' : 'comfortable'); } catch (e) {} }
                    }">
                    <div class="ak-dt-toolbar">
                        <p><b>{{ number_format($roles->total()) }}</b> {{ \Illuminate\Support\Str::plural('role', $roles->total()) }}
                            &middot; sorted by <b>{{ $sortNames[$sortField] ?? 'Name' }}</b> ({{ str_starts_with($sort, '-') ? 'Z–A / most first' : 'A–Z / fewest first' }})</p>
                        <div class="ak-seg ak-seg-sm" role="group" aria-label="Row density">
                            <button type="button" @click="setDensity(false)" :aria-pressed="!compact" :class="!compact && 'is-on'">Comfortable</button>
                            <button type="button" @click="setDensity(true)" :aria-pressed="compact" :class="compact && 'is-on'">Compact</button>
                        </div>
                    </div>
                    <div class="ak-dt-scroll" :class="compact && 'is-compact'" tabindex="0" aria-label="Roles table">
                        <table class="ak-dt">
                            <caption class="sr-only">Roles, {{ $roles->total() }} results</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="ak-c ak-sticky-1">#</th>
                                    <th scope="col" class="ak-sticky-2" aria-sort="{{ $ariaSort('name') }}"><a href="{{ $sortUrl('name') }}">Role <span aria-hidden="true">{{ $sortMark('name') }}</span></a></th>
                                    <th scope="col">Organogram level</th>
                                    <th scope="col" class="ak-c">Permissions</th>
                                    <th scope="col" class="ak-c" aria-sort="{{ $ariaSort('users_count') }}"><a href="{{ $sortUrl('users_count') }}">Users <span aria-hidden="true">{{ $sortMark('users_count') }}</span></a></th>
                                    <th scope="col" aria-sort="{{ $ariaSort('created_at') }}"><a href="{{ $sortUrl('created_at') }}">Added <span aria-hidden="true">{{ $sortMark('created_at') }}</span></a></th>
                                    <th scope="col" class="ak-c ak-sticky-end print:hidden"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    @php
                                        $level = $roleLevel[$role->name] ?? ['Custom', 'Custom role'];
                                        $moduleCount = collect(\App\Support\PermissionCatalog::build($role->permissions))->count();
                                    @endphp
                                    <tr>
                                        <td class="ak-c ak-sticky-1 ak-muted" data-label="#">{{ $roles->firstItem() + $loop->index }}</td>
                                        <td class="ak-sticky-2" data-label="Role">
                                            @can('view roles')
                                                <a href="{{ route('roles.show', $role) }}" class="ak-primary-link">{{ \Illuminate\Support\Str::headline($role->name) }}</a>
                                            @else
                                                <span class="ak-strong">{{ \Illuminate\Support\Str::headline($role->name) }}</span>
                                            @endcan
                                            <div class="ak-muted ak-hide-compact ak-mono">{{ $role->name }}</div>
                                        </td>
                                        <td data-label="Organogram level">
                                            <div>{{ $level[0] }}</div>
                                            <div class="ak-muted ak-hide-compact">{{ $level[1] }}</div>
                                        </td>
                                        <td class="ak-c" data-label="Permissions">
                                            @if ($role->permissions->isEmpty())
                                                <span class="ak-status ak-status-amber"><i aria-hidden="true"></i>None</span>
                                            @else
                                                <span class="ak-strong">{{ number_format($role->permissions->count()) }}</span>
                                                <div class="ak-muted ak-hide-compact">in {{ $moduleCount }} {{ \Illuminate\Support\Str::plural('module', $moduleCount) }}</div>
                                            @endif
                                        </td>
                                        <td class="ak-c" data-label="Users">
                                            @can('view users')
                                                <a href="{{ route('users.index', ['filter' => ['role' => $role->name]]) }}" class="ak-primary-link">{{ number_format($role->users_count) }}</a>
                                            @else
                                                {{ number_format($role->users_count) }}
                                            @endcan
                                        </td>
                                        <td class="ak-mono" data-label="Added">
                                            {{ $role->created_at?->format('d M Y') ?? '—' }}
                                            <div class="ak-muted ak-hide-compact">{{ $role->created_at?->diffForHumans() }}</div>
                                        </td>
                                        <td class="ak-sticky-end print:hidden" data-label="">
                                            <div class="ak-actions">
                                                @can('view roles')
                                                    <a href="{{ route('roles.show', $role) }}" class="ak-icon ak-icon-view" title="View role" aria-label="View {{ $role->name }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                @else
                                                    <span class="ak-slot"></span>
                                                @endcan
                                                <span class="ak-slot">
                                                    @can('edit roles')
                                                        <a href="{{ route('roles.edit', $role) }}" class="ak-icon" title="Edit role" aria-label="Edit {{ $role->name }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.2-.9 10.9-10.9a1.9 1.9 0 0 0-2.7-2.7L5.5 16.4 4 20Z" /></svg>
                                                        </a>
                                                    @endcan
                                                </span>
                                                <span class="ak-slot">
                                                    @can('delete roles')
                                                        @if ($role->name !== 'super-admin')
                                                            <button type="button" class="ak-icon ak-icon-danger" title="Delete role" aria-label="Delete {{ $role->name }}"
                                                                @click="$dispatch('open-delete-role-modal', { url: @js(route('roles.destroy', $role)), name: @js($role->name), users: {{ (int) $role->users_count }} })">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67A2.25 2.25 0 0 1 15.92 21.75H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0" /></svg>
                                                            </button>
                                                        @endif
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
                    <p>Showing <b>{{ number_format($roles->firstItem()) }}–{{ number_format($roles->lastItem()) }}</b> of <b>{{ number_format($roles->total()) }}</b> roles</p>
                    <div class="ak-pager-right">
                        <form method="GET" action="{{ route('roles.index') }}" class="ak-perpage">
                            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $k => $v) <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}"> @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()">
                                @foreach (\App\Http\Controllers\RoleController::PER_PAGE as $n)
                                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $roles->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            @else
                <div class="ak-empty">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                    @if ($filters)
                        <h2>No roles match these filters</h2>
                        <p>Remove a filter or search with part of a role name.</p>
                        <a href="{{ route('roles.index') }}" class="ak-btn ak-btn-primary">Clear all filters</a>
                    @else
                        <h2>No roles yet</h2>
                        <p>Add the first role with “Add role”.</p>
                        @can('create roles') <a href="{{ route('roles.create') }}" class="ak-btn ak-btn-primary">＋ Add role</a> @endcan
                    @endif
                </div>
            @endif
        </section>

        {{-- Delete confirmation --}}
        <div class="uf-modal" x-show="confirm.open" x-cloak style="display:none" @keydown.escape.window="confirm.open = false" role="dialog" aria-modal="true" aria-labelledby="role-del-title">
            <div class="uf-modal-bg" x-show="confirm.open" x-transition.opacity @click="confirm.open = false"></div>
            <div class="uf-modal-box" x-show="confirm.open" x-transition>
                <form method="POST" :action="confirm.url">
                    @csrf
                    @method('DELETE')
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon ak-pill-red" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg></span>
                        <div>
                            <h3 id="role-del-title" x-text="'Delete role ' + confirm.name + '?'"></h3>
                            <p style="margin:8px 0 0; font-size:14px; color:#334155" x-text="confirm.users > 0 ? confirm.users + ' user(s) still have this role; move them to another role first.' : 'Nobody has this role. This cannot be undone.'"></p>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn ak-btn-outline" @click="confirm.open = false">Cancel</button>
                        <button type="submit" class="ak-btn ak-btn-danger-outline">Delete role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
