<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Branch Targets
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bbg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
            <a href="#"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden md:inline-block">Add Target</span>
            </a>
            <a href="#"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters" style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('branch-targets.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <div>
                            <x-label for="fiscal_year" value="{{ __('Fiscal Year') }}"/>
                            <select name="filter[fiscal_year]" id="fiscal_year"
                                    class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a branch</option>
                                @for($i = 2025; $i <= 2099; $i++)
                                    <option value="{{ $i }}" {{ request('filter.fiscal_year') == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div></div>
                    </div>

                    <div class="mt-4">
                        <x-button class="mc-bg-blue text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Display session message -->
                <x-status-message/>
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
                                           class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
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

