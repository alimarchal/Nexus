<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Document
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('docs.index') }}"
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

                    <form method="POST" action="{{ route('docs.update', $doc->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <x-label for="title" value="Document Title" :required="true" />
                                <x-input id="title" type="text" name="title" class="mt-1 block w-full"
                                         :value="old('title', $doc->title)" required />
                            </div>

                            <div>
                                <x-label for="category_id" value="Category" :required="true" />
                                <select id="category_id" name="category_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (old('category_id', $doc->category_id) == $category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-label for="division_id" value="Division" :required="true" />
                                <select id="division_id" name="division_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <option value="">Select Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}"
                                            {{ (old('division_id', $doc->division_id) == $division->id) ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-2">
                                <x-label for="description" value="Description" />
                                <textarea id="description" name="description"
                                          class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          rows="3">{{ old('description', $doc->description) }}</textarea>
                            </div>

                            <div class="col-span-2">
                                <x-label for="document" value="Document File" />
                                <input type="file" name="document" id="document"
                                       class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100" />
                                <p class="mt-1 text-sm text-gray-500">Leave empty to keep the current file. Allowed file types: PDF, DOC, DOCX (Max: 2MB)</p>

                                @if ($doc->document && Storage::disk('public')->exists($doc->document))
                                    <div class="mt-2">
                                        <p class="text-gray-600 dark:text-gray-400">Current file:</p>
                                        <a href="{{ asset('storage/' . $doc->document) }}" target="_blank"
                                           class="inline-flex items-center mt-1 text-blue-600 hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                            {{ basename($doc->document) }}
                                        </a>
                                    </div>
                                @else
                                    <p class="mt-2 text-yellow-600 dark:text-yellow-400">No file currently attached.</p>
                                @endif
                            </div>

                            <div class="col-span-2">
                                <x-label value="Created By" />
                                <x-input class="mt-1 block w-full" value="{{ $doc->user->name ?? 'Unknown' }}" disabled />
                            </div>

                            <div>
                                <x-label value="Created Date" />
                                <x-input class="mt-1 block w-full" value="{{ $doc->created_at ? $doc->created_at->format('d-m-Y H:i:s') : '-' }}" disabled />
                            </div>

                            <div>
                                <x-label value="Last Updated" />
                                <x-input class="mt-1 block w-full" value="{{ $doc->updated_at ? $doc->updated_at->format('d-m-Y H:i:s') : '-' }}" disabled />
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6">
                            <a href="{{ route('docs.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>

                            <x-button class="ml-4 bg-blue-950 hover:bg-green-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7" />
                                </svg>
                                Update Document
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
