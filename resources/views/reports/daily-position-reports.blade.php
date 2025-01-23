<!-- resources/views/reports/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            {{ __('Reports List') }}
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="javascript:;" id="toggle"
               class="flex items-center px-4 py-2 text-gray-600 dark:bg-gray-700 dark:hover:bg-white dark:hover:text-black bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
               title="Search Filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="hidden md:inline-block ml-2" style="font-size: 14px;">Search Filters</span>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8 print:hidden" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="{{ route('reports.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block text-gray-700 font-bold mb-2">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" id="date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="branch_id" class="block text-gray-700 font-bold mb-2">Branch ID</label>
                        <input type="text" name="branch_id" value="{{ request('branch_id') }}" id="branch_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="branch_code" class="block text-gray-700 font-bold mb-2">Branch </label>
                        <input type="text" name="branch_code" value="{{ request('branch_code') }}" id="branch_code" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                type="submit">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <table class="mb-4 w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-400">
                        <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-1 py-3 border border-black text-center">ID</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Date</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Branch Name</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Branch Code</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($reports as $report)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-black text-left">
                                <td class="border px-2 py-2 border-black font-medium text-black dark:text-white text-center">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="border px-2 py-2 border-black font-medium text-black dark:text-white text-center">
                                    {{ $report->date }}
                                </td>
                                <td class="border px-2 py-2 border-black font-medium text-black dark:text-white text-center">
                                    {{ $report->branch_name }}
                                </td>
                                <td class="border px-2 py-2 border-black font-medium text-black dark:text-white text-center">
                                    {{ $report->branch_code }}
                                </td>
                                <td class="border px-2 py-2 border-black font-medium text-white dark:text-white text-center 
                                    {{ $report->status ? 'bg-green-500' : 'bg-red-500' }}">
                                    {{ $report->status ? 'OK' : 'Missing' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        <script>
            $('form').submit(function () {
                $(this).find(':submit').attr('disabled', 'disabled');
            });

            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            btn.onclick = function () {
                if (targetDiv.style.display !== "none") {
                    targetDiv.style.display = "none";
                } else {
                    targetDiv.style.display = "block";
                }
            };
        </script>
    @endpush
</x-app-layout>