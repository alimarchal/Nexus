<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block print:hidden">
            AKSIC Rules & Loans Report
        </h2>

        <div class="flex justify-center items-center float-right print:hidden">
            <button onclick="window.print()"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Print
            </button>
            <a href="{{ route('aksic.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                AKSIC
            </a>
            <a href="{{ route('reports.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back
            </a>
        </div>
    </x-slot>

    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 mt-4 pb-16 print:p-0 print:m-0">
        <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg print:shadow-none">
            <style>
                @media print {
                    @page {
                        margin: 0.6cm;
                    }

                    body {
                        color: #000;
                    }

                    .print-hidden {
                        display: none !important;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }

                    th,
                    td {
                        border: 1px solid #000 !important;
                        padding: 3px 5px !important;
                        font-size: 10px !important;
                        color: #000 !important;
                    }

                    thead {
                        display: table-header-group;
                    }
                }
            </style>

            <div class="p-4 print:p-0">
                <div class="text-center mb-4">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 print:text-black">
                        AKSIC Rules & Loans Report
                    </h1>
                    <p class="text-sm text-gray-700 dark:text-gray-300 print:text-black">
                        Generated: {{ now()->format('d-M-Y h:i A') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 print:hidden">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Proposed Beneficiaries</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($totals['proposed_beneficiaries']) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Loans Done</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($totals['loans_done']) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Loan Amount</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($totals['principal_amount'], 2) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Interest</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($totals['interest_amount'], 2) }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4 print:hidden">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Beneficiaries by District</h3>
                        <div id="beneficiaryChart"></div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Gender Quota by District</h3>
                        <div id="genderQuotaChart"></div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Actual Gender Loans</h3>
                        <div id="actualGenderChart"></div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Amount & Interest by District</h3>
                        <div id="amountChart"></div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-400 print:overflow-visible print:border-0">
                    <table class="min-w-[1900px] w-full text-xs border-collapse text-left text-black dark:text-gray-300 print:min-w-full print:text-black">
                        <caption class="caption-top text-left p-2 text-sm font-bold">District-wise AKSIC Rules and Loan Position</caption>
                        <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <tr>
                                <th class="px-2 py-2 border border-black text-center whitespace-nowrap">#</th>
                                <th class="px-2 py-2 border border-black text-left whitespace-nowrap">District</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Population %</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Proposed</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Male 48%</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Female 48%</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Disabled 2%</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Transgender 2%</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Male Loans</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Female Loans</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Disabled Male</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Disabled Female</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Transgender Loans</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Loans Done</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Remaining</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Loan Amount</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Interest</th>
                                <th class="px-2 py-2 border border-black text-right whitespace-nowrap">Total Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportRows as $row)
                                <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-700 print:bg-gray-100' : '' }}">
                                    <td class="px-2 py-2 border border-black text-center whitespace-nowrap">{{ $loop->iteration }}</td>
                                    <td class="px-2 py-2 border border-black text-left whitespace-nowrap">{{ $row['district'] }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['population_percentage'], 2) }}%</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['proposed_beneficiaries']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['male_beneficiaries']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['female_beneficiaries']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['disabled_beneficiaries']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['transgender_beneficiaries']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['actual_male_loans']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['actual_female_loans']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['actual_disabled_male_loans']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['actual_disabled_female_loans']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['actual_transgender_loans']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['loans_done']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['remaining']) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['principal_amount'], 2) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['interest_amount'], 2) }}</td>
                                    <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($row['total_payable'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="font-bold bg-gray-100 dark:bg-gray-700 print:bg-gray-200">
                            <tr>
                                <td colspan="2" class="px-2 py-2 border border-black text-right whitespace-nowrap">Totals</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['population_percentage'], 2) }}%</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['proposed_beneficiaries']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['male_beneficiaries']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['female_beneficiaries']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['disabled_beneficiaries']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['transgender_beneficiaries']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['actual_male_loans']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['actual_female_loans']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['actual_disabled_male_loans']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['actual_disabled_female_loans']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['actual_transgender_loans']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['loans_done']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['remaining']) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['principal_amount'], 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['interest_amount'], 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right whitespace-nowrap">{{ number_format($totals['total_payable'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
                        { name: 'Loans Done', data: chartData.loansDone }
                    ],
                    xaxis: { categories: chartData.districts },
                    yaxis: { labels: { formatter: value => Math.round(value).toLocaleString() } },
                    colors: ['#1e3a8a', '#15803d'],
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
                    yaxis: { labels: { formatter: value => Math.round(value).toLocaleString() } },
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
                        { name: 'Interest', data: chartData.interestAmounts }
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
