<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Printed Stationery Details: {{ $printedStationery->item_code }}
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('printed-stationeries.index') }}"
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
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Stationery Information</h3>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Item Code:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->item_code }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Audit Information</h3>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created By:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->creator->name ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created At:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->created_at->format('d-m-Y H:i') }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated By:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->updater->name ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated At:</span>
                                <span class="mt-1 text-gray-900 dark:text-gray-100">{{ $printedStationery->updated_at->format('d-m-Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('printed-stationeries.edit', $printedStationery) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit
                        </a>


                        <form action="{{ route('printed-stationeries.destroy', $printedStationery) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this item?')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
