{{--
    Settings > User module > Users > View (/settings/users/{user}).
    Profile, access summary, what the user can actually open (arranged by module, with where each
    permission comes from) and recent activity. Read only; changes happen on Edit.
--}}
@php
    $isSuper = $user->is_super_admin === 'Yes' || $user->roles->contains('name', 'super-admin');
    $initials = collect(explode(' ', trim((string) $user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $fromRoles = collect($grants)->filter(fn ($g) => ! empty($g['roles']))->count();
    $extra = collect($grants)->filter(fn ($g) => ! empty($g['direct']))->count();
    $onlyExtra = collect($grants)->filter(fn ($g) => ! empty($g['direct']) && empty($g['roles']))->count();
    $total = collect($grants)->count();

    $office = match (true) {
        (bool) $user->branch => ['Branch', trim(($user->branch->code ? $user->branch->code.' - ' : '').$user->branch->name)],
        (bool) $user->region => ['Region', $user->region->name],
        (bool) $user->division => ['Division', $user->division->name],
        (bool) $user->headOffice => ['Head office', $user->headOffice->name],
        (bool) $user->is_president_office => ['President office', 'All offices'],
        default => [null, null],
    };

    // Keep only modules where the user has something.
    $grantedModules = collect($modules)->map(function (array $module) use ($grants) {
        $groups = collect($module['groups'])->map(function (array $group) use ($grants) {
            $permissions = $group['permissions']->filter(fn ($permission) => isset($grants[(string) $permission->id]))->values();

            return $permissions->isEmpty() ? null : ['label' => $group['label'], 'count' => $group['permissions']->count(), 'permissions' => $permissions];
        })->filter()->values();

        return [
            'key' => $module['key'], 'label' => $module['label'], 'groups' => $groups,
            'given' => $groups->sum(fn ($group) => $group['permissions']->count()), 'total' => $module['count'],
        ];
    })->filter(fn ($module) => $module['given'] > 0)->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('user.module') }}">User module</a><span aria-hidden="true">›</span>
                    <a href="{{ route('users.index') }}">Users</a><span aria-hidden="true">›</span>
                    <span>{{ $user->name }}</span>
                </nav>
                <div class="ak-person" style="align-items:center; margin-top:6px">
                    <span class="ak-avatar" style="width:48px; height:48px; font-size:16px" aria-hidden="true">{{ $initials ?: '?' }}</span>
                    <div>
                        <h1 class="ak-title" style="margin:0">{{ $user->name }}</h1>
                        <p class="ak-sub" style="margin-top:2px">
                            {{ $user->email }} &middot;
                            @if ($user->is_active === 'Yes')
                                <span class="ak-status ak-status-green"><i aria-hidden="true"></i>Active</span>
                            @else
                                <span class="ak-status ak-status-red"><i aria-hidden="true"></i>Suspended</span>
                            @endif
                            @if ($isSuper) <span class="ak-pill ak-pill-red" style="margin:0">Super admin</span> @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('users.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back to Users</a>
                <button type="button" class="ak-btn ak-btn-outline" onclick="window.print()">Print</button>
                @can('edit users')
                    <a href="{{ route('users.edit', $user) }}" class="ak-btn ak-btn-primary">Edit user &amp; access</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>
        .us-grid { display: grid; gap: 20px; grid-template-columns: 1fr; }
        @media (min-width: 1024px) { .us-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } }
        .us-list { margin: 0; display: flex; flex-direction: column; font-size: 14px; }
        .us-list > div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .us-list > div:last-child { border-bottom: 0; }
        .us-list dt { color: var(--ak-muted); }
        .us-list dd { margin: 0; font-weight: 600; color: var(--ak-text); text-align: right; word-break: break-word; }
        .us-src { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
        .us-feed { margin: 0; padding: 0; list-style: none; }
        .us-feed li { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .us-feed li:last-child { border-bottom: 0; }
        .us-feed i { flex: none; width: 8px; height: 8px; margin-top: 6px; border-radius: 50%; background: var(--ak-navy); }
        .us-feed small { display: block; color: var(--ak-muted); font-size: 12px; }
        @media print { .us-grid { display: block; } .uf-card { break-inside: avoid; box-shadow: none; margin-bottom: 12px; } }
    </style>

    <div class="ak-page">
        <x-status-message />

        <section class="ak-kpis" aria-label="Access summary">
            <div class="ak-kpi">
                <span class="ak-kpi-icon {{ $user->is_active === 'Yes' ? 'ak-tone-green' : 'ak-tone-amber' }}" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.93 17.93 0 0 1 12 21.75c-2.68 0-5.22-.58-7.5-1.63Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Sign in</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ $user->is_active === 'Yes' ? 'Allowed' : 'Blocked (suspended)' }}</span>
                    <span class="ak-kpi-hint">since {{ $user->created_at?->format('d M Y') ?? '—' }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.1 9.1 0 0 0 3.74-.48 3 3 0 0 0-4.68-2.72m.94 3.2v.03c0 .34-.02.67-.06 1A11.94 11.94 0 0 1 12 21c-2.17 0-4.2-.58-5.95-1.58a6.06 6.06 0 0 1-.06-1.02m12.01 0a5.97 5.97 0 0 0-.94-3.2m0 0A5.99 5.99 0 0 0 12 12.75a6 6 0 0 0-5.06 2.77M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Roles</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ $user->roles->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::headline($n))->implode(', ') ?: 'None' }}</span>
                    <span class="ak-kpi-hint">{{ $fromRoles }} permissions from roles</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Total access</span>
                    <span class="ak-kpi-value">{{ $isSuper ? 'Everything' : number_format($total) }}</span>
                    <span class="ak-kpi-hint">{{ $isSuper ? 'super admins skip every check' : 'permissions in all' }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Extra permissions</span>
                    <span class="ak-kpi-value">{{ number_format($extra) }}</span>
                    <span class="ak-kpi-hint">{{ $onlyExtra }} beyond the roles</span>
                </span>
            </div>
        </section>

        <div class="us-grid">
            <section class="uf-card" aria-label="What this user can open">
                <header class="uf-card-head">
                    <h2 class="uf-card-title">What this user can open</h2>
                    <span class="ak-sub" style="margin:0">Grouped by module; the tag shows where each permission comes from.</span>
                </header>
                <div style="padding: 16px 20px" x-data="{ module: 'all' }">
                    @if ($isSuper)
                        <p class="ak-sub" style="margin:0 0 12px">Super admins can open everything regardless of the lists below.</p>
                    @endif
                    @if ($grantedModules->isEmpty())
                        <p class="ak-sub" style="margin:0">No role or permission is assigned yet, so this user cannot open anything.</p>
                    @else
                        <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Modules" style="margin-bottom:14px">
                            <button type="button" role="tab" data-module-tab="all" @click="module = 'all'" :class="module === 'all' ? 'ak-btn ak-btn-primary ak-btn-sm' : 'ak-btn ak-btn-outline ak-btn-sm'">All modules ({{ $grantedModules->sum('given') }})</button>
                            @foreach ($grantedModules as $module)
                                <button type="button" role="tab" data-module-tab="{{ $module['key'] }}" @click="module = '{{ $module['key'] }}'" :class="module === '{{ $module['key'] }}' ? 'ak-btn ak-btn-primary ak-btn-sm' : 'ak-btn ak-btn-outline ak-btn-sm'">{{ $module['label'] }} ({{ $module['given'] }}/{{ $module['total'] }})</button>
                            @endforeach
                        </div>
                        @foreach ($grantedModules as $module)
                            <div data-module="{{ $module['key'] }}" x-show="module === 'all' || module === '{{ $module['key'] }}'" style="margin-bottom:16px">
                                <h3 style="margin:0 0 6px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--ak-navy)">{{ $module['label'] }}</h3>
                                @foreach ($module['groups'] as $group)
                                    <div style="margin-bottom:10px">
                                        <div style="font-size:13px; font-weight:700">{{ $group['label'] }} <span class="ak-muted">({{ $group['permissions']->count() }}/{{ $group['count'] }})</span></div>
                                        <ul style="margin:4px 0 0; padding:0; list-style:none; display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:4px 16px; font-size:13px">
                                            @foreach ($group['permissions'] as $permission)
                                                @php($grant = $grants[(string) $permission->id])
                                                <li class="flex items-center justify-between gap-2">
                                                    <span>{{ $permission->name }}</span>
                                                    <span class="us-src" style="color: {{ ! empty($grant['direct']) ? '#b45309' : '#1e3a8a' }}">
                                                        {{ ! empty($grant['direct']) ? 'extra' : '' }}{{ ! empty($grant['direct']) && ! empty($grant['roles']) ? ' + ' : '' }}{{ ! empty($grant['roles']) ? implode(', ', array_unique($grant['roles'])) : '' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <div style="display:flex; flex-direction:column; gap:20px">
                <section class="uf-card" aria-label="Profile">
                    <header class="uf-card-head"><h2 class="uf-card-title">Profile</h2></header>
                    <dl class="us-list" style="padding: 6px 20px 12px">
                        <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                        <div><dt>{{ $office[0] ?? 'Office' }}</dt><dd>{{ $office[1] ?? '—' }}</dd></div>
                        <div><dt>Status</dt><dd>{{ $user->is_active === 'Yes' ? 'Active' : 'Suspended' }}</dd></div>
                        <div><dt>Super admin</dt><dd>{{ $isSuper ? 'Yes' : 'No' }}</dd></div>
                        <div><dt>Added</dt><dd>{{ $user->created_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    </dl>
                </section>

                <section class="uf-card" aria-label="Recent activity">
                    <header class="uf-card-head"><h2 class="uf-card-title">Recent activity</h2></header>
                    <ul class="us-feed" style="padding: 6px 20px 12px">
                        @forelse ($activity as $entry)
                            <li><i aria-hidden="true"></i><div>{{ $entry->description }}<small>{{ $entry->created_at?->format('d M Y H:i') }}{{ $entry->causer ? ' · '.$entry->causer->name : '' }}</small></div></li>
                        @empty
                            <li class="ak-muted">No activity recorded yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
