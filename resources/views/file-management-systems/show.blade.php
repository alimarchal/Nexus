<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Document Record: {{ $fileManagementSystem->digital_id }}
        </h2>

        <div class="flex justify-center items-center float-right">
            @can('edit file management systems')
                <a href="{{ route('file-management-systems.edit', $fileManagementSystem) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-900 focus:bg-green-900 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </a>
            @endcan
            @can('transfer file management systems')
                <a href="{{ route('file-management-systems.transfer', $fileManagementSystem) }}"
                    class="ml-2 inline-flex items-center rounded-md border border-transparent bg-blue-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-900">Transfer</a>
            @endcan
            @can('archive file management systems')
                @if (!$fileManagementSystem->is_archived)
                    <a href="{{ route('file-management-systems.archive-form', $fileManagementSystem) }}"
                        class="ml-2 inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-purple-700">
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

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Digital ID</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->digital_id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Category</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->fileCategory->category_name ?? 'N/A' }}
                        </dd>
                    </div>

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

                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Archived At</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->archived_at?->format('d-m-Y H:i') ?? 'N/A' }}
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Document Date</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->document_date?->format('d-m-Y') }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Title</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->title ?? '-' }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Storage Collection</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">Public disk / pages</dd>
                    </div>
                </dl>

                <hr class="my-6 border-gray-200 dark:border-gray-700">

                <h3 class="mb-3 font-semibold text-gray-700 dark:text-gray-300">Scanned Pages
                    ({{ $fileManagementSystem->media->count() }})</h3>

                @if ($fileManagementSystem->media->isNotEmpty())
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
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No pages uploaded.</p>
                @endif

                @include('file-management-systems.partials.activity-history')
                @include('file-management-systems.partials.transfers')
            </div>
        </div>
    </div>
</x-app-layout>