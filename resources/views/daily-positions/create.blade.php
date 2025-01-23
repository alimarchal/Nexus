<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Add Daily Position
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="{{ route('daily-positions.index') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <!-- Display session message -->
                    <x-status-message class="mb-4 mt-4" />
                    <x-validation-errors class="mb-4 mt-4" />

                    <form method="POST" action="{{ route('daily-positions.store') }}" id="bankingForm">
                            @csrf


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

                                <div class="space-y-4">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Consumer</label>
                                        <input type="text" name="consumer" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Commercial/SME</label>
                                        <input type="text" name="commercial" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Micro</label>
                                        <input type="text" name="micro" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">AGRI</label>
                                        <input type="text" name="agri" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalAssets()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Assets</label>
                                        <input type="text" name="totalAssets" class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               value="0.000" readonly>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Accounts</label>
                                        <input type="text" name="noOfAccount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)">
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Profit</label>
                                        <input type="text" name="profit" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)">
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
                                        <input type="text" name="govtDeposit" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalDeposits()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Private Deposit</label>
                                        <input type="text" name="privateDeposit" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalDeposits()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total (Govt + Private)</label>
                                        <input type="text" name="totalDeposits" class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               value="0.000" readonly>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CASA</label>
                                        <input type="text" name="casa" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalCasaTdr()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">TDR</label>
                                        <input type="text" name="tdr" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)" onkeyup="calculateTotalCasaTdr()">
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total (CASA + TDR)</label>
                                        <input type="text" name="totalCasaTdr" class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               value="0.000" readonly>
                                    </div>

                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Grand Total</label>
                                        <input type="text" name="grandTotal" class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 font-semibold text-gray-700"
                                               value="0.000" readonly>
                                    </div>


                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of ACC</label>
                                        <input type="text" name="noOfAcc" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                               value="0.000" onblur="formatNumber(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <x-button class="ms-4">
                                Save
                            </x-button>
{{--                            <button type="submit" class="px-6 py-3 bg-blue-800 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">--}}
{{--                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>--}}
{{--                                </svg>--}}
{{--                                Save--}}
{{--                            </button>--}}
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('modals')
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
                const privateDeposit = parseFloat(document.querySelector('[name="privateDeposit"]').value) || 0;

                const total = govt + privateDeposit;
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

                const grandTotal = totalDeposits - totalCasaTdr;
                document.querySelector('[name="grandTotal"]').value = grandTotal.toFixed(3);
            }

            document.getElementById('bankingForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const totalDeposits = parseFloat(document.querySelector('[name="totalDeposits"]').value);
                const totalCasaTdr = parseFloat(document.querySelector('[name="totalCasaTdr"]').value);

                const totalGT = totalDeposits - totalCasaTdr;

                if (totalDeposits !== totalCasaTdr) {
                    alert('Total Difference Must Equal to Zero \nYour Difference is: ' + totalGT + '\nPlease correct before submission.');
                    return false;
                }

                // Form is valid, can be submitted
                // alert('Form is valid and ready to submit');
                // Form is valid, submit it
                this.submit();
            });
        </script>
    @endpush
</x-app-layout>
