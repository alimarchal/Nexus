<x-app-layout>
    <x-slot name="header">
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

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
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


    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Display session message -->

            @if ($stationeries->count() > 0)
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="min-w-max w-full table-auto text-sm">
                        <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-2 px-2 text-center">#</th>
                            <th class="py-2 px-2 text-center">Item Code</th>
                            <th class="py-2 px-2 text-center">Item Name</th>
                            <th class="py-2 px-2 text-center">Stock In Hand</th>
                            <th class="py-2 px-2 text-center">Latest Price</th>
{{--                            <th class="py-2 px-2 text-center">Created By</th>--}}
{{--                            <th class="py-2 px-2 text-center">Created At</th>--}}
                            <th class="py-2 px-2 text-center print:hidden">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="text-black text-md leading-normal font-extrabold">
                        @foreach ($stationeries as $index => $stationery)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-1 px-2 text-center">{{ $index + 1 }}</td>
                                <td class="py-1 px-2 text-center">{{ $stationery->item_code }}</td>
                                <td class="py-1 px-2 text-left">{{ $stationery->name ?? '-' }}</td>
                                <td class="py-1 px-2 text-center">
                                    <span class="{{ $stationery->current_stock > 0 ? 'text-green-700' : 'text-red-700' }} font-bold">
                                        {{ $stationery->current_stock }}
                                    </span>
                                </td>
                                <td class="py-1 px-2 text-center">
                                    {{ $stationery->latest_purchase_price ? number_format($stationery->latest_purchase_price, 2) : 'N/A' }}
                                </td>
{{--                                <td class="py-1 px-2 text-center">{{ $stationery->creator->name ?? 'N/A' }}</td>--}}
{{--                                <td class="py-1 px-2 text-center">{{ $stationery->created_at->format('d-m-Y') }}</td>--}}
                                <td class="py-1 px-2 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <!-- Stock Management Buttons -->
                                        <div class="flex space-x-1">
                                            <a href="{{ route('stationery-transactions.create', ['stationery_id' => $stationery->id, 'transaction_type' => 'in']) }}"
                                               class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" title="Stock In">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                <span class="hidden sm:inline-block ml-1">In</span>
                                            </a>
                                            <a href="{{ route('stationery-transactions.create', ['stationery_id' => $stationery->id, 'transaction_type' => 'out']) }}"
                                               class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" title="Stock Out">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7" />
                                                </svg>
                                                <span class="hidden sm:inline-block ml-1">Out</span>
                                            </a>
                                        </div>

                                        <!-- Edit Button -->
                                        <a href="{{ route('printed-stationeries.edit', $stationery) }}"
                                           class="inline-flex items-center px-3 py-1 bg-blue-800 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Edit
                                        </a>

                                        <!-- Delete Button -->
{{--                                        <form action="{{ route('printed-stationeries.destroy', $stationery) }}" method="POST" class="inline-block">--}}
{{--                                            @csrf--}}
{{--                                            @method('DELETE')--}}
{{--                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this item?')"--}}
{{--                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">--}}
{{--                                                Delete--}}
{{--                                            </button>--}}
{{--                                        </form>--}}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-2 py-2">
                    {{ $stationeries->links() }}
                </div>
            @else
                <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                    No printed stationeries found.
                    <a href="{{ route('printed-stationeries.create') }}" class="text-blue-600 hover:underline">
                        Add a new stationery item
                    </a>.
                </p>
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
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
        </script>
    @endpush
</x-app-layout>
