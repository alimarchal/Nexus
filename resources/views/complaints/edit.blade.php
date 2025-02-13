<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Complaint
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">

                <form method="POST" action="{{ route('complaints.update', $complaint->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Example Input Fields -->
                    <div>
                        <label for="subject"
                            class="block font-medium text-sm text-gray-700 dark:text-gray-300">Subject</label>
                        <input type="text" name="subject" id="subject"
                            value="{{ old('subject', $complaint->subject) }}"
                            class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                    </div>

                    <div class="mt-4">
                        <label for="description"
                            class="block font-medium text-sm text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" class="form-textarea rounded-md shadow-sm mt-1 block w-full" required>{{ old('description', $complaint->description) }}</textarea>
                    </div>

                    <!-- Status Dropdown -->
                    <div class="mt-4">
                        <label for="status"
                            class=" block font-medium text-sm text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" id="status"
                            class=" select2 form-select rounded-md shadow-sm mt-1 block w-full">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ $complaint->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned To -->
                    <div class="mt-4">
                        <label for="assigned_to"
                            class=" block font-medium text-sm text-gray-700 dark:text-gray-300">Assigned
                            To</label>
                        <select name="assigned_to" id="assigned_to"
                            class="select2 form-select rounded-md shadow-sm mt-1 block w-full">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $complaint->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attachments Handling -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Attachments</label>
                        <input type="file" name="attachments[]" multiple
                            class="form-input rounded-md shadow-sm mt-1 block w-full">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Leave empty to keep existing attachments.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Complaint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
