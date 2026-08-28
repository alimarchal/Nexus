<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Document Record: {{ $fileManagementSystem->digital_id }}
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('file-management-systems.index') }}"
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('file-management-systems.update', $fileManagementSystem) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Category:</label>
                        <select name="file_category_id"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            required>
                            @foreach ($fileCategories as $fileCategory)
                                <option value="{{ $fileCategory->id }}" {{ old('file_category_id', $fileManagementSystem->file_category_id) == $fileCategory->id ? 'selected' : '' }}>
                                    {{ $fileCategory->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('file_category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        @if ($autoFileable)
                            <label class="block text-gray-700 dark:text-gray-300">Org Unit:</label>
                            <input type="text" value="Branch: {{ $autoFileable['label'] }}" disabled
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-200 dark:text-gray-500 rounded-md shadow-sm">
                            <input type="hidden" name="fileable_type" value="{{ $autoFileable['type'] }}">
                            <input type="hidden" name="fileable_id" value="{{ $autoFileable['id'] }}">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Automatically set to your branch.</p>
                        @else
                            <div
                                x-data="{ fileableType: '{{ old('fileable_type', $fileManagementSystem->fileable_type) }}' }">
                                <label class="block text-gray-700 dark:text-gray-300">Org Unit Type:</label>
                                <select name="fileable_type" x-model="fileableType"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                    <option value="branch">Branch</option>
                                    <option value="region">Region</option>
                                    <option value="division">Division</option>
                                </select>
                                @error('fileable_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                                <div class="mt-4" x-show="fileableType === 'branch'">
                                    <label class="block text-gray-700 dark:text-gray-300">Branch:</label>
                                    <select name="fileable_id" :disabled="fileableType !== 'branch'"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('fileable_id', $fileManagementSystem->fileable_type === 'branch' ? $fileManagementSystem->fileable_id : null) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->code ? $branch->code . ' - ' : '' }}{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mt-4" x-show="fileableType === 'region'">
                                    <label class="block text-gray-700 dark:text-gray-300">Region:</label>
                                    <select name="fileable_id" :disabled="fileableType !== 'region'"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Region</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}" {{ old('fileable_id', $fileManagementSystem->fileable_type === 'region' ? $fileManagementSystem->fileable_id : null) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mt-4" x-show="fileableType === 'division'">
                                    <label class="block text-gray-700 dark:text-gray-300">Division:</label>
                                    <select name="fileable_id" :disabled="fileableType !== 'division'"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Division</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('fileable_id', $fileManagementSystem->fileable_type === 'division' ? $fileManagementSystem->fileable_id : null) == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('fileable_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Document Date:</label>
                        <input type="date" name="document_date"
                            value="{{ old('document_date', $fileManagementSystem->document_date?->format('Y-m-d')) }}"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            required>
                        @error('document_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Title:</label>
                        <input type="text" name="title" value="{{ old('title', $fileManagementSystem->title) }}"
                            maxlength="255"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Add More Scanned Pages (PDF, DOC, DOCX,
                            JPG, PNG):</label>
                        <x-drag-drop-file-input name="pages[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                        @error('pages') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        @error('pages.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition duration-200">Update
                            Document Record</button>
                    </div>
                </form>

                @if ($fileManagementSystem->media->isNotEmpty())
                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <h3 class="mb-3 font-semibold text-gray-700 dark:text-gray-300">Uploaded Pages
                        ({{ $fileManagementSystem->media->count() }})</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($fileManagementSystem->media as $page)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-3 text-center">
                                <a href="{{ $page->getUrl() }}" target="_blank"
                                    class="text-blue-600 hover:underline text-sm break-all">
                                    {{ $page->file_name }}
                                </a>

                                @can('edit file management systems')
                                    <form method="POST"
                                        action="{{ route('file-management-systems.media.destroy', [$fileManagementSystem, $page->id]) }}"
                                        class="mt-2" onsubmit="return confirm('Remove this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>