<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Transaction Details: #{{ $stationeryTransaction->id }}
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
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Transaction Information</h3>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stationery Item:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $stationeryTransaction->printedStationery->item_code }} -
                                    {{ $stationeryTransaction->printedStationery->name ?? 'N/A' }}
                                </span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Transaction Type:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">
                                    @if($stationeryTransaction->type == 'opening_balance')
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Opening Balance</span>
                                    @elseif($stationeryTransaction->type == 'in')
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Stock In</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Stock Out</span>
                                    @endif
                                </span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Quantity:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->quantity }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Balance After Transaction:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->balance_after_transaction }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Unit Price:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $stationeryTransaction->unit_price ? number_format($stationeryTransaction->unit_price, 2) : 'N/A' }}
                                </span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Transaction Date:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->transaction_date->format('d-m-Y') }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Reference Number:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->reference_number ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div>
                            @if($stationeryTransaction->type == 'out')
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Destination Information</h3>

                                <div class="mb-4">
                                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Destination Type:</span>
                                    <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->stock_out_to }}</span>
                                </div>

                                @if($stationeryTransaction->stock_out_to == 'Branch' && $stationeryTransaction->branch)
                                    <div class="mb-4">
                                        <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Branch:</span>
                                        <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->branch->name }}</span>
                                    </div>
                                @elseif($stationeryTransaction->stock_out_to == 'Region' && $stationeryTransaction->region)
                                    <div class="mb-4">
                                        <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Region:</span>
                                        <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->region->name }}</span>
                                    </div>
                                @elseif($stationeryTransaction->stock_out_to == 'Division' && $stationeryTransaction->division)
                                    <div class="mb-4">
                                        <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Division:</span>
                                        <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->division->name }}</span>
                                    </div>
                                @endif
                            @endif

                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 {{ $stationeryTransaction->type == 'out' ? 'mt-6' : '' }}">
                                Audit Information
                            </h3>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created By:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->creator->name ?? 'N/A' }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created At:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->created_at->format('d-m-Y H:i') }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated By:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->updater->name ?? 'N/A' }}</span>
                            </div>

                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated At:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->updated_at->format('d-m-Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($stationeryTransaction->notes)
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Notes</h3>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <p class="text-gray-900 dark:text-gray-100">{{ $stationeryTransaction->notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($stationeryTransaction->document_path)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Supporting Document</h3>
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <a href="{{ route('stationery-transactions.download', $stationeryTransaction) }}" class="text-blue-600 hover:underline">
                                    View Document
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('stationery-transactions.edit', $stationeryTransaction) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit
                        </a>

{{--                        <form action="{{ route('stationery-transactions.destroy', $stationeryTransaction) }}" method="POST" class="inline-block">--}}
{{--                            @csrf--}}
{{--                            @method('DELETE')--}}
{{--                            <button type="submit" onclick="return confirm('Are you sure you want to delete this transaction? This may affect stock balances.')"--}}
{{--                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">--}}
{{--                                Delete--}}
{{--                            </button>--}}
{{--                        </form>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
