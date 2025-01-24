
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Edit Daily Position</h2>
                        <a href="{{ route('daily-positions.index') }}"
                           class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                            Back to List
                        </a>
                    </div>
                    <div class="p-6">
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500">
                                <div class="text-green-700">
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        <!-- Error Message -->
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500">
                                <div class="text-red-700">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                    <x-status-message class="mb-4 mt-4" />


                    <div class="p-6">
                        <x-validation-errors class="mb-4 mt-4" />

                    <form id="bankingForm" action="{{ route('daily-positions.update', $dailyPosition) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Left Side -->
                            <div class="space-y-6">
                                <div class="pb-4 border-b border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Advances & Assets
                                    </h2>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Branch</label>
                                    <select name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $dailyPosition->branch_id == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-4">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Consumer</label>
                                        <input type="text" name="consumer"
                                               value="{{ old('consumer', number_format($dailyPosition->consumer, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Commercial/SME</label>
                                        <input type="text" name="commercial"
                                               value="{{ old('commercial', number_format($dailyPosition->commercial, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Micro</label>
                                        <input type="text" name="micro"
                                               value="{{ old('micro', number_format($dailyPosition->micro, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">AGRI</label>
                                        <input type="text" name="agri"
                                               value="{{ old('agri', number_format($dailyPosition->agri, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Assets</label>
                                        <input type="text" name="totalAssets"
                                               value="{{ old('totalAssets', number_format($dailyPosition->totalAssets, 3)) }}"
                                               class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side -->
                            <div class="space-y-6">
                                <div class="pb-4 border-b border-gray-200">
                                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Deposit Liability
                                    </h2>
                                </div>

                                <div class="space-y-4">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Government Deposit</label>
                                        <input type="text" name="govtDeposit"
                                               value="{{ old('govtDeposit', number_format($dailyPosition->govtDeposit, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalDeposits()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Private Deposit</label>
                                        <input type="text" name="privateDeposit"
                                               value="{{ old('privateDeposit', number_format($dailyPosition->privateDeposit, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalDeposits()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total (Govt + Private)</label>
                                        <input type="text" name="totalDeposits"
                                               value="{{ old('totalDeposits', number_format($dailyPosition->totalDeposits, 3)) }}"
                                               class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               readonly>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CASA</label>
                                        <input type="text" name="casa"
                                               value="{{ old('casa', number_format($dailyPosition->casa, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalCasaTdr()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">TDR</label>
                                        <input type="text" name="tdr"
                                               value="{{ old('tdr', number_format($dailyPosition->tdr, 3)) }}"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               onblur="formatNumber(this)" onkeyup="calculateTotalCasaTdr()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total (CASA + TDR)</label>
                                        <input type="text" name="totalCasaTdr"
                                               value="{{ old('totalCasaTdr', number_format($dailyPosition->totalCasaTdr, 3)) }}"
                                               class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               readonly>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Grand Total</label>
                                        <input type="text" name="grandTotal"
                                               value="{{ old('grandTotal', number_format($dailyPosition->grandTotal, 3)) }}"
                                               class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Section -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Accounts</label>
                                    <input type="text" name="noOfAccount"
                                           value="{{ old('noOfAccount', number_format($dailyPosition->noOfAccount, 3)) }}"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                           onblur="formatNumber(this)">
                                </div>

                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of ACC</label>
                                    <input type="text" name="noOfAcc"
                                           value="{{ old('noOfAcc', number_format($dailyPosition->noOfAcc, 3)) }}"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                           onblur="formatNumber(this)">
                                </div>

                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Profit</label>
                                    <input type="text" name="profit"
                                           value="{{ old('profit', number_format($dailyPosition->profit, 3)) }}"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                           onblur="formatNumber(this)">
                                </div>

                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" name="date"
                                           value="{{ old('date', $dailyPosition->date->format('Y-m-d')) }}"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('daily-positions.index') }}"
                               class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L"></path>
                                </svg>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatNumber(input) {
            let value = input.value.replace(/[^0-9.-]/g, '');
            if (value === '' || isNaN(value)) value = '0';
            let num = parseFloat(value);
            if (num < 0) num = 0;
            input.value = num.toFixed(3);
        }

        function calculateTotalAssets() {
            const consumer = parseFloat(document.querySelector('[name="consumer"]').value) || 0;
            const commercial = parseFloat(document.querySelector('[name="commercial"]').value) || 0;
            const micro = parseFloat(document.querySelector('[name="micro"]').value) || 0;
            const agri = parseFloat(document.querySelector('[name="agri"]').value) || 0;

            const total = consumer + commercial + micro + agri;
            document.querySelector('[name="totalAssets"]').value = total.toFixed(3);
        }

        function calculateTotalDeposits() {
            const govt = parseFloat(document.querySelector('[name="govtDeposit"]').value) || 0;
            const private = parseFloat(document.querySelector('[name="privateDeposit"]').value) || 0;

            const total = govt + private;
            document.querySelector('[name="totalDeposits"]').value = total.toFixed(3);
            calculateGrandTotal();
        }

        function calculateTotalCasaTdr() {
            const casa = parseFloat(document.querySelector('[name="casa"]').value) || 0;
            const tdr = parseFloat(document.querySelector('[name="tdr"]').value) || 0;

            const total = casa + tdr;
            document.querySelector('[name="totalCasaTdr"]').value = total.toFixed(3);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const totalDeposits = parseFloat(document.querySelector('[name="totalDeposits"]').value) || 0;
            const totalCasaTdr = parseFloat(document.querySelector('[name="totalCasaTdr"]').value) || 0;

            const grandTotal = totalDeposits + totalCasaTdr;
            document.querySelector('[name="grandTotal"]').value = grandTotal.toFixed(3);
        }

        document.getElementById('bankingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const totalDeposits = parseFloat(document.querySelector('[name="totalDeposits"]').value);
            const totalCasaTdr = parseFloat(document.querySelector('[name="totalCasaTdr"]').value);

            if (totalDeposits !== totalCasaTdr) {
                alert('Total Deposits must equal Total CASA + TDR');
                return false;
            }

            alert('Form is valid and ready to submit');
        });
    </script>
</body>
</html>


</x-app-layout>

