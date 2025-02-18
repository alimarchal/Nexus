<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Complaint
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
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('complaints.update', $complaint) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="subject" class="block text-gray-700">Subject:</label>
                            <input type="text" name="subject" id="subject"
                                value="{{ old('subject', $complaint->subject) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                required>
                        </div>

                        <div>
                            <x-label for="assigned_to" value="{{ __('Assign To Division') }}" />
                            <select name="assigned_to" id="assigned_to"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full"
                                required>
                                <option value="">Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('assigned_to', $complaint->assigned_to) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }} ({{ $division->short_name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>


                        <div>
                            <label for="status_id" class="block text-gray-700">Status:</label>
                            <select name="status_id" id="status_id"
                                class="select2 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ old('status_id', $complaint->status_id) == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-gray-700">Description:</label>
                        <textarea name="description" id="description" class="w-full border-gray-300 rounded-md shadow-sm" required>{{ old('description', $complaint->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Attachments:</label>
                        <input type="file" name="attachments[]" multiple
                            class="w-full border-gray-300 rounded-md shadow-sm">
                        <p class="text-sm text-gray-500">Leave empty to keep existing attachments.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">
                            Update Complaint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
