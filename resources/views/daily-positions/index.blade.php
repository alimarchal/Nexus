<x-app-layout>

    @push('header')
        <link rel="stylesheet" href="{{ url('jsandcss/daterangepicker.min.css') }}">
        <script src="{{ url('jsandcss/moment.min.js') }}"></script>
        <script src="{{ url('jsandcss/knockout-3.5.1.js') }}" defer></script>
        <script src="{{ url('jsandcss/daterangepicker.min.js') }}" defer></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Daily Position Records
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bbg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>
            <a href="{{ route('daily-positions.create') }}" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add</span>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters" style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('daily-positions.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="branch" value="{{ __('Branch') }}" />
                            <select name="filter[branch]" id="branch" class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select Branch</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ request('filter.branch') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="date_range" value="{{ __('Date Range') }}" />
                            <input type="text" id="date_range" name="filter[date_range]" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" />
                        </div>

                        <div></div>
                        <div class="mt-4">
                            <x-button class="bg-blue-950 text-white">
                                {{ __('Apply Filters') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
            <x-status-message />
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">


                @if($dailyPositions ?? false)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                            <tr class="bg-blue-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">Branch</th>
                                <th class="py-2 px-2 text-center">Advances & Assets</th>
                                <th class="py-2 px-2 text-center">Deposit Liability</th>
                                <th class="py-2 px-2 text-center">Total CASA+TDR</th>
                                <th class="py-2 px-2 text-center">Profit</th>
                                <th class="py-2 px-2 text-center">Date</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="text-black text-md leading-normal font-medium">
                            @foreach($dailyPositions as $position)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $position->branch->name }}</td>
                                    <td class="py-1 px-2 text-center">{{ number_format($position->totalAssets, 3) }}</td>
                                    <td class="py-1 px-2 text-center">{{ number_format($position->totalDeposits, 3) }}</td>
                                    <td class="py-1 px-2 text-center">{{ number_format($position->totalCasaTdr, 3) }}</td>
                                    <td class="py-1 px-2 text-center">{{ number_format($position->profit, 3) }}</td>
                                    <td class="py-1 px-2 text-center">{{ $position->date->format('Y-m-d') }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <a href="{{ route('daily-positions.view', $position) }}" class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            View
                                        </a>
                                        <a href="{{ route('daily-positions.edit', $position) }}" class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            Edit
                                        </a>
                                        <form class="inline-block" method="POST" action="{{ route('daily-positions.destroy', $position) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 delete-button">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($dailyPositions->hasPages())
                        <div class="px-2 py-2">
                            {{ $dailyPositions->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-gray-500">
                        No records found
                    </div>
                @endif
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
        style.textContent = `
    #filters {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
`;
        document.head.appendChild(style);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const form = this.closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

