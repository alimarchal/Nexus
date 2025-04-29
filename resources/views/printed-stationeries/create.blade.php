<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Create Printed Stationery
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
                <x-status-message class="mb-4 mt-4" />
                <div class="p-6">
                    <x-validation-errors class="mb-4 mt-4" />

                    <form method="POST" action="{{ route('printed-stationeries.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <x-label for="item_code" value="Item Code" :required="true" />
                                <x-input id="item_code" type="text" name="item_code" class="mt-1 block w-full"
                                         :value="old('item_code')" required autofocus placeholder="Enter unique item code" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    A unique identifier for this stationery item
                                </p>
                            </div>

                            <div>
                                <x-label for="name" value="Name" />
                                <x-input id="name" type="text" name="name" class="mt-1 block w-full"
                                         :value="old('name')" placeholder="Enter stationery name" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Descriptive name for the stationery item
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6">
                            <a href="{{ route('printed-stationeries.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>

                            <x-button class="ml-4 bg-blue-950 hover:bg-green-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Create Stationery
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate a unique item code automatically when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            const itemCodeInput = document.getElementById('item_code');

            // Only prepopulate if the field is empty (to preserve user input if there's validation error)
            if (!itemCodeInput.value) {
                // Generate a prefix + random digits (you can customize this pattern)
                const prefix = 'ST';
                const randomDigits = Math.floor(Math.random() * 900000) + 100000; // 6-digit number
                itemCodeInput.value = `${prefix}-${randomDigits}`;
            }

            // Auto-select the text in the field when it gets focus
            itemCodeInput.addEventListener('focus', function() {
                this.select();
            });
        });
    </script>
</x-app-layout>
