<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Create Stationery Transaction
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('stationery-transactions.index') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-status-message class="mb-4 mt-4" />
                <div class="p-6">
                    <x-validation-errors class="mb-4 mt-4" />

                    <form method="POST" action="{{ route('stationery-transactions.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Stationery Item -->
                            <div>
                                <x-label for="printed_stationery_id" value="Stationery Item" :required="true" />
                                <select id="printed_stationery_id" name="printed_stationery_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select Stationery Item --</option>
                                    @foreach($stationeries as $stationery)
                                        <option value="{{ $stationery->id }}" {{ (old('printed_stationery_id') == $stationery->id || (isset($selectedStationery) && $selectedStationery->id == $stationery->id)) ? 'selected' : '' }}>
                                            {{ $stationery->item_code }} - {{ $stationery->name ?? 'Unnamed' }}
                                            (Stock: {{ $stationery->current_stock }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Select the stationery item for this transaction
                                </p>
                            </div>


                            <!-- Transaction Type -->
                            <div>
                                <x-label for="type" value="Transaction Type" :required="true" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select Transaction Type --</option>
                                    <option value="opening_balance" {{ old('type') == 'opening_balance' || $selectedTransactionType == 'opening_balance' ? 'selected' : '' }}>Opening Balance</option>
                                    <option value="in" {{ old('type') == 'in' || $selectedTransactionType == 'in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="out" {{ old('type') == 'out' || $selectedTransactionType == 'out' ? 'selected' : '' }}>Stock Out</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Type of transaction being recorded
                                </p>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <x-label for="quantity" value="Quantity" :required="true" />
                                <x-input id="quantity" type="number" name="quantity" class="mt-1 block w-full"
                                         :value="old('quantity')" required min="1" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Number of items to add/remove
                                </p>
                            </div>

                            <!-- Unit Price -->
                            <div>
                                <x-label for="unit_price" value="Unit Price" />
                                <x-input id="unit_price" type="number" name="unit_price" class="mt-1 block w-full"
                                         :value="old('unit_price')" step="0.01" min="0" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Price per unit (optional)
                                </p>
                            </div>

                            <!-- Transaction Date -->
                            <div>
                                <x-label for="transaction_date" value="Transaction Date" :required="true" />
                                <x-input id="transaction_date" type="date" name="transaction_date" class="mt-1 block w-full"
                                         :value="old('transaction_date', date('Y-m-d'))" required />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Date when this transaction occurred
                                </p>
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <x-label for="reference_number" value="Reference Number" />
                                <x-input id="reference_number" type="text" name="reference_number" class="mt-1 block w-full"
                                         :value="old('reference_number')" placeholder="Invoice/PO/Requisition number" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Reference document number (optional)
                                </p>
                            </div>
                        </div>

                        <!-- Stock Out Destination -->
                        <div id="stockOutSection" class="mb-6 {{ old('type') == 'out' ? '' : 'hidden' }}">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Stock Out Destination</h3>

                            <div class="mb-4">
                                <x-label for="stock_out_to" value="Destination Type" :required="true" />
                                <select id="stock_out_to" name="stock_out_to" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">-- Select Destination --</option>
                                    <option value="Branch" {{ old('stock_out_to') == 'Branch' ? 'selected' : '' }}>Branch</option>
                                    <option value="Region" {{ old('stock_out_to') == 'Region' ? 'selected' : '' }}>Region</option>
                                    <option value="Division" {{ old('stock_out_to') == 'Division' ? 'selected' : '' }}>Division</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Branch -->
                                <div id="branchSection" class="{{ old('stock_out_to') == 'Branch' ? '' : 'hidden' }}">
                                    <x-label for="branch_id" value="Branch" :required="true" />
                                    <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Select Branch --</option>
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Region -->
                                <div id="regionSection" class="{{ old('stock_out_to') == 'Region' ? '' : 'hidden' }}">
                                    <x-label for="region_id" value="Region" :required="true" />
                                    <select id="region_id" name="region_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Select Region --</option>
                                        @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Division -->
                                <div id="divisionSection" class="{{ old('stock_out_to') == 'Division' ? '' : 'hidden' }}">
                                    <x-label for="division_id" value="Division" :required="true" />
                                    <select id="division_id" name="division_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Select Division --</option>
                                        @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Supporting Document -->
                        <div class="mb-6">
                            <x-label for="document" value="Supporting Document" />
                            <input type="file" id="document" name="document" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                dark:file:bg-gray-700 dark:file:text-gray-200
                                dark:hover:file:bg-gray-600" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Upload invoice, purchase order, or requisition form (PDF, JPG, PNG, max 2MB)
                            </p>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-label for="notes" value="Notes" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Additional information about this transaction
                            </p>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6">
                            <a href="{{ route('stationery-transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>

                            <x-button class="ml-4 bg-blue-950 hover:bg-green-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Create Transaction
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        <script>
            // Show/hide stock out sections based on transaction type
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const stockOutSection = document.getElementById('stockOutSection');

                // Function to toggle visibility of stock out sections
                function toggleStockOutSection() {
                    if (typeSelect.value === 'out') {
                        stockOutSection.classList.remove('hidden');
                    } else {
                        stockOutSection.classList.add('hidden');
                        document.getElementById('stock_out_to').value = '';
                        document.getElementById('branch_id').value = '';
                        document.getElementById('region_id').value = '';
                        document.getElementById('division_id').value = '';

                        // Hide all destination sections
                        document.getElementById('branchSection').classList.add('hidden');
                        document.getElementById('regionSection').classList.add('hidden');
                        document.getElementById('divisionSection').classList.add('hidden');
                    }
                }

                // Add change event listener
                typeSelect.addEventListener('change', toggleStockOutSection);

                // Call immediately on page load to initialize correctly
                toggleStockOutSection();
            });

            // Show appropriate destination section based on stock_out_to selection
            document.getElementById('stock_out_to')?.addEventListener('change', function() {
                const branchSection = document.getElementById('branchSection');
                const regionSection = document.getElementById('regionSection');
                const divisionSection = document.getElementById('divisionSection');

                // Hide all sections first
                branchSection.classList.add('hidden');
                regionSection.classList.add('hidden');
                divisionSection.classList.add('hidden');

                // Show selected section
                if (this.value === 'Branch') {
                    branchSection.classList.remove('hidden');
                } else if (this.value === 'Region') {
                    regionSection.classList.remove('hidden');
                } else if (this.value === 'Division') {
                    divisionSection.classList.remove('hidden');
                }
            });
        </script>
    @endpush
</x-app-layout>
