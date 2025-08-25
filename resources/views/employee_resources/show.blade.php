<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Employee Resource Details
        </h2>
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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">System Reference
                        </h3>
                        <p class="mt-1 text-lg font-bold">{{ $resource->resource_number }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Reference No</h3>
                        <p class="mt-1 text-lg">{{ $resource->reference_no ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Resource No</h3>
                        <p class="mt-1">{{ $resource->resource_no ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Division</h3>
                        <p class="mt-1">{{ $resource->division->name ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</h3>
                        <p class="mt-1">{{ $resource->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Title</h3>
                        <p class="mt-1">{{ $resource->title ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Created</h3>
                        <p class="mt-1">{{ $resource->created_at?->format('d-m-Y H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Updated</h3>
                        <p class="mt-1">{{ $resource->updated_at?->format('d-m-Y H:i') }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Description</h3>
                    <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed whitespace-pre-line">{{
                        $resource->description ?? '—' }}</div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Attachment</h3>
                    @if($resource->attachment)
                    <div class="flex space-x-2">
                        <a href="{{ route('file.download', $resource->attachment) }}" title="Download"
                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-500 text-white rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v12m0 0 4-4m-4 4-4-4m8 8H8" />
                            </svg>
                            <span class="sr-only">Download</span>
                        </a>
                        <a href="{{ route('file.view', $resource->attachment) }}" target="_blank" title="View"
                            class="inline-flex items-center justify-center w-8 h-8 bg-gray-600 hover:bg-gray-500 text-white rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12s3-7.5 9.75-7.5S21.75 12 21.75 12s-3 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                            <span class="sr-only">View</span>
                        </a>
                    </div>
                    @else
                    <p class="text-gray-400 text-sm">No attachment.</p>
                    @endif
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('employee_resources.edit', $resource) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-800 text-white rounded-md text-xs font-semibold hover:bg-blue-700">Edit</a>
                    <form method="POST" action="{{ route('employee_resources.destroy', $resource) }}"
                        onsubmit="return confirm('Are you sure you want to delete this resource?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md text-xs font-semibold hover:bg-red-600">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>