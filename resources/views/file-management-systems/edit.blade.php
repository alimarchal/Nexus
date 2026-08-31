<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Document Record: {{ $fileManagementSystem->digital_id }}
        </h2>
        <div class="flex justify-center items-center float-right gap-2">
            @can('transfer file management systems')
                <a href="{{ route('file-management-systems.transfer', $fileManagementSystem) }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-900">
                    Transfer
                </a>
            @endcan
            @can('archive file management systems')
                @if (!$fileManagementSystem->is_archived)
                    <a href="{{ route('file-management-systems.archive-form', $fileManagementSystem) }}"
                        class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-purple-700">
                        Archive
                    </a>
                @endif
            @endcan
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
                <dl class="mb-6 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Record Owner (Org Unit)</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->fileable_label }}:
                            {{ $fileManagementSystem->fileable_name ?? 'N/A' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Created By</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->creator?->name ?? 'System' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Current Custodian</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->currentCustodian?->name ?? 'Unassigned' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Last Updated By</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->updater?->name ?? 'System' }}
                        </dd>
                    </div>

                    @if ($fileManagementSystem->is_archived)
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Archive Status</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 dark:bg-green-900 dark:text-green-200">
                                    🗃️ {{ $fileManagementSystem->box->box_number ?? 'Unknown' }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Archive Location</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->box->location ?? 'Not specified' }} (Position
                                {{ $fileManagementSystem->position_in_box }})
                            </dd>
                        </div>
                    @endif
                </dl>

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
                        <label class="block text-gray-700 dark:text-gray-300">File No <span
                                class="text-gray-400 text-xs">(your own reference, e.g. HRMS/12/ABC)</span>:</label>
                        <input type="text" name="file_no" value="{{ old('file_no', $fileManagementSystem->file_no) }}"
                            maxlength="60"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        @error('file_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                                <div
                                    class="mb-2 flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    <span>Page {{ str_pad((string) $loop->iteration, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span>#{{ $loop->iteration }}</span>
                                </div>

                                <a href="{{ $page->getUrl() }}" target="_blank" class="block">
                                    @if (str_starts_with((string) $page->mime_type, 'image/'))
                                        <img src="{{ $page->getUrl() }}"
                                            alt="{{ $page->getCustomProperty('original_filename', $page->file_name) }}"
                                            class="mx-auto h-32 w-full rounded-md object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-32 w-16 text-gray-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    @endif

                                    <p class="mt-2 break-all text-sm text-blue-600 hover:underline">
                                        {{ $page->getCustomProperty('original_filename', $page->file_name) }}
                                    </p>
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

                @include('file-management-systems.partials.activity-history')
                @include('file-management-systems.partials.transfers')
            </div>
        </div>
    </div>
</x-app-layout>