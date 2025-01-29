<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Branch Details
            </h2>
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
            {{-- Status Messages --}}
            <x-status-message class="mb-6"/>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Advances & Assets Section --}}
                    <section class="mb-8">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Advances & Assets</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $assets = [
                                    ['label' => 'Consumer', 'value' => $dailyPosition->consumer],
                                    ['label' => 'Commercial/SME', 'value' => $dailyPosition->commercial],
                                    ['label' => 'Micro', 'value' => $dailyPosition->micro],
                                    ['label' => 'AGRI', 'value' => $dailyPosition->agri],
                                    ['label' => 'Total Assets', 'value' => $dailyPosition->totalAssets, 'highlight' => true],
                                ];
                            @endphp

                            @foreach($assets as $asset)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $asset['label'] }}
                                    </div>
                                    <div class="mt-1 text-xl font-semibold {{ isset($asset['highlight']) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ number_format($asset['value'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Deposit Liability Section --}}
                    <section class="mb-8">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deposit Liability</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $deposits = [
                                    ['label' => 'Government Deposit', 'value' => $dailyPosition->govtDeposit],
                                    ['label' => 'Private Deposit', 'value' => $dailyPosition->privateDeposit],
                                    ['label' => 'Total (Govt + Private)', 'value' => $dailyPosition->totalDeposits, 'highlight' => true],
                                    ['label' => 'CASA', 'value' => $dailyPosition->casa],
                                    ['label' => 'TDR', 'value' => $dailyPosition->tdr],
                                    ['label' => 'Total (CASA + TDR)', 'value' => $dailyPosition->totalCasaTdr, 'highlight' => true],
                                    ['label' => 'Grand Total', 'value' => $dailyPosition->grandTotal, 'highlight' => true],
                                ];
                            @endphp

                            @foreach($deposits as $deposit)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $deposit['label'] }}
                                    </div>
                                    <div class="mt-1 text-xl font-semibold {{ isset($deposit['highlight']) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ number_format($deposit['value'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Additional Information Section --}}
                    <section>
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Additional Information</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @php
                                $additionalInfo = [
                                    ['label' => 'Number of Accounts', 'value' => $dailyPosition->noOfAccount],
                                    ['label' => 'Number of ACC', 'value' => $dailyPosition->noOfAcc],
                                    ['label' => 'Profit', 'value' => $dailyPosition->profit, 'highlight' => true],
                                ];
                            @endphp

                            @foreach($additionalInfo as $info)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $info['label'] }}
                                    </div>
                                    <div class="mt-1 text-xl font-semibold {{ isset($info['highlight']) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ is_numeric($info['value']) ? number_format($info['value'], 2) : $info['value'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
