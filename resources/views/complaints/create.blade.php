<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add New Complaint
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">

                <form method="POST" action="{{ route('complaints.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="subject" class="block text-gray-700">Subject:</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            required>
                        @error('subject')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status_id" class="block text-gray-700">Status:</label>
                        <select name="status_id" id="status_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            required>
                            <option value="">Select Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ old('status_id', isset($defaultStatus) ? $defaultStatus->id : '') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>




                    <div class="mb-4">
                        <label class="block text-gray-700">Description:</label>
                        <textarea name="description" class="w-full border-gray-300 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <input type="hidden" name="status_id" value="{{ $submitStatusId ?? '' }}">

                    <div class="mb-4">
                        <label class="block text-gray-700">Assigned To:</label>
                        <select name="assigned_to" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Due Date:</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                        @error('due_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Priority:</label>
                        <select name="priority" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('priority')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Attachments:</label>
                        <input type="file" name="attachments[]" multiple
                            class="w-full border-gray-300 rounded-md shadow-sm">
                        @error('attachments')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Save</button>
                    </div>
                </form>

            </div>
        </div>
</x-app-layout>
