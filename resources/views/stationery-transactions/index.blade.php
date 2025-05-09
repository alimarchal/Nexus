<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Stationery Transactions
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
            <a href="{{ route('stationery-transactions.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Transaction</span>
            </a>
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>

            <a href="{{ route('product.index') }}"
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

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('stationery-transactions.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Stationery Item -->
                        <div>
                            <x-label for="stationery_item" value="{{ __('Stationery Item') }}" />
                            <select name="filter[printed_stationery_id]" id="stationery_item"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Items</option>
                                @foreach ($stationeries as $stationery)
                                    <option value="{{ $stationery->id }}"
                                        {{ request('filter.printed_stationery_id') == $stationery->id ? 'selected' : '' }}>
                                        {{ $stationery->item_code }} - {{ $stationery->name ?? 'Unnamed' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Transaction Type -->
                        <div>
                            <x-label for="transaction_type" value="{{ __('Transaction Type') }}" />
                            <select name="filter[type]" id="transaction_type"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                <option value="opening_balance"
                                    {{ request('filter.type') == 'opening_balance' ? 'selected' : '' }}>Opening Balance
                                </option>
                                <option value="in" {{ request('filter.type') == 'in' ? 'selected' : '' }}>Stock In
                                </option>
                                <option value="out" {{ request('filter.type') == 'out' ? 'selected' : '' }}>Stock
                                    Out</option>
                            </select>
                        </div>

                        <!-- Filter by Reference Number -->
                        <div>
                            <x-label for="reference" value="{{ __('Reference Number') }}" />
                            <x-input type="text" name="filter[reference]" id="reference"
                                value="{{ request('filter.reference') }}" class="block mt-1 w-full" />
                        </div>

                        <!-- Filter by Stock Out Destination Type -->
                        <div>
                            <x-label for="stock_out_to" value="{{ __('Destination Type') }}" />
                            <select name="filter[stock_out_to]" id="stock_out_to"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Destination Types</option>
                                <option value="Branch"
                                    {{ request('filter.stock_out_to') == 'Branch' ? 'selected' : '' }}>Branch</option>
                                <option value="Region"
                                    {{ request('filter.stock_out_to') == 'Region' ? 'selected' : '' }}>Region</option>
                                <option value="Division"
                                    {{ request('filter.stock_out_to') == 'Division' ? 'selected' : '' }}>Division
                                </option>
                            </select>
                        </div>

                        <!-- Filter by Branch -->
                        <div>
                            <x-label for="branch_id" value="{{ __('Branch') }}" />
                            <select name="filter[branch_id]" id="branch_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ request('filter.branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Region -->
                        <div>
                            <x-label for="region_id" value="{{ __('Region') }}" />
                            <select name="filter[region_id]" id="region_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Regions</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ request('filter.region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Division -->
                        <div>
                            <x-label for="division_id" value="{{ __('Division') }}" />
                            <select name="filter[division_id]" id="division_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ request('filter.division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
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

                        <!-- Filter by Quantity Range -->
                        <div>
                            <x-label for="min_quantity" value="{{ __('Min Quantity') }}" />
                            <x-input type="number" name="filter[min_quantity]" id="min_quantity"
                                value="{{ request('filter.min_quantity') }}" class="block mt-1 w-full"
                                min="0" />
                        </div>

                        <div>
                            <x-label for="max_quantity" value="{{ __('Max Quantity') }}" />
                            <x-input type="number" name="filter[max_quantity]" id="max_quantity"
                                value="{{ request('filter.max_quantity') }}" class="block mt-1 w-full"
                                min="0" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex space-x-3">
                        <x-button class="bg-blue-950 text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>

                        <a href="{{ route('stationery-transactions.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            @if ($transactions->count() > 0)
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="min-w-max w-full table-auto text-sm">
                        <thead>
                            <tr class="bg-green-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">#</th>
                                <th class="py-2 px-2 text-center">Item Code</th>
                                <th class="py-2 px-2 text-center">Date</th>
                                <th class="py-2 px-2 text-center">Type</th>
                                <th class="py-2 px-2 text-center">Quantity</th>
                                <th class="py-2 px-2 text-center">Unit Price</th>
                                <th class="py-2 px-2 text-center">Total</th>
                                <th class="py-2 px-2 text-center">Stock In Hand</th>
                                <th class="py-2 px-2 text-center">Supply To</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-black text-md leading-normal font-extrabold">
                            @foreach ($transactions as $index => $transaction)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $index + 1 }}</td>
                                    <td class="py-1 px-2 text-center">
                                        {{ $transaction->printedStationery->item_code }}
                                        <div class="text-xs text-gray-500 font-normal">
                                            {{ $transaction->printedStationery->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="py-1 px-2 text-center">
                                        {{ $transaction->transaction_date->format('d-m-Y') }}</td>
                                    <td class="py-1 px-2 text-center">
                                        @if ($transaction->type == 'opening_balance')
                                            <span
                                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Opening
                                                Balance</span>
                                        @elseif($transaction->type == 'in')
                                            <span
                                                class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Stock
                                                In</span>
                                        @else
                                            <span
                                                class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Stock
                                                Out</span>
                                        @endif
                                    </td>
                                    <td class="py-1 px-2 text-center">
                                        {{ $transaction->quantity }}
                                    </td>

                                    <td class="py-1 px-2 text-center">
                                        {{ number_format($transaction->unit_price, 2) }}
                                    </td>

                                    <td class="py-1 px-2 text-center">
                                        {{ number_format($transaction->quantity * $transaction->unit_price, 2) }}
                                    </td>

                                    <td class="py-1 px-2 text-center">
                                        {{ number_format($transaction->balance_after_transaction, 2) }}</td>
                                    <td class="py-1 px-2 text-center">
                                        @if ($transaction->type == 'out')
                                            {{ $transaction->stock_out_to }}:
                                            @if ($transaction->stock_out_to == 'Branch' && $transaction->branch)
                                                {{ $transaction->branch->name }}
                                            @elseif($transaction->stock_out_to == 'Region' && $transaction->region)
                                                {{ $transaction->region->name }}
                                            @elseif($transaction->stock_out_to == 'Division' && $transaction->division)
                                                {{ $transaction->division->name }}
                                            @else
                                                N/A
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-1 px-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('stationery-transactions.show', $transaction) }}"
                                                class="inline-flex items-center px-2 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                View
                                            </a>
                                            <a href="{{ route('stationery-transactions.edit', $transaction) }}"
                                                class="inline-flex items-center px-2 py-1 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                Edit
                                            </a>
                                            {{--                                        <form action="{{ route('stationery-transactions.destroy', $transaction) }}" method="POST" class="inline-block"> --}}
                                            {{--                                            @csrf --}}
                                            {{--                                            @method('DELETE') --}}
                                            {{--                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this transaction? This may affect stock balances.')" --}}
                                            {{--                                                    class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"> --}}
                                            {{--                                                Delete --}}
                                            {{--                                            </button> --}}
                                            {{--                                        </form> --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-2 py-2">
                    {{ $transactions->links() }}
                </div>
            @else
                <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                    No transactions found.
                    <a href="{{ route('stationery-transactions.create') }}" class="text-blue-600 hover:underline">
                        Add a new transaction
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
