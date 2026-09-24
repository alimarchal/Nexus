<x-app-layout>
    <x-slot name="header">
        <div class="ak-head print:hidden">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('reports.index') }}">Reports</a><span aria-hidden="true">›</span><span>AKSIC</span>
                </nav>
                <h1 class="ak-title">AKSIC Rules &amp; Loans Report</h1>
                <p class="ak-sub">District-wise scheme rules, gender quotas and loans done</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('reports.index') }}" class="ak-btn ak-btn-outline" title="Back"><span aria-hidden="true">←</span> Back</a>
                <a href="{{ route('aksic.index') }}" class="ak-btn ak-btn-outline">AKSIC cases</a>
                @can('view aksic budget')
                    <a href="{{ route('aksic-budgets.index') }}" class="ak-btn ak-btn-outline">Markup budget</a>
                @endcan
                <button type="button" onclick="window.print()" class="ak-btn ak-btn-primary">Print report</button>
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')
    @include('aksics._grid-style')

    @php
        $n = fn ($v) => number_format((float) $v);
        $m = fn ($v) => number_format((float) $v, 2);
        $usedPct = $totals['proposed_beneficiaries'] > 0 ? round($totals['applications'] / $totals['proposed_beneficiaries'] * 100, 1) : 0;
    @endphp

    <style>
        @page { size: A4 landscape; margin: 8mm; }
        .rr-h { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
        .rr-h h3 { font-size: 15px; font-weight: 700; color: #111827; }
        .rr-h p { font-size: 12px; color: #4b5563; }
        .rr-chart { background: #fff; border: 1px solid #d1d5db; border-radius: 10px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .rr-chart h4 { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .rr-print-only { display: none; }
        table.aksic-grid.rr-table { font-size: 12px; }
        table.aksic-grid.rr-table th { white-space: nowrap; }
        table.aksic-grid.rr-table td.full { color: #b91c1c; font-weight: 700; }
        @media print {
            html, body { background: #fff !important; }
            .min-h-screen { min-height: 0 !important; }
            .rr-screen-only { display: none !important; }
            .rr-print-only { display: block !important; }
            .rr-page { max-width: none !important; padding: 0 !important; margin: 0 !important; }
            .rr-card { box-shadow: none !important; border: 0 !important; padding: 0 !important; }
            .overflow-x-auto { overflow: visible !important; }
            table.aksic-grid.rr-table { font-size: 7.6px !important; width: 100% !important; }
            table.aksic-grid.rr-table th, table.aksic-grid.rr-table td { padding: 2px 3px !important; border: 1px solid #000 !important; }
            table.aksic-grid.rr-table th { white-space: normal; font-size: 7px !important; letter-spacing: 0 !important; }
            .rr-h h3 { font-size: 11px; color: #000; }
            table.aksic-grid thead th, table.aksic-grid tfoot td {
                background: #000 !important; color: #fff !important;
                -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
            }
            table.aksic-grid thead th { border-color: #fff #000 #fff #000 !important; }
            table.aksic-grid.rr-table td.full { color: #b91c1c !important; }
        }
    </style>

    <div class="rr-page mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Print header (paper only) --}}
        <div class="rr-print-only" style="color:#000;margin-bottom:6px">
            <p style="font-size:15px;font-weight:800;text-align:center">The Bank of Azad Jammu &amp; Kashmir</p>
            <p style="font-size:11.5px;font-weight:700;text-align:center">AKSIC PM Youth Loan Scheme &mdash; District-wise Rules &amp; Loans Report</p>
            <p style="font-size:9px;text-align:center">Printed {{ now()->format('d.m.Y H:i') }} by {{ auth()->user()?->name }} &middot; Applications = all live cases (counted against the limits) &middot; Loans / amounts = approved cases</p>
        </div>

        {{-- KPI cards --}}
        <section class="ak-kpis rr-screen-only" aria-label="Scheme summary">
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Proposed beneficiaries</span>
                    <span class="ak-kpi-value">{{ $n($totals['proposed_beneficiaries']) }}</span>
                    <span class="ak-kpi-hint">{{ $n($totals['remaining']) }} slots remaining</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Applications</span>
                    <span class="ak-kpi-value">{{ $n($totals['applications']) }}</span>
                    <span class="ak-kpi-hint">{{ $usedPct }}% of limit &middot; {{ $n($totals['pending']) }} pending approval</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Loans approved</span>
                    <span class="ak-kpi-value">{{ $n($totals['loans_done']) }}</span>
                    <span class="ak-kpi-hint">Rs {{ $m($totals['principal_amount']) }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.3 4.3a11.95 11.95 0 0 1 5.8-5.8l2.65-1.2m0 0-5.94-2.28m5.94 2.28-2.28 5.94" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Markup (approved)</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">Rs {{ $m($totals['interest_amount']) }}</span>
                    <span class="ak-kpi-hint">Total payable Rs {{ $m($totals['total_payable']) }}</span>
                </span>
            </div>
        </section>

        {{-- Position table --}}
        <section class="rr-card ak-card p-4 sm:p-5">
            <div class="rr-h">
                <h3>District-wise rules and loan position</h3>
                <p class="rr-screen-only">Applications count against the limits; loans and amounts are approved cases. Red = limit reached.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="aksic-grid rr-table">
                    <thead>
                        <tr class="group">
                            <th rowspan="2" class="ctr">#</th>
                            <th rowspan="2">District</th>
                            <th rowspan="2" class="num">Pop. %</th>
                            <th rowspan="2" class="num">Proposed</th>
                            <th colspan="4">Quota target (beneficiaries)</th>
                            <th colspan="5">Applications by quota</th>
                            <th colspan="4">Position</th>
                            <th colspan="3">Approved loans (Rs)</th>
                        </tr>
                        <tr>
                            <th class="num">Male</th><th class="num">Female</th><th class="num">Disabled</th><th class="num">Transgender</th>
                            <th class="num">Male</th><th class="num">Female</th><th class="num">Disabled M</th><th class="num">Disabled F</th><th class="num">Transgender</th>
                            <th class="num">Applications</th><th class="num">Approved</th><th class="num">Pending</th><th class="num">Remaining</th>
                            <th class="num">Loan amount</th><th class="num">Markup</th><th class="num">Total payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportRows as $row)
                            <tr>
                                <td class="ctr">{{ $loop->iteration }}</td>
                                <td><b>{{ $row['district'] }}</b></td>
                                <td class="num">{{ number_format($row['population_percentage'], 2) }}</td>
                                <td class="num">{{ $n($row['proposed_beneficiaries']) }}</td>
                                <td class="num">{{ $n($row['male_beneficiaries']) }}</td>
                                <td class="num">{{ $n($row['female_beneficiaries']) }}</td>
                                <td class="num">{{ $n($row['disabled_beneficiaries']) }}</td>
                                <td class="num">{{ $n($row['transgender_beneficiaries']) }}</td>
                                <td class="num">{{ $n($row['actual_male_loans']) }}</td>
                                <td class="num">{{ $n($row['actual_female_loans']) }}</td>
                                <td class="num">{{ $n($row['actual_disabled_male_loans']) }}</td>
                                <td class="num">{{ $n($row['actual_disabled_female_loans']) }}</td>
                                <td class="num">{{ $n($row['actual_transgender_loans']) }}</td>
                                <td class="num"><b>{{ $n($row['applications']) }}</b></td>
                                <td class="num">{{ $n($row['loans_done']) }}</td>
                                <td class="num">{{ $n($row['pending']) }}</td>
                                <td class="num {{ $row['remaining'] <= 0 ? 'full' : '' }}">{{ $n($row['remaining']) }}</td>
                                <td class="num">{{ $m($row['principal_amount']) }}</td>
                                <td class="num">{{ $m($row['interest_amount']) }}</td>
                                <td class="num">{{ $m($row['total_payable']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="20" class="ctr">No active AKSIC rules.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td><td>Total</td>
                            <td class="num">{{ number_format($totals['population_percentage'], 2) }}</td>
                            <td class="num">{{ $n($totals['proposed_beneficiaries']) }}</td>
                            <td class="num">{{ $n($totals['male_beneficiaries']) }}</td>
                            <td class="num">{{ $n($totals['female_beneficiaries']) }}</td>
                            <td class="num">{{ $n($totals['disabled_beneficiaries']) }}</td>
                            <td class="num">{{ $n($totals['transgender_beneficiaries']) }}</td>
                            <td class="num">{{ $n($totals['actual_male_loans']) }}</td>
                            <td class="num">{{ $n($totals['actual_female_loans']) }}</td>
                            <td class="num">{{ $n($totals['actual_disabled_male_loans']) }}</td>
                            <td class="num">{{ $n($totals['actual_disabled_female_loans']) }}</td>
                            <td class="num">{{ $n($totals['actual_transgender_loans']) }}</td>
                            <td class="num">{{ $n($totals['applications']) }}</td>
                            <td class="num">{{ $n($totals['loans_done']) }}</td>
                            <td class="num">{{ $n($totals['pending']) }}</td>
                            <td class="num">{{ $n($totals['remaining']) }}</td>
                            <td class="num">{{ $m($totals['principal_amount']) }}</td>
                            <td class="num">{{ $m($totals['interest_amount']) }}</td>
                            <td class="num">{{ $m($totals['total_payable']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- Charts (screen only) --}}
        <section class="rr-screen-only grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rr-chart"><h4>Proposed vs applications vs approved</h4><div id="beneficiaryChart"></div></div>
            <div class="rr-chart"><h4>Quota target by district</h4><div id="genderQuotaChart"></div></div>
            <div class="rr-chart"><h4>Applications by quota</h4><div id="actualGenderChart"></div></div>
            <div class="rr-chart"><h4>Approved loan amount &amp; markup</h4><div id="amountChart"></div></div>
        </section>

        <div class="rr-print-only">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;margin-top:34px;font-size:10px;color:#000">
                <div style="border-top:1px solid #000;padding-top:4px;text-align:center">Prepared by</div>
                <div style="border-top:1px solid #000;padding-top:4px;text-align:center">Checked by</div>
                <div style="border-top:1px solid #000;padding-top:4px;text-align:center">Approved by</div>
            </div>
        </div>
    </div>

    @push('modals')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = {{ Illuminate\Support\Js::from($chartData) }};

                new ApexCharts(document.querySelector('#beneficiaryChart'), {
                    chart: {
                        type: 'bar',
                        height: 340,
                        toolbar: { show: false }
                    },
                    series: [
                        { name: 'Proposed', data: chartData.proposed },
                        { name: 'Applications', data: chartData.applications },
                        { name: 'Approved', data: chartData.loansDone }
                    ],
                    xaxis: { categories: chartData.districts },
                    yaxis: { labels: { formatter: value => Math.round(value).toLocaleString() } },
                    colors: ['#1e3a8a', '#b45309', '#15803d'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' }
                }).render();

                new ApexCharts(document.querySelector('#genderQuotaChart'), {
                    chart: {
                        type: 'bar',
                        height: 340,
                        stacked: true,
                        toolbar: { show: false }
                    },
                    series: [
                        { name: 'Male', data: chartData.maleBeneficiaries },
                        { name: 'Female', data: chartData.femaleBeneficiaries },
                        { name: 'Disabled', data: chartData.disabledBeneficiaries },
                        { name: 'Transgender', data: chartData.transgenderBeneficiaries }
                    ],
                    xaxis: { categories: chartData.districts },
                    yaxis: { labels: { formatter: value => Math.round(value).toLocaleString() } },
                    colors: ['#1e3a8a', '#be185d', '#b45309', '#7c3aed'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' }
                }).render();

                new ApexCharts(document.querySelector('#actualGenderChart'), {
                    chart: {
                        type: 'bar',
                        height: 340,
                        stacked: true,
                        toolbar: { show: false }
                    },
                    series: [
                        { name: 'Male', data: chartData.actualMaleLoans },
                        { name: 'Female', data: chartData.actualFemaleLoans },
                        { name: 'Disabled Male', data: chartData.actualDisabledMaleLoans },
                        { name: 'Disabled Female', data: chartData.actualDisabledFemaleLoans },
                        { name: 'Transgender', data: chartData.actualTransgenderLoans }
                    ],
                    xaxis: { categories: chartData.districts },
                    // whole-number axis: loan counts are integers, so avoid repeated ticks like 1, 1, 2, 2
                    yaxis: { min: 0, forceNiceScale: true, tickAmount: Math.max(1, Math.min(10, Math.ceil(Math.max(0, ...chartData.districts.map((_, k) => [chartData.actualMaleLoans, chartData.actualFemaleLoans, chartData.actualDisabledMaleLoans, chartData.actualDisabledFemaleLoans, chartData.actualTransgenderLoans].reduce((sum, series) => sum + Number(series[k] || 0), 0)))))), labels: { formatter: value => Math.round(value).toLocaleString() } },
                    colors: ['#1e3a8a', '#be185d', '#0f766e', '#b45309', '#7c3aed'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' }
                }).render();

                new ApexCharts(document.querySelector('#amountChart'), {
                    chart: {
                        type: 'bar',
                        height: 340,
                        toolbar: { show: false }
                    },
                    series: [
                        { name: 'Loan Amount', data: chartData.principalAmounts },
                        { name: 'Markup', data: chartData.interestAmounts }
                    ],
                    xaxis: { categories: chartData.districts },
                    yaxis: { labels: { formatter: value => Number(value).toLocaleString() } },
                    colors: ['#0f766e', '#b45309'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: value => Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 }) } }
                }).render();
            });
        </script>
    @endpush
</x-app-layout>
