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
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Org Unit</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->fileable_label }}:
                            {{ $fileManagementSystem->fileable_name ?? 'N/A' }}
                        </dd>
                    </div>

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
                </dl>

                <hr class="my-6 border-gray-200 dark:border-gray-700">

                <h3 class="mb-3 font-semibold text-gray-700 dark:text-gray-300">Scanned Pages
                    ({{ $fileManagementSystem->media->count() }})</h3>

                @if ($fileManagementSystem->media->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($fileManagementSystem->media as $page)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-3 text-center">
                                <a href="{{ $page->getUrl() }}" target="_blank"
                                    class="text-blue-600 hover:underline text-sm break-all">
                                    {{ $page->file_name }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No pages uploaded.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>