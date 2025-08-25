<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">Create Employee
            Resource</h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('employee_resources.index') }}"
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
                    <form method="POST" action="{{ route('employee_resources.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label for="resource_no" value="Resource Number" :required="true" />
                                <x-input id="resource_no" type="text" name="resource_no" class="mt-1 block w-full"
                                    :value="old('resource_no')" required />
                            </div>
                            <div>
                                <x-label for="division_id" value="Division" :required="true" />
                                <select id="division_id" name="division_id"
                                    class="select2 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id')==$division->id ?
                                        'selected' : '' }}>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-label for="category_id" value="Category" :required="true" />
                                <select id="category_id" name="category_id"
                                    class="select2 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id')==$category->id ?
                                        'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-label for="title" value="Title" />
                                <x-input id="title" type="text" name="title" class="mt-1 block w-full"
                                    :value="old('title')" />
                            </div>
                            <div class="col-span-2">
                                <x-label for="description" value="Description" />
                                <textarea id="description" name="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-span-2">
                                <x-label for="attachment" value="Attachment" />
                                <input type="file" id="attachment" name="attachment"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-950 file:text-white hover:file:bg-green-800" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ml-4">Create Resource</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function(){ $('#division_id,#category_id').select2({ width: '100%' }); });
    </script>
</x-app-layout>