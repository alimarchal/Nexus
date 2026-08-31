<x-app-layout>
    <x-slot name="header">
        <h2 class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Archive Document: {{ $fileManagementSystem->digital_id }}
        </h2>
        <div class="float-right flex items-center justify-center gap-2">
            <a href="{{ route('file-management-systems.boxes.create') }}"
                class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                New Box
            </a>
            <a href="{{ route('file-management-systems.show', $fileManagementSystem) }}"
                class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg dark:bg-gray-800">
                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="list-inside list-disc space-y-1 text-sm text-red-800 dark:text-red-200">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- File Information -->
                <div class="mb-6 border-b border-gray-200 pb-6 dark:border-gray-700">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">File Information</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Digital ID</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->digital_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Title</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->title ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Owner</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->fileable_label }}: {{ $fileManagementSystem->fileable_name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Document Date</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->document_date?->format('d M Y') ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Current Custodian</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->currentCustodian?->name ?? 'Unassigned' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Created By</dt>
                            <dd class="mt-1 text-gray-800 dark:text-gray-200">
                                {{ $fileManagementSystem->creator?->name ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Archive Form -->
                <form method="POST" action="{{ route('file-management-systems.archive', $fileManagementSystem) }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="box_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Select Box <span class="text-red-600">*</span>
                        </label>
                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                            @if ($boxes->isEmpty())
                                No open boxes available.
                                <a href="{{ route('file-management-systems.boxes.create') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                    Create a new box
                                </a>
                            @else
                                {{ $boxes->count() }} box(es) available
                            @endif
                        </p>

                        @if ($boxes->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($boxes as $box)
                                    <label class="flex items-start space-x-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                        <input type="radio" name="box_id" value="{{ $box->id }}" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                            @if (old('box_id') === $box->id) checked @endif required>
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ $box->box_number }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Location: {{ $box->location ?? 'Not specified' }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Capacity: {{ $box->file_count }} / {{ $box->capacity }} files
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('box_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <a href="{{ route('file-management-systems.show', $fileManagementSystem) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" @if ($boxes->isEmpty()) disabled @endif
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-900">
                            Archive to Selected Box
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
