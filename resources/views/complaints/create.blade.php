<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Add New Complaint
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('complaints.index') }}"
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

                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4 p-4 rounded bg-red-100 text-red-700">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <input type="hidden" name="status_id" value="{{ $submitStatusId }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="subject" class="block text-gray-700">Subject:</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                required>
                            @error('subject')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700">Assign To Division:</label>
                            <select name="assigned_to" class="select2 w-full border-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('assigned_to') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }} ({{ $division->short_name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        @php
                            $today = now()->toDateString();
                            $maxDate = now()->addDays(7)->toDateString();
                        @endphp

                        <div>
                            <label class="block text-gray-700">Due Date:</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $today) }}"
                                min="{{ $today }}" max="{{ $maxDate }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                            @error('due_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Description:</label>
                        <textarea name="description" class="w-full border-gray-300 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Priority:</label>
                        <select name="priority" class="select2 w-full border-gray-300 rounded-md shadow-sm">
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
    </div>
</x-app-layout>
