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

                <form method="POST" action="{{ route('complaints.update', $complaint->id) }}"
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
                            <label for="assigned_to" class="block text-gray-700">Assigned To:</label>
                            <select name="assigned_to" id="assigned_to"
                                class="select2 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $complaint->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-gray-700">Status:</label>
                            <select name="status" id="status"
                                class="select2 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ $complaint->status_id == $status->id ? 'selected' : '' }}>
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
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Update
                            Complaint</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>
