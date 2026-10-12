{{--
    Settings > User module > Roles > View (/settings/roles/{role}). What the role grants, by module, and who has it.
    Read only; changes happen on Edit.
--}}
@php
    $total = $role->permissions->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('user.module') }}">User module</a><span aria-hidden="true">›</span>
                    <a href="{{ route('roles.index') }}">Roles</a><span aria-hidden="true">›</span><span>{{ $role->name }}</span>
                </nav>
                <h1 class="ak-title">{{ \Illuminate\Support\Str::headline($role->name) }}</h1>
                <p class="ak-sub"><span class="ak-mono">{{ $role->name }}</span> &middot; guard {{ $role->guard_name }} &middot; added {{ $role->created_at?->format('d M Y') ?? '—' }}</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('roles.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back to Roles</a>
                <button type="button" class="ak-btn ak-btn-outline" onclick="window.print()">Print</button>
                @can('edit roles')
                    <a href="{{ route('roles.edit', $role) }}" class="ak-btn ak-btn-primary">Edit role &amp; permissions</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>
        .rl-grid { display: grid; gap: 20px; grid-template-columns: 1fr; }
        @media (min-width: 1024px) { .rl-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } }
        @media print { .rl-grid { display: block; } .uf-card { break-inside: avoid; box-shadow: none; margin-bottom: 12px; } }
    </style>

    <div class="ak-page">
        <x-status-message />

        <section class="ak-kpis" aria-label="Role summary">
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Permissions</span><span class="ak-kpi-value">{{ number_format($total) }}</span><span class="ak-kpi-hint">of {{ number_format($allCount) }} available</span></span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Modules</span><span class="ak-kpi-value">{{ count($modules) }}</span><span class="ak-kpi-hint">with at least one permission</span></span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.93 17.93 0 0 1 12 21.75c-2.68 0-5.22-.58-7.5-1.63Z" /></svg></span>
                <span class="ak-kpi-body"><span class="ak-kpi-label">Users</span><span class="ak-kpi-value">{{ number_format($usersCount) }}</span><span class="ak-kpi-hint">have this role</span></span>
            </div>
        </section>

        <div class="rl-grid">
            <section class="uf-card" aria-label="Permissions of this role">
                <header class="uf-card-head">
                    <h2 class="uf-card-title">What this role grants</h2>
                    <span class="ak-sub" style="margin:0">Grouped by module.</span>
                </header>
                <div style="padding: 16px 20px" x-data="{ module: 'all' }">
                    @if ($role->name === 'super-admin')
                        <p class="ak-sub" style="margin:0 0 12px">Super admins can open everything regardless of the list below.</p>
                    @endif
                    @if (empty($modules))
                        <p class="ak-sub" style="margin:0">This role has no permissions yet.</p>
                    @else
                        <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Modules" style="margin-bottom:14px">
                            <button type="button" role="tab" data-module-tab="all" @click="module = 'all'" :class="module === 'all' ? 'ak-btn ak-btn-primary ak-btn-sm' : 'ak-btn ak-btn-outline ak-btn-sm'">All modules ({{ $total }})</button>
                            @foreach ($modules as $module)
                                <button type="button" role="tab" data-module-tab="{{ $module['key'] }}" @click="module = '{{ $module['key'] }}'" :class="module === '{{ $module['key'] }}' ? 'ak-btn ak-btn-primary ak-btn-sm' : 'ak-btn ak-btn-outline ak-btn-sm'">{{ $module['label'] }} ({{ $module['count'] }})</button>
                            @endforeach
                        </div>
                        @foreach ($modules as $module)
                            <div data-module="{{ $module['key'] }}" x-show="module === 'all' || module === '{{ $module['key'] }}'" style="margin-bottom:16px">
                                <h3 style="margin:0 0 6px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--ak-navy)">{{ $module['label'] }}</h3>
                                @foreach ($module['groups'] as $group)
                                    <div style="margin-bottom:10px">
                                        <div style="font-size:13px; font-weight:700">{{ $group['label'] }} <span class="ak-muted">({{ $group['permissions']->count() }})</span></div>
                                        <ul style="margin:4px 0 0; padding:0; list-style:none; display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:4px 16px; font-size:13px">
                                            @foreach ($group['permissions'] as $permission)
                                                <li>{{ $permission->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="uf-card" aria-label="Users with this role">
                <header class="uf-card-head">
                    <h2 class="uf-card-title">Users with this role</h2>
                    @can('view users')
                        <a href="{{ route('users.index', ['filter' => ['role' => $role->name]]) }}" class="ak-primary-link" style="font-size:13px">See all {{ number_format($usersCount) }}</a>
                    @endcan
                </header>
                <ul class="ak-actions" style="display:block; padding: 6px 20px 12px; list-style:none; margin:0">
                    @forelse ($users as $member)
                        <li style="display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:14px">
                            <span>
                                @can('view users')
                                    <a href="{{ route('users.show', $member) }}" class="ak-primary-link">{{ $member->name }}</a>
                                @else
                                    {{ $member->name }}
                                @endcan
                                <span class="ak-muted" style="display:block; font-size:12px">{{ $member->email }}</span>
                            </span>
                            @if ($member->is_active !== 'Yes') <span class="ak-status ak-status-red"><i aria-hidden="true"></i>Suspended</span> @endif
                        </li>
                    @empty
                        <li class="ak-muted" style="padding:8px 0">Nobody has this role.</li>
                    @endforelse
                    @if ($usersCount > $users->count())
                        <li class="ak-muted" style="padding:8px 0; font-size:12px">… and {{ $usersCount - $users->count() }} more.</li>
                    @endif
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>
