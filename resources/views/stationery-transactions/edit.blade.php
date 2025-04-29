<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Transaction: #{{ $stationeryTransaction->id }}
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

                    <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                    <strong>Note:</strong> Transaction type, quantity, and stationery item cannot be changed.
                                    To modify these, please delete this transaction and create a new one.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('stationery-transactions.update', $stationeryTransaction) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Stationery Item (read-only) -->
                            <div>
                                <x-label for="printed_stationery_id" value="Stationery Item" :required="true" />
                                <select id="printed_stationery_id" name="printed_stationery_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-800" disabled required>
                                    @foreach($stationeries as $stationery)
                                        <option value="{{ $stationery->id }}" {{ $stationeryTransaction->printed_stationery_id == $stationery->id ? 'selected' : '' }}>
                                            {{ $stationery->item_code }} - {{ $stationery->name ?? 'Unnamed' }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="printed_stationery_id" value="{{ $stationeryTransaction->printed_stationery_id }}">
                            </div>

                            <!-- Transaction Type (read-only) -->
                            <div>
                                <x-label for="type" value="Transaction Type" :required="true" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-800" disabled required>
                                    <option value="opening_balance" {{ $stationeryTransaction->type == 'opening_balance' ? 'selected' : '' }}>Opening Balance</option>
                                    <option value="in" {{ $stationeryTransaction->type == 'in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="out" {{ $stationeryTransaction->type == 'out' ? 'selected' : '' }}>Stock Out</option>
                                </select>
                                <input type="hidden" name="type" value="{{ $stationeryTransaction->type }}">
                            </div>

                            <!-- Quantity (read-only) -->
                            <div>
                                <x-label for="quantity" value="Quantity" :required="true" />
                                <x-input id="quantity" type="number" class="mt-1 block w-full bg-gray-100 dark:bg-gray-800"
                                         value="{{ $stationeryTransaction->quantity }}" disabled required min="1" />
                                <input type="hidden" name="quantity" value="{{ $stationeryTransaction->quantity }}">
                            </div>

                            <!-- Unit Price -->
                            <div>
                                <x-label for="unit_price" value="Unit Price" />
                                <x-input id="unit_price" type="number" name="unit_price" class="mt-1 block w-full"
                                         :value="old('unit_price', $stationeryTransaction->unit_price)" step="0.01" min="0" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Price per unit (optional)
                                </p>
                            </div>

                            <!-- Transaction Date -->
                            <div>
                                <x-label for="transaction_date" value="Transaction Date" :required="true" />
                                <x-input id="transaction_date" type="date" name="transaction_date" class="mt-1 block w-full"
                                         :value="old('transaction_date', $stationeryTransaction->transaction_date->format('Y-m-d'))" required />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Date when this transaction occurred
                                </p>
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <x-label for="reference_number" value="Reference Number" />
                                <x-input id="reference_number" type="text" name="reference_number" class="mt-1 block w-full"
                                         :value="old('reference_number', $stationeryTransaction->reference_number)" placeholder="Invoice/PO/Requisition number" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Reference document number (optional)
                                </p>
                            </div>
                        </div>

                        <!-- Stock Out Destination -->
                        @if($stationeryTransaction->type == 'out')
                            <div id="stockOutSection" class="mb-6">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Stock Out Destination</h3>

                                <div class="mb-4">
                                    <x-label for="stock_out_to" value="Destination Type" :required="true" />
                                    <select id="stock_out_to" name="stock_out_to" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">-- Select Destination --</option>
                                        <option value="Branch" {{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Branch' ? 'selected' : '' }}>Branch</option>
                                        <option value="Region" {{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Region' ? 'selected' : '' }}>Region</option>
                                        <option value="Division" {{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Division' ? 'selected' : '' }}>Division</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Branch -->
                                    <div id="branchSection" class="{{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Branch' ? '' : 'hidden' }}">
                                        <x-label for="branch_id" value="Branch" :required="true" />
                                        <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">-- Select Branch --</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id', $stationeryTransaction->branch_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Region -->
                                    <div id="regionSection" class="{{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Region' ? '' : 'hidden' }}">
                                        <x-label for="region_id" value="Region" :required="true" />
                                        <select id="region_id" name="region_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">-- Select Region --</option>
                                            @foreach($regions as $region)
                                                <option value="{{ $region->id }}" {{ old('region_id', $stationeryTransaction->region_id) == $region->id ? 'selected' : '' }}>
                                                    {{ $region->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Division -->
                                    <div id="divisionSection" class="{{ old('stock_out_to', $stationeryTransaction->stock_out_to) == 'Division' ? '' : 'hidden' }}">
                                        <x-label for="division_id" value="Division" :required="true" />
                                        <select id="division_id" name="division_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">-- Select Division --</option>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ old('division_id', $stationeryTransaction->division_id) == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Current Document -->
                        @if($stationeryTransaction->document_path)
                            <div class="mb-4">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Current Document</h3>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <a href="{{ Storage::url($stationeryTransaction->document_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        View Document
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Supporting Document -->
                        <div class="mb-6">
                            <x-label for="document" value="Replace Document" />
                            <input type="file" id="document" name="document" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                dark:file:bg-gray-700 dark:file:text-gray-200
                                dark:hover:file:bg-gray-600" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Upload new document to replace the current one (PDF, JPG, PNG, max 2MB)
                            </p>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-label for="notes" value="Notes" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $stationeryTransaction->notes) }}</textarea>
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
                                Update Transaction
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        <script>
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
