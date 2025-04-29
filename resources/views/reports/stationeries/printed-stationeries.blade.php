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
            <a href="javascript:window.location.reload();"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4 print:hidden">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters" style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('printed-stationeries.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Item Code -->
                        <div>
                            <x-label for="item_code" value="{{ __('Item Code') }}" />
                            <x-input type="text" name="filter[item_code]" id="item_code"
                                     value="{{ request('filter.item_code') }}" class="block mt-1 w-full" />
                        </div>

                        <!-- Filter by Name -->
                        <div>
                            <x-label for="name" value="{{ __('Name') }}" />
                            <x-input type="text" name="filter[name]" id="name"
                                     value="{{ request('filter.name') }}" class="block mt-1 w-full" />
                        </div>

                        <!-- Filter by Date Range -->
                        <div>
                            <x-label for="date_from" value="{{ __('Date From') }}" />
                            <x-input type="date" name="filter[date_from]" id="date_from"
                                     value="{{ request('filter.date_from') }}" class="block mt-1 w-full" />
                        </div>

                        <div>
                            <x-label for="date_to" value="{{ __('Date To') }}" />
                            <x-input type="date" name="filter[date_to]" id="date_to"
                                     value="{{ request('filter.date_to') }}" class="block mt-1 w-full" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex space-x-3">
                        <x-button class="bg-blue-950 text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>

                        <a href="{{ route('printed-stationeries.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>
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
                }
            </style>

            <table class="mb-4 w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-400 print:text-black">
                <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">S.No</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">Name</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">Code</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" rowspan="2">Supply To Branch</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center" colspan="12">MONTHLY DISTRIBUTION OF STATIONERY</th>
                </tr>


                <tr>

                    <th scope="col" class="px-2 py-2 border border-black text-center">Jan</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Feb</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Mar</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Apr</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">May</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Jun</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Jul</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Aug</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Sep</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Oct</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Nov</th>
                    <th scope="col" class="px-2 py-2 border border-black text-center">Dec</th>
                </tr>
                </thead>
                <tbody>

                @foreach(\App\Models\PrintedStationery::all() as $item)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 print:hover:bg-white">
                        <td class="px-2 py-2 border border-black text-center">{{ $item->id }}</td>
                        <td class="px-2 py-2 border border-black text-left">{{ $item->name }}</td>
                        <td class="px-2 py-2 border border-black text-center">{{ $item->item_code }}</td>
                        <td class="px-2 py-2 border border-black text-center">North</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                        <td class="px-2 py-2 border border-black text-center">N/A</td>
                    </tr>
                @endforeach
                <!-- Row 1 -->

                </tbody>
            </table>
        </div>
    </div>

    @push('modals')
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");

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
