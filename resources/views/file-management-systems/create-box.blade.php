<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Create New Archive Box
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="{{ route('file-management-systems.boxes') }}"
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

                <!-- Organization Unit Info -->
                <div class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                        <strong>Organization Unit:</strong>
                        @php
                            $unitName = match ($userOrgUnit['type']) {
                                'App\\Models\\Branch' => 'Branch',
                                'App\\Models\\Region' => 'Region',
                                'App\\Models\\Division' => 'Division',
                                'App\\Models\\HeadOffice' => 'Head Office',
                                default => $userOrgUnit['type'],
                            };
                        @endphp
                        {{ $unitName }} #{{ $userOrgUnit['id'] }}
                    </p>
                    <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                        All files archived to this box will be associated with your organization unit.
                    </p>
                </div>

                <!-- Box Creation Form -->
                <form method="POST" action="{{ route('file-management-systems.boxes.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="location" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Physical Location <span class="text-red-600">*</span>
                        </label>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                            Specify where this box will be stored (e.g., "Vault-A-Shelf-2", "Archive Room 1").
                        </p>
                        <input type="text" name="location" id="location" placeholder="e.g., Vault-A-Shelf-2"
                            value="{{ old('location') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            required>
                        @error('location')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Box Capacity (Number of Files)
                        </label>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                            How many files can this box hold? Default is 100.
                        </p>
                        <input type="number" name="capacity" id="capacity" placeholder="100" min="1" max="1000"
                            value="{{ old('capacity', 100) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        @error('capacity')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Box Number Info -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            📦 Box Number
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            A unique box number will be automatically generated in the format: <span
                                class="font-mono">BOX-{{ now()->year }}-###</span>
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <a href="{{ route('file-management-systems.boxes') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Create Box
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>