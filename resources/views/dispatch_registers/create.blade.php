<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Create Dispatch Register Entry
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('dispatch-registers.index') }}"
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

                    <form method="POST" action="{{ route('dispatch-registers.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">


                            <div>
                                <x-label for="date" value="Date" :required="true" />
                                <x-input id="date" type="date" name="date" class="mt-1 block w-full"
                                    :value="old('date')" required />
                            </div>

                            <div>
                                <x-label for="dispatch_no" value="Dispatch Number" :required="true" />
                                <x-input id="dispatch_no" type="text" name="dispatch_no" class="mt-1 block w-full"
                                    :value="old('dispatch_no')" required placeholder="Enter dispatch number" />
                            </div>

                            <div>
                                <x-label for="division_id" value="Division" :required="true" />
                                <select id="division_id" name="division_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}"
                                            {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-label for="name_of_courier_service" value="Courier Name" />
                                <x-input id="name_of_courier_service" type="text" name="name_of_courier_service"
                                    class="mt-1 block w-full" :value="old('name_of_courier_service')" placeholder="Optional" />
                            </div>



                            <div>
                                <x-label for="receipt_no" value="Receipt Number" />
                                <x-input id="receipt_no" type="text" name="receipt_no" class="mt-1 block w-full"
                                    :value="old('receipt_no')" placeholder="Optional" />
                            </div>

                            <div class="col-span-2">
                                <x-label for="particulars" value="Particulars" :required="true" />
                                <textarea id="particulars" name="particulars"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>{{ old('particulars') }}</textarea>
                            </div>

                            <div class="col-span-2">
                                <x-label for="address" value="Address" :required="true" />
                                <textarea id="address" name="address"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-span-2">
                                <x-label for="attachment" value="Attachment" />
                                <x-input id="attachment" type="file" name="attachment" class="mt-1 block w-full" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6">
                            <a href="{{ route('dispatch-registers.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>

                            <x-button class="ml-4 bg-blue-950 hover:bg-green-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Create Entry
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
