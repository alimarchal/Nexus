<x-app-layout>
    <x-slot name="header">
        <h2 class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Transfer Document Record: {{ $fileManagementSystem->digital_id }}
        </h2>
        <div class="float-right flex items-center justify-center">
            <a href="{{ route('file-management-systems.show', $fileManagementSystem) }}"
                class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg dark:bg-gray-800">
                <dl class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Current Owner</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->fileable_label }}:
                            {{ $fileManagementSystem->fileable_name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Current Custodian</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">
                            {{ $fileManagementSystem->currentCustodian?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-500 dark:text-gray-400">Digital ID</dt>
                        <dd class="mt-1 text-gray-800 dark:text-gray-200">{{ $fileManagementSystem->digital_id }}</dd>
                    </div>
                </dl>

                <form method="POST"
                    action="{{ route('file-management-systems.transfers.store', $fileManagementSystem) }}">
                    @csrf
                    <div x-data="{ type: '{{ old('destination_fileable_type', 'branch') }}' }"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Destination Unit Type</label>
                            <select name="destination_fileable_type" x-model="type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                required>
                                <option value="branch">Branch</option>
                                <option value="region">Region</option>
                                <option value="division">Division</option>
                                <option value="head-office">Head Office</option>
                            </select>
                            @error('destination_fileable_type') <span class="text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div x-show="type === 'branch'">
                            <label class="block text-gray-700 dark:text-gray-300">Destination Branch</label>
                            <select name="destination_fileable_id" :disabled="type !== 'branch'"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">
                                        {{ $branch->code ? $branch->code . ' - ' : '' }}{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="type === 'region'">
                            <label class="block text-gray-700 dark:text-gray-300">Destination Region</label>
                            <select name="destination_fileable_id" :disabled="type !== 'region'"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select Region</option>
                                @foreach ($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach
                            </select>
                        </div>
                        <div x-show="type === 'division'">
                            <label class="block text-gray-700 dark:text-gray-300">Destination Division</label>
                            <select name="destination_fileable_id" :disabled="type !== 'division'"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->short_name ?? $division->name }}
                                </option>@endforeach
                            </select>
                        </div>
                        <div x-show="type === 'head-office'">
                            <label class="block text-gray-700 dark:text-gray-300">Destination Head Office</label>
                            <select name="destination_fileable_id" :disabled="type !== 'head-office'"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select Head Office</option>
                                @foreach ($headOffices as $headOffice)
                                <option value="{{ $headOffice->id }}">{{ $headOffice->name }}</option>@endforeach
                            </select>
                        </div>
                        @error('destination_fileable_id') <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror

                        <div class="sm:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300">Receiving Custodian</label>
                            <select name="recipient_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                required>
                                <option value="">Select Custodian</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                            </select>
                            @error('recipient_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300">Reason for Transfer</label>
                            <textarea name="reason" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                required>{{ old('reason') }}</textarea>
                            @error('reason') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end"><button type="submit"
                            class="rounded-md bg-blue-800 px-6 py-2 text-white transition duration-200 hover:bg-blue-900">Submit
                            Transfer Request</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>