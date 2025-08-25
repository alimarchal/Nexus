<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Printed Stationeries
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>
            <a href="{{ route('printed-stationeries.create') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Stationery</span>
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 print:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                Print
            </button>
            <a href="javascript:window.location.reload();"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
              <a href="{{ route('reports.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4 print:hidden">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters" style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('report.printed-stationeries') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Distribution Type -->
                        <div>
                            <x-label for="distribution_type" value="{{ __('Distribution Type') }}" />
                            <select id="distribution_type" name="filter[distribution_type]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="branch" {{ $distributionType == 'branch' ? 'selected' : '' }}>Branch-wise</option>
                                <option value="region" {{ $distributionType == 'region' ? 'selected' : '' }}>Region-wise</option>
                                <option value="division" {{ $distributionType == 'division' ? 'selected' : '' }}>Division-wise</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <x-label for="year" value="{{ __('Year') }}" />
                            <select id="year" name="filter[year]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Date Range Filter Options -->
                        <div>
                            <x-label for="date_range_type" value="{{ __('Date Range Type') }}" />
                            <select id="date_range_type" name="filter[date_range_type]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="full_year" {{ (isset($dateRangeType) && $dateRangeType == 'full_year') || (!isset($dateRangeType) && $startMonth == 1 && $endMonth == 12) ? 'selected' : '' }}>Full Year</option>
                                <option value="quarter" {{ isset($dateRangeType) && $dateRangeType == 'quarter' ? 'selected' : '' }}>Quarter</option>
                                <option value="custom" {{ isset($dateRangeType) && $dateRangeType == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>

                        <!-- Quarter Selection (shown only when date_range_type is quarter) -->
                        <div id="quarter_filter" class="{{ isset($dateRangeType) && $dateRangeType == 'quarter' ? '' : 'hidden' }}">
                            <x-label for="quarter" value="{{ __('Quarter') }}" />
                            <select id="quarter" name="filter[quarter]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="1" {{ $quarter == '1' ? 'selected' : '' }}>Q1 (Jan-Mar)</option>
                                <option value="2" {{ $quarter == '2' ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                                <option value="3" {{ $quarter == '3' ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                                <option value="4" {{ $quarter == '4' ? 'selected' : '' }}>Q4 (Oct-Dec)</option>
                            </select>
                        </div>

                        <!-- Custom Month Range (shown only when date_range_type is custom) -->
                        <div id="start_month_filter" class="{{ isset($dateRangeType) && $dateRangeType == 'custom' ? '' : 'hidden' }}">
                            <x-label for="start_month" value="{{ __('Start Month') }}" />
                            <select id="start_month" name="filter[start_month]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($allMonths as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $startMonth == $monthNum ? 'selected' : '' }}>{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="end_month_filter" class="{{ isset($dateRangeType) && $dateRangeType == 'custom' ? '' : 'hidden' }}">
                            <x-label for="end_month" value="{{ __('End Month') }}" />
                            <select id="end_month" name="filter[end_month]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($allMonths as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $endMonth == $monthNum ? 'selected' : '' }}>{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch Filter (shown only when distribution_type is branch) -->
                        <div id="branch_filter" class="{{ $distributionType == 'branch' ? '' : 'hidden' }}">
                            <x-label for="branch_id" value="{{ __('Branch') }}" />
                            <select id="branch_id" name="filter[branch_id]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('filter.branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Region Filter (shown only when distribution_type is region) -->
                        <div id="region_filter" class="{{ $distributionType == 'region' ? '' : 'hidden' }}">
                            <x-label for="region_id" value="{{ __('Region') }}" />
                            <select id="region_id" name="filter[region_id]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Regions</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ request('filter.region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Division Filter (shown only when distribution_type is division) -->
                        <div id="division_filter" class="{{ $distributionType == 'division' ? '' : 'hidden' }}">
                            <x-label for="division_id" value="{{ __('Division') }}" />
                            <select id="division_id" name="filter[division_id]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('filter.division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex space-x-3">
                        <x-button class="bg-blue-950 text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>

                        <a href="{{ route('report.printed-stationeries') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Header - visible on screen and in print -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 mb-4 print:mt-0">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg print:shadow-none p-4">
            <div class="text-center">
                <h1 class="text-xl font-bold">Printed Stationeries Distribution Report</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-4 text-sm">
                    <div class="text-left border-r pr-4">
                        <p><strong>Report as of:</strong> {{ now()->format('d M, Y h:i A') }}</p>
                        <p><strong>Distribution Type:</strong>
                            @if($distributionType == 'branch')
                                Branch-wise
                            @elseif($distributionType == 'region')
                                Region-wise
                            @else
                                Division-wise
                            @endif
                        </p>
                        <p><strong>Year:</strong> {{ $year }}</p>
                        @if(isset($quarter))
                            <p><strong>Quarter:</strong> Q{{ $quarter }}
                                @if($quarter == '1')
                                    (Jan-Mar)
                                @elseif($quarter == '2')
                                    (Apr-Jun)
                                @elseif($quarter == '3')
                                    (Jul-Sep)
                                @elseif($quarter == '4')
                                    (Oct-Dec)
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="text-left md:pl-4">
                        <p><strong>Filter Applied:</strong>
                            @if($selectedEntityName != "All")
                                {{ ucfirst($distributionType) }}: {{ $selectedEntityName }}
                            @else
                                All {{ ucfirst($distributionType) }}s
                            @endif
                        </p>
                        <p><strong>Date Range:</strong> {{ $dateRangeText }}</p>
                        <p><strong>Data Type:</strong> Monthly Distribution</p>
                        @if($startMonth != 1 || $endMonth != 12)
                            <p><strong>Months Included:</strong>
                                @foreach($monthsInRange as $monthNum => $monthName)
                                    {{ $monthName }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16 print:p-0">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl print:shadow-none">
            <!-- Print-specific styles -->
            <style>
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        page-break-inside: avoid;
                    }
                    th, td {
                        padding: 2px 4px !important;
                        font-size: 10px !important;
                        border: 1px solid #000 !important;
                    }
                    .print-wrapper {
                        padding: 0.5cm;
                    }
                    tr:nth-child(even) {
                        background-color: #f8f8f8 !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    /* Header row styling */
                    .print-header-row {
                        display: table-row !important;
                        background-color: #f0f0f0 !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .print-header-row td {
                        padding: 8px !important;
                        font-size: 12px !important;
                    }
                    .print-header-title {
                        font-size: 16px !important;
                        font-weight: bold !important;
                    }
                    /* Hide screen-only elements when printing */
                    .screen-only {
                        display: none !important;
                    }
                }
            </style>

            <table class="mb-4 w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-400 print:text-black">
                <!-- Table Header for Printing -->
                <caption class="caption-top text-center p-2 text-lg font-bold print:block hidden">
                    Printed Stationeries Distribution Report
                    <div class="text-sm font-normal mt-1">
                        Year: {{ $year }} |
                        @if($distributionType == 'branch')
                            Branch-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                        @elseif($distributionType == 'region')
                            Region-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                        @else
                            Division-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                        @endif
                        | Date Range: {{ $dateRangeText }}
                    </div>
                </caption>

                <!-- Table Column Headers -->
                <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">S.NO</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">NAME</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">CODE</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">SUPPLY TO {{ strtoupper($distributionType) }}</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" colspan="{{ count($monthsInRange) }}">MONTHLY DISTRIBUTION OF STATIONERY</th>
                </tr>

                <tr>
                    @foreach($monthsInRange as $monthNum => $monthName)
                        <th scope="col" class="px-2 py-2 border border-black text-center">{{ $shortMonthNames[$monthNum] }}</th>
                    @endforeach
                </tr>
                </thead>
                <!-- Add Report Header Row Before Column Headers - Always visible in print -->
                <tbody>
                <tr class="hidden print:table-row print-header-row">
                    <td colspan="{{ 4 + count($monthsInRange) }}" class="p-4 text-center border border-black">
                        <div class="print-header-title">Printed Stationeries Distribution Report</div>
                        <div>
                            <strong>Year:</strong> {{ $year }} |
                            <strong>Distribution:</strong>
                            @if($distributionType == 'branch')
                                Branch-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                            @elseif($distributionType == 'region')
                                Region-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                            @else
                                Division-wise{{ $selectedEntityName != 'All' ? ' (' . $selectedEntityName . ')' : '' }}
                            @endif
                            | <strong>Date Range:</strong> {{ $dateRangeText }}
                            | <strong>Generated:</strong> {{ now()->format('d M, Y') }}
                        </div>
                    </td>
                </tr>
                @foreach($stationeries as $index => $item)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 print:hover:bg-white">
                        <td class="px-2 py-2 border border-black text-center">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 border border-black text-left">{{ $item->name }}</td>
                        <td class="px-2 py-2 border border-black text-center">{{ $item->item_code }}</td>
                        <td class="px-2 py-2 border border-black text-center">{{ $selectedEntityName }}</td>
                        @foreach($monthsInRange as $monthNum => $monthName)
                            <td class="px-2 py-2 border border-black text-center">
                                {{ $monthlyData[$item->id]['monthly_data'][$monthNum] > 0 ? $monthlyData[$item->id]['monthly_data'][$monthNum] : '0' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            const distributionTypeSelect = document.getElementById("distribution_type");
            const branchFilter = document.getElementById("branch_filter");
            const regionFilter = document.getElementById("region_filter");
            const divisionFilter = document.getElementById("division_filter");

            // Date range filter elements
            const dateRangeTypeSelect = document.getElementById("date_range_type");
            const quarterFilter = document.getElementById("quarter_filter");
            const startMonthFilter = document.getElementById("start_month_filter");
            const endMonthFilter = document.getElementById("end_month_filter");

            function showFilters() {
                targetDiv.style.display = 'block';
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.opacity = '1';
                    targetDiv.style.transform = 'translateY(0)';
                }, 10);
            }

            function hideFilters() {
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.display = 'none';
                }, 300);
            }

            btn.onclick = function(event) {
                event.stopPropagation();
                if (targetDiv.style.display === "none") {
                    showFilters();
                } else {
                    hideFilters();
                }
            };

            // Show/hide entity filters based on distribution type
            distributionTypeSelect.addEventListener('change', function() {
                branchFilter.classList.add('hidden');
                regionFilter.classList.add('hidden');
                divisionFilter.classList.add('hidden');

                if (this.value === 'branch') {
                    branchFilter.classList.remove('hidden');
                } else if (this.value === 'region') {
                    regionFilter.classList.remove('hidden');
                } else if (this.value === 'division') {
                    divisionFilter.classList.remove('hidden');
                }
            });

            // Show/hide date range filters based on date range type
            dateRangeTypeSelect.addEventListener('change', function() {
                // Hide all date range filters first
                quarterFilter.classList.add('hidden');
                startMonthFilter.classList.add('hidden');
                endMonthFilter.classList.add('hidden');

                // Show appropriate filters based on selection
                if (this.value === 'quarter') {
                    quarterFilter.classList.remove('hidden');
                } else if (this.value === 'custom') {
                    startMonthFilter.classList.remove('hidden');
                    endMonthFilter.classList.remove('hidden');
                }
                // For 'full_year', we don't need to show any additional filters
            });

            // Hide filters when clicking outside
            document.addEventListener('click', function(event) {
                if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn) {
                    hideFilters();
                }
            });

            // Prevent clicks inside the filter from closing it
            targetDiv.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            // Add CSS for smooth transitions
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
        </script>
    @endpush
</x-app-layout>
