<x-app-layout>

        @push('header')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="{{ url('jsandcss/moment.min.js') }}"></script>
            <script src="{{ url('jsandcss/daterangepicker.min.js') }}" defer></script>
            <link rel="stylesheet" href="{{ url('jsandcss/daterangepicker.min.css') }}">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        @endpush

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
                Branch Targets
            </h2>

            <div class="flex justify-center items-center float-right">
                <button id="toggle" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    Search
                </button>
                <a href="{{ route('branch-targets.create') }}" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black focus:bg-black active:bg-black focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="hidden md:inline-block"></span>
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black focus:bg-black active:bg-black focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                </a>
            </div>
        </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">


            <!-- Filter Form (Initially Hidden) -->
            <div class="p-8" id="filters" style="display: none;">
                <form method="GET" action="{{ route('branch-targets.index') }}">
                    <!-- Form Header -->
                    <div class="text-2xl font-semibold text-gray-900 dark:text-gray-200 mb-6">
                        <span class="border-b-2 border-indigo-600 pb-1">{{ __('Filter Targets') }}</span>
                    </div>

                    <!-- Filter Fields -->
                    <div class="select2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Branch Selection -->
                        <div>
                            <label for="branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Branch') }}
                            </label>
                            <select
                                name="filter[branch_id]"
                                id="branch"
                                class="select2 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-600 dark:focus:border-indigo-600 shadow-md"
                            >
                                <option value="">Select Branch</option>
                                @foreach(\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}" {{ request('filter.branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fiscal Year -->
                        <div>
                            <label for="fiscal_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Fiscal Year') }}
                            </label>
                            <input
                                id="fiscal_year"
                                type="text"
                                name="filter[fiscal_year]"
                                value="{{ request('filter.fiscal_year') }}"
                                class=" select2 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-600 dark:focus:border-indigo-600 shadow-md"
                                placeholder="Enter Fiscal Year" />
                        </div>

                        <!-- Target Date Range -->
                        <div>
                            <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Target Date Range') }}
                            </label>
                            <input
                                type="text"
                                name="filter[target_date_range]"
                                id="date_range"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-600 dark:focus:border-indigo-600 shadow-md"
                                placeholder="Select Target Date Range"
                                />
                        </div>

                    </div>

                    <!-- Apply Filters Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-500 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-800 shadow-lg transition duration-200 ease-in-out"
                        >
                            {{ __('Apply Filters') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>






    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                @if (session('status'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('status') }}
                </div>
            @endif

                @if($branchTargets->count() > 0)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-blue-800 text-white uppercase text-sm">

                                    <th class="py-2 px-2 text-center">Branch Code</th>
                                    <th class="py-2 px-2 text-center">Branch Name</th>
                                    <th class="py-2 px-2 text-center">Fiscal Year</th>
                                    <th class="py-2 px-2 text-center">Target Amount</th>
                                    <th class="py-2 px-2 text-center">Start Date</th>
                                    <th class="py-2 px-2 text-center print:hidden">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-black text-md leading-normal font-extrabold">
                                @foreach($branchTargets as $target)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-1 px-2 text-center">{{ $target->branch->code }}</td>
                                        <td class="py-1 px-2 text-center">{{ $target->branch->name }}</td>
                                        <td class="py-1 px-2 text-center">{{ $target->fiscal_year }}</td>
                                        <td class="py-1 px-2 text-center">{{ number_format($target->annual_target_amount, 3) }}</td>
                                        <td class="py-1 px-2 text-center">{{ $target->target_start_date?->format('Y-m-d') }}</td>
                                        <td class="py-1 px-2 text-center">
                                            <a href="{{ route('branch-targets.edit', $target) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                Edit
                                            </a>

                                            <form class="inline-block" method="POST" action="{{ route('branch-targets.destroy', $target) }}">
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
                    <div class="px-2 py-2">
                        {{ $branchTargets->links() }}
                    </div>
                @else
                    <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                        No branch targets found.
                        <a href="{{ route('branch-targets.create') }}" class="text-blue-600 hover:underline">
                            Add a new target
                        </a>.
                    </p>
                @endif
            </div>
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

