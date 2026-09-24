@php
    $user = auth()->user();
    $n = fn ($v) => number_format((float) $v);
    // Short money for KPI tiles: 1.25 Cr / 45.2 Lac / 12,500 (full figure is in the title attribute).
    $rs = function ($v): string {
        $v = (float) $v;

        return match (true) {
            $v >= 10000000 => number_format($v / 10000000, 2).' Cr',
            $v >= 100000 => number_format($v / 100000, 1).' Lac',
            default => number_format($v),
        };
    };
    $breakdownTitle = [
        'district' => 'Cases by district',
        'branch' => 'Cases by branch',
        'category' => 'Cases by business category',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <h1 class="ak-title">Dashboard</h1>
                <p class="ak-sub">{{ $greeting }}, {{ $user->name }} &middot; {{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="ak-head-actions db-scopes" aria-label="What you are viewing">
                @if ($aksic)
                    <span class="ak-scope" title="AKSIC cases you can see">AKSIC: {{ $aksic['office']['label'] }}</span>
                @endif
                @if ($files)
                    <span class="ak-scope" title="Files you can see">Files: {{ $files['unit_label'] }}</span>
                @endif
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')

    <style>
        .db-page { --s1: #2a78d6; --s2: #eb6834; --s3: #1baf7a; --s4: #eda100; }
        .db-scopes { flex-wrap: wrap; gap: 6px; }
        .db-section-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .db-section-head h2 { font-size: 18px; font-weight: 700; color: var(--ak-text); }
        .db-section-head p { font-size: 13px; color: var(--ak-muted); margin-top: 2px; }
        .db-links { display: flex; flex-wrap: wrap; gap: 8px; }
        .db-grid { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1024px) { .db-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } }
        .db-card { background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: var(--ak-shadow); padding: 16px 18px; min-width: 0; }
        .db-card h3 { font-size: 14px; font-weight: 700; color: var(--ak-text); }
        .db-card .db-card-sub { font-size: 12px; color: var(--ak-muted); margin-top: 2px; }
        .db-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
        .db-card-head a { font-size: 12px; font-weight: 600; color: var(--ak-navy); white-space: nowrap; }
        .db-card-head a:hover { text-decoration: underline; }
        .db-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 180px; text-align: center; color: var(--ak-muted); font-size: 13px; }
        .db-empty b { color: var(--ak-text); font-size: 14px; }
        .db-list { list-style: none; margin: 0; padding: 0; }
        .db-list li + li { border-top: 1px solid var(--ak-line); }
        .db-list a { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 2px; text-decoration: none; color: var(--ak-text); }
        .db-list a:hover { background: var(--ak-soft); }
        .db-list .db-l1 { font-size: 13px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .db-list .db-l2 { font-size: 12px; color: var(--ak-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .db-list .db-r { text-align: right; flex: none; font-size: 12px; color: var(--ak-muted); }
        .db-list .db-r b { display: block; font-size: 13px; color: var(--ak-text); font-variant-numeric: tabular-nums; }
        .db-meter { height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin: 10px 0 6px; }
        .db-meter > span { display: block; height: 100%; border-radius: 999px; background: var(--s1); }
        .db-meter > span.warn { background: #b45309; }
        .db-meter > span.over { background: #b91c1c; }
        .db-figs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-top: 12px; }
        .db-figs div { background: var(--ak-soft); border: 1px solid var(--ak-line); border-radius: 8px; padding: 8px 10px; }
        .db-figs span { display: block; font-size: 11px; color: var(--ak-muted); }
        .db-figs b { font-size: 13px; color: var(--ak-text); font-variant-numeric: tabular-nums; }
        .db-badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .db-badge.archived { background: #e2e8f0; color: #334155; }
        .db-badge.active { background: #dbeafe; color: #1e3a8a; }
        .db-stack > * + * { margin-top: 16px; }
        .db-chart { min-height: 280px; }
        .db-chart-sm { min-height: 240px; }
    </style>

    <div class="db-page mx-auto max-w-7xl space-y-10 px-4 py-6 sm:px-6 lg:px-8">
        @if (! $aksic && ! $files)
            <div class="db-card">
                <div class="db-empty">
                    <b>Nothing to show yet</b>
                    <span>Your role has no AKSIC or file management access. Ask the administrator to assign the right role.</span>
                </div>
            </div>
        @endif

        {{-- ============================== AKSIC ============================== --}}
        @if ($aksic)
            @php
                $t = $aksic['totals'];
                $pendingUrl = route('aksic.index', ['filter' => ['schedule' => 'pending']]);
            @endphp
            <section aria-labelledby="db-aksic">
                <div class="db-section-head">
                    <div>
                        <h2 id="db-aksic">AKSIC &mdash; PM Youth Loan Scheme</h2>
                        <p>{{ $aksic['office']['label'] }} &middot; applications, approvals and markup</p>
                    </div>
                    <div class="db-links">
                        <a href="{{ route('aksic.index') }}" class="ak-btn ak-btn-outline">Open cases</a>
                        @can('view aksic budget')
                            <a href="{{ route('aksic-budgets.index') }}" class="ak-btn ak-btn-outline">Markup budget</a>
                        @endcan
                        @can('create aksics')
                            <a href="{{ route('aksic.create') }}" class="ak-btn ak-btn-primary">＋ New case</a>
                        @endcan
                    </div>
                </div>

                <div class="ak-kpis">
                    <div class="ak-kpi">
                        <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Applications</span>
                            <span class="ak-kpi-value">{{ $n($t['cases']) }}</span>
                            <span class="ak-kpi-hint">{{ $t['female_share'] }}% women applicants</span>
                        </span>
                    </div>
                    <a href="{{ $pendingUrl }}" class="ak-kpi{{ $t['pending'] ? ' ak-kpi-action' : '' }}">
                        <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Pending approval</span>
                            <span class="ak-kpi-value">{{ $n($t['pending']) }}</span>
                            <span class="ak-kpi-hint">{{ $t['pending'] ? 'Review and approve →' : 'Nothing waiting' }}</span>
                        </span>
                    </a>
                    <div class="ak-kpi" title="Rs {{ number_format($t['principal'], 2) }}">
                        <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Approved loans</span>
                            <span class="ak-kpi-value">{{ $n($t['approved']) }}</span>
                            <span class="ak-kpi-hint">Rs {{ $rs($t['principal']) }} disbursed</span>
                        </span>
                    </div>
                    <div class="ak-kpi" title="Rs {{ number_format($t['markup'], 2) }}">
                        <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.3 4.3a11.95 11.95 0 0 1 5.8-5.8l2.65-1.2m0 0-5.94-2.28m5.94 2.28-2.28 5.94" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Scheduled markup</span>
                            <span class="ak-kpi-value">Rs {{ $rs($t['markup']) }}</span>
                            <span class="ak-kpi-hint">on approved loans</span>
                        </span>
                    </div>
                </div>

                @if ($t['cases'] === 0)
                    <div class="db-card mt-4"><div class="db-empty"><b>No AKSIC cases yet</b><span>Cases booked at {{ $aksic['office']['label'] }} will show here.</span></div></div>
                @else
                    <div class="db-grid mt-4">
                        <div class="db-card">
                            <div class="db-card-head">
                                <div><h3>Applications and approvals</h3><p class="db-card-sub">Cases entered per month, last 12 months</p></div>
                            </div>
                            <div id="db-aksic-monthly" class="db-chart" role="img" aria-label="Monthly applications and approvals"></div>
                        </div>
                        <div class="db-card">
                            <div class="db-card-head">
                                <div><h3>Quota mix</h3><p class="db-card-sub">Applications by quota</p></div>
                            </div>
                            <div id="db-aksic-quota" class="db-chart" role="img" aria-label="Applications by quota"></div>
                        </div>
                    </div>

                    <div class="db-grid mt-4">
                        <div class="db-card">
                            <div class="db-card-head">
                                <div><h3>{{ $breakdownTitle[$aksic['breakdown_by']] }}</h3><p class="db-card-sub">Top {{ count($aksic['breakdown']) }}, approved and pending</p></div>
                                @if ($aksic['breakdown_by'] === 'district')
                                    @can('view reports')<a href="{{ route('reports.aksic-rules-report') }}">Rules report →</a>@endcan
                                @endif
                            </div>
                            <div id="db-aksic-breakdown" class="db-chart" role="img" aria-label="{{ $breakdownTitle[$aksic['breakdown_by']] }}"></div>
                        </div>

                        <div class="db-stack">
                            @if ($aksic['budget'])
                                @php $b = $aksic['budget']; @endphp
                                <div class="db-card">
                                    <div class="db-card-head">
                                        <div><h3>Markup budget</h3><p class="db-card-sub">{{ $b['title'] }}</p></div>
                                        <a href="{{ $b['url'] }}">Open →</a>
                                    </div>
                                    <p class="ak-kpi-value ak-kpi-value-sm">{{ $b['percent'] }}% used</p>
                                    <div class="db-meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $b['percent'] }}" aria-label="Markup budget used">
                                        <span class="{{ $b['percent'] > 100 ? 'over' : ($b['percent'] >= 85 ? 'warn' : '') }}" style="width: {{ min(100, $b['percent']) }}%"></span>
                                    </div>
                                    <div class="db-figs">
                                        <div><span>Allocated</span><b>{{ $rs($b['budget']) }}</b></div>
                                        <div><span>Used</span><b>{{ $rs($b['used']) }}</b></div>
                                        <div><span>Remaining</span><b @if ($b['remaining'] < 0) style="color:#b91c1c" @endif>{{ $rs($b['remaining']) }}</b></div>
                                    </div>
                                </div>
                            @endif

                            <div class="db-card">
                                <div class="db-card-head">
                                    <div><h3>Awaiting approval</h3><p class="db-card-sub">Newest pending cases</p></div>
                                    @if ($t['pending'])<a href="{{ $pendingUrl }}">All {{ $n($t['pending']) }} →</a>@endif
                                </div>
                                @if (empty($aksic['recent_pending']))
                                    <div class="db-empty" style="min-height:120px"><span>No pending cases.</span></div>
                                @else
                                    <ul class="db-list">
                                        @foreach ($aksic['recent_pending'] as $case)
                                            <li>
                                                <a href="{{ $case['url'] }}">
                                                    <span style="min-width:0">
                                                        <span class="db-l1" style="display:block">{{ $case['name'] }}</span>
                                                        <span class="db-l2" style="display:block">{{ $case['application_no'] }} &middot; {{ $case['branch'] }} &middot; {{ $case['quota'] }}</span>
                                                    </span>
                                                    <span class="db-r"><b>{{ $rs($case['amount']) }}</b>{{ $case['date'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- ========================== File management ========================== --}}
        @if ($files)
            @php $f = $files['totals']; @endphp
            <section aria-labelledby="db-files">
                <div class="db-section-head">
                    <div>
                        <h2 id="db-files">File management</h2>
                        <p>{{ $files['unit_label'] }} &middot; digitised records, archive boxes and transfers</p>
                    </div>
                    <div class="db-links">
                        <a href="{{ route('file-management-systems.index') }}" class="ak-btn ak-btn-outline">Open files</a>
                        @can('manage boxes')
                            <a href="{{ route('file-management-systems.boxes') }}" class="ak-btn ak-btn-outline">Boxes</a>
                        @endcan
                        @can('create file management systems')
                            <a href="{{ route('file-management-systems.create') }}" class="ak-btn ak-btn-primary">＋ New file</a>
                        @endcan
                    </div>
                </div>

                <div class="ak-kpis">
                    <div class="ak-kpi">
                        <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Files</span>
                            <span class="ak-kpi-value">{{ $n($f['files']) }}</span>
                            <span class="ak-kpi-hint">{{ $n($f['this_month']) }} added this month</span>
                        </span>
                    </div>
                    <div class="ak-kpi">
                        <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Archived in boxes</span>
                            <span class="ak-kpi-value">{{ $n($f['archived']) }}</span>
                            <span class="ak-kpi-hint">{{ $n($f['active']) }} still in circulation</span>
                        </span>
                    </div>
                    <div class="ak-kpi">
                        <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Scanned pages</span>
                            <span class="ak-kpi-value">{{ $n($f['pages']) }}</span>
                            <span class="ak-kpi-hint">{{ $f['files'] ? number_format($f['pages'] / $f['files'], 1) : 0 }} pages per file</span>
                        </span>
                    </div>
                    <div class="ak-kpi{{ $f['incoming'] ? ' ak-kpi-action' : '' }}">
                        <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg></span>
                        <span class="ak-kpi-body">
                            <span class="ak-kpi-label">Incoming transfers</span>
                            <span class="ak-kpi-value">{{ $n($f['incoming']) }}</span>
                            <span class="ak-kpi-hint">{{ $f['incoming'] ? 'Awaiting your decision' : 'None waiting' }} &middot; {{ $n($f['outgoing']) }} sent</span>
                        </span>
                    </div>
                </div>

                @if ($f['files'] === 0)
                    <div class="db-card mt-4">
                        <div class="db-empty">
                            <b>No files yet</b>
                            <span>Files recorded for {{ $files['unit_label'] }} will show here with monthly and category charts.</span>
                            @can('create file management systems')
                                <a href="{{ route('file-management-systems.create') }}" class="ak-btn ak-btn-primary mt-2">＋ Add the first file</a>
                            @endcan
                        </div>
                    </div>
                @else
                    <div class="db-grid mt-4">
                        <div class="db-card">
                            <div class="db-card-head">
                                <div><h3>Files added</h3><p class="db-card-sub">Per month, last 12 months</p></div>
                            </div>
                            <div id="db-files-monthly" class="db-chart" role="img" aria-label="Files added per month"></div>
                        </div>
                        <div class="db-card">
                            <div class="db-card-head">
                                <div><h3>By category</h3><p class="db-card-sub">Top {{ count($files['categories']) }} categories</p></div>
                            </div>
                            <div id="db-files-category" class="db-chart" role="img" aria-label="Files by category"></div>
                        </div>
                    </div>

                    <div class="db-card mt-4">
                        <div class="db-card-head">
                            <div><h3>Recently added</h3><p class="db-card-sub">Latest files</p></div>
                            <a href="{{ route('file-management-systems.index') }}">All files →</a>
                        </div>
                        <ul class="db-list">
                            @foreach ($files['recent'] as $file)
                                <li>
                                    <a href="{{ $file['url'] }}">
                                        <span style="min-width:0">
                                            <span class="db-l1" style="display:block">{{ $file['title'] }}</span>
                                            <span class="db-l2" style="display:block">{{ $file['digital_id'] }} &middot; {{ $file['category'] }}</span>
                                        </span>
                                        <span class="db-r">
                                            <span class="db-badge {{ $file['archived'] ? 'archived' : 'active' }}">{{ $file['archived'] ? 'Archived' : 'In circulation' }}</span>
                                            <span style="display:block;margin-top:2px">{{ $file['date'] }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endif
    </div>

    @php
        $chartData = [
            'aksic' => $aksic && $aksic['totals']['cases'] > 0 ? [
                'monthly' => $aksic['monthly'],
                'quota' => ['labels' => array_keys($aksic['quota']), 'values' => array_values($aksic['quota'])],
                'breakdown' => [
                    'labels' => array_column($aksic['breakdown'], 'label'),
                    'approved' => array_column($aksic['breakdown'], 'approved'),
                    'pending' => array_column($aksic['breakdown'], 'pending'),
                ],
            ] : null,
            'files' => $files && $files['totals']['files'] > 0 ? [
                'monthly' => $files['monthly'],
                'categories' => ['labels' => array_keys($files['categories']), 'values' => array_values($files['categories'])],
            ] : null,
        ];
    @endphp

    @push('modals')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof ApexCharts === 'undefined') { return; }
                const data = {{ Illuminate\Support\Js::from($chartData) }};
                const css = getComputedStyle(document.querySelector('.db-page'));
                const c = ['--s1', '--s2', '--s3', '--s4'].map(v => css.getPropertyValue(v).trim());
                const ink = '#475569', grid = '#e2e8f0';
                const whole = v => Math.round(v).toLocaleString();
                const intOnly = v => Number.isInteger(Math.round(+v * 1000) / 1000) ? whole(v) : '';
                // Whole-number ticks: small counts would otherwise repeat (0, 0, 1, 1 ...).
                const ticks = values => Math.max(1, Math.min(6, Math.max(0, ...values)));
                const base = {
                    chart: { fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: false } },
                    dataLabels: { enabled: false },
                    grid: { borderColor: grid, strokeDashArray: 3, padding: { left: 6, right: 8 } },
                    legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: ink }, markers: { size: 6 } },
                    xaxis: { labels: { style: { colors: ink, fontSize: '11px' } }, axisBorder: { color: grid }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: ink, fontSize: '11px' }, formatter: intOnly }, forceNiceScale: true, min: 0 },
                    tooltip: { y: { formatter: whole } },
                    states: { active: { filter: { type: 'none' } } },
                };
                const draw = (id, opts) => {
                    const el = document.getElementById(id);
                    if (el) { new ApexCharts(el, Object.assign({}, base, opts, { chart: Object.assign({}, base.chart, opts.chart) })).render(); }
                };

                if (data.aksic) {
                    draw('db-aksic-monthly', {
                        chart: { type: 'bar', height: 290 },
                        series: [
                            { name: 'Applications', data: data.aksic.monthly.total },
                            { name: 'Approved', data: data.aksic.monthly.approved },
                        ],
                        colors: [c[0], c[1]],
                        plotOptions: { bar: { columnWidth: '58%', borderRadius: 4, borderRadiusApplication: 'end' } },
                        stroke: { show: true, width: 2, colors: ['#fff'] },
                        xaxis: Object.assign({}, base.xaxis, { categories: data.aksic.monthly.labels }),
                        yaxis: Object.assign({}, base.yaxis, { tickAmount: ticks(data.aksic.monthly.total) }),
                    });
                    draw('db-aksic-quota', {
                        chart: { type: 'donut', height: 290 },
                        series: data.aksic.quota.values,
                        labels: data.aksic.quota.labels,
                        tooltip: { y: { formatter: v => whole(v) + ' cases' } },
                        colors: c,
                        stroke: { width: 2, colors: ['#fff'] },
                        legend: Object.assign({}, base.legend, { position: 'bottom', horizontalAlign: 'center' }),
                        dataLabels: { enabled: true, formatter: v => v >= 5 ? Math.round(v) + '%' : '', dropShadow: { enabled: false } },
                        plotOptions: { pie: { donut: { size: '64%', labels: { show: true, total: { show: true, label: 'Cases', color: ink, formatter: w => whole(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
                    });
                    draw('db-aksic-breakdown', {
                        chart: { type: 'bar', height: Math.max(190, data.aksic.breakdown.labels.length * 34 + 90), stacked: true },
                        series: [
                            { name: 'Approved', data: data.aksic.breakdown.approved },
                            { name: 'Pending', data: data.aksic.breakdown.pending },
                        ],
                        colors: [c[0], c[1]],
                        plotOptions: { bar: { horizontal: true, barHeight: '62%', borderRadius: 4, borderRadiusApplication: 'end', borderRadiusWhenStacked: 'last' } },
                        stroke: { show: true, width: 2, colors: ['#fff'] },
                        xaxis: Object.assign({}, base.xaxis, { categories: data.aksic.breakdown.labels, tickAmount: ticks(data.aksic.breakdown.approved.map((a, i) => a + data.aksic.breakdown.pending[i])), labels: { style: { colors: ink, fontSize: '11px' }, formatter: intOnly } }),
                        yaxis: { labels: { style: { colors: ink, fontSize: '12px' }, maxWidth: 160 } },
                    });
                }

                if (data.files) {
                    draw('db-files-monthly', {
                        chart: { type: 'area', height: 290 },
                        series: [{ name: 'Files added', data: data.files.monthly.total }],
                        colors: [c[0]],
                        stroke: { curve: 'monotoneCubic', width: 2 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.28, opacityTo: 0.02 } },
                        markers: { size: 0, hover: { size: 5 } },
                        legend: { show: false },
                        xaxis: Object.assign({}, base.xaxis, { categories: data.files.monthly.labels }),
                        yaxis: Object.assign({}, base.yaxis, { tickAmount: ticks(data.files.monthly.total) }),
                    });
                    draw('db-files-category', {
                        chart: { type: 'bar', height: Math.max(190, data.files.categories.labels.length * 34 + 80) },
                        series: [{ name: 'Files', data: data.files.categories.values }],
                        colors: [c[0]],
                        plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 4, borderRadiusApplication: 'end' } },
                        legend: { show: false },
                        xaxis: Object.assign({}, base.xaxis, { categories: data.files.categories.labels, tickAmount: ticks(data.files.categories.values), labels: { style: { colors: ink, fontSize: '11px' }, formatter: intOnly } }),
                        yaxis: { labels: { style: { colors: ink, fontSize: '12px' }, maxWidth: 190 } },
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
