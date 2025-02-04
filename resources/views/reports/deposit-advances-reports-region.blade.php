<x-app-layout>
    @push('header')
        <link rel="stylesheet" href="{{ url('jsandcss/daterangepicker.min.css') }}">
        <script src="{{ url('jsandcss/moment.min.js') }}"></script>
        <script src="{{ url('jsandcss/knockout-3.5.1.js') }}" defer></script>
        <script src="{{ url('jsandcss/daterangepicker.min.js') }}" defer></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Deposit & Advances RegionWise Reports
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8 print:hidden" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="{{ route('reports.daily-position-report') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block text-gray-700 font-bold mb-2">Date</label>
                        <input type="date" name="filter[date]" value="{{ request('filter.date') }}" id="date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="branch_id" value="{{ __('Branch') }}"/>
                        <select name="filter[branch_id]" id="branch_id"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                            <option value="">Select a branch</option>
                            @foreach (\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" {{ request('filter.branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->code . ' - ' . $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="mt-4">
                            <x-button class="bg-blue-950 text-white">
                                {{ __('Apply Filters') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-center text-2xl font-bold mb-4 text-gray-800 bg-gray-100 p-2 rounded">
                        Daily Regions Position as of {{ \Carbon\Carbon::parse(request('filter.date'))->format('d-M-Y') ?? Carbon::now()->format('d-M-Y') }}
                    </h1>
                    <table class="mb-4 w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-400">
                        <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-1 py-3 border border-black text-center">NO of branches</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Region</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Deposit</th>
                            <th scope="col" class="px-1 py-3 border border-black text-center">Advances</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyPositions as $region)
                                <tr>
                                    <td class="px-1 py-3 border border-black text-center">{{ $region->branches_count }}</td>
                                    <td class="px-1 py-3 border border-black text-center">{{ $region->name ?? 'N/A' }}</td>
                                    <td class="px-1 py-3 border border-black text-center">
                                        {{ number_format($region->deposit_sum, 3) }}
                                    </td>
                                    <td class="px-1 py-3 border border-black text-center">
                                        {{ number_format($region->advances_sum, 3) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const targetDiv = document.getElementById("filters");
        const btn = document.getElementById("toggle");

        // Check if elements exist in the DOM
        if (!targetDiv || !btn) {
            console.error("Elements not found in the DOM.");
            return;
        }

        // Function to show filters with a transition
        function showFilters() {
            targetDiv.style.display = 'block'; // Make the filter visible
            targetDiv.style.opacity = '0';
            targetDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                targetDiv.style.opacity = '1'; // Fade-in effect
                targetDiv.style.transform = 'translateY(0)'; // Move filter back into place
            }, 10);
        }

        // Function to hide filters with a transition
        function hideFilters() {
            targetDiv.style.opacity = '0';
            targetDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                targetDiv.style.display = 'none'; // Hide the filter
            }, 300);
        }

        // Toggle the visibility of the filter when the button is clicked
        btn.addEventListener('click', function(event) {
            event.stopPropagation();
            if (targetDiv.style.display === "none" || targetDiv.style.display === "") {
                showFilters(); // Show the filter
            } else {
                hideFilters(); // Hide the filter
            }
        });

        // Hide filters if clicking outside of the filter or button
        document.addEventListener('click', function(event) {
            if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn) {
                hideFilters(); // Hide the filter if clicked outside
            }
        });

        // Prevent clicks inside the filter from closing it
        targetDiv.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });
</script>

</x-app-layout>
