<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                    #{{ $complaint->reference_number }}
                </h2>
                <span
                    class="px-3 py-1 rounded-full text-sm font-medium
                    {{ $complaint->priority === 'high'
                        ? 'bg-red-100 text-red-800'
                        : ($complaint->priority === 'medium'
                            ? 'bg-yellow-100 text-yellow-800'
                            : 'bg-green-100 text-green-800') }}">
                    {{ ucfirst($complaint->priority) }} Priority
                </span>
            </div>
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
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Main Complaint Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Complaint Details</span>
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Subject</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $complaint->subject }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Description</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $complaint->description }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Created By</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $complaint->creator->name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Created At</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $complaint->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Status Information</span>
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Current Status</label>
                                        <p class="mt-1">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $complaint->status->name }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Assigned To</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $complaint->assignedDivision ? $complaint->assignedDivision->name : 'Not Assigned' }}
                                            {{ $complaint->assignedDivision ? '(' . $complaint->assignedDivision->short_name . ')' : '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Due Date</label>
                                        <p
                                            class="mt-1 text-sm {{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $complaint->due_date?->format('M d, Y') ?? 'Not set' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Time Remaining</label>
                                        <p
                                            class="mt-1 text-sm {{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $complaint->due_date ? $complaint->due_date->diffForHumans() : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolution Form -->
            @if ($complaint->status->code !== 'RESOLVED')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Update Status</span>
                        </h3>
                        <form method="POST" action="{{ route('complaints.update-status', $complaint) }}"
                            enctype="multipart/form-data">

                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Status</label>
                                    <select name="status_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                {{ $complaint->status_id == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reassign To</label>
                                    <select name="assigned_to"
                                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select User</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ $complaint->assigned_to == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comments</label>
                                <textarea name="comments" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <div>
                                <x-label for="attachment" value="Attachment" />
                                <input type="file" id="attachment" name="attachment"
                                    class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-950 file:text-white
                                    hover:file:bg-green-800" />
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Status Timeline</span>
                    </h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach ($complaint->histories()->latest()->get() as $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if (!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full {{ $history->status->code === 'RESOLVED' ? 'bg-green-500' : 'bg-blue-500' }} flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        Status changed to
                                                        <span class="font-medium">{{ $history->status->name }}</span>
                                                    </p>

                                                    @if ($history->comments)
                                                        <p class="mt-2 text-sm text-gray-500">{{ $history->comments }}
                                                        </p>
                                                    @endif

                                                    @if ($history->changes)
                                                        <div class="mt-2 text-sm text-gray-500">
                                                            @foreach (json_decode($history->changes, true) as $field => $change)
                                                                <p>
                                                                    <span
                                                                        class="font-medium">{{ ucfirst($field) }}</span>:
                                                                    {{ $change['old'] ?? 'N/A' }} →
                                                                    {{ $change['new'] ?? 'N/A' }}

                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if ($history->attachment)
                                                        <a href="{{ Storage::url($history->attachment) }}"
                                                            class="text-blue-600 hover:underline" target="_blank"
                                                            download>
                                                            <span class="font-medium">Attachment:</span>

                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 16v-8m0 8l-4-4m4 4l4-4M4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <span class="text-gray-500"></span>
                                                    @endif


                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                        <time datetime="{{ $history->created_at }}">
                                                            {{ $history->created_at->format('M d, Y H:i') }}
                                                        </time>
                                                        <div class="text-xs mt-1">by
                                                            {{ $history->changedBy->name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            @if ($complaint->attachments->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span>Attachments</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($complaint->attachments as $attachment)
                                <div
                                    class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $attachment->original_filename }}
                                        </p>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-xs text-gray-500">
                                                {{ number_format($attachment->file_size / 1024, 2) }} KB
                                            </p>
                                            <a href="{{ route('complaints.attachments.download', $attachment) }}"
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelector('.delete-button').addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('div').querySelector('form');

                Swal.fire({
                    title: 'Delete Complaint?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700',
                        cancelButton: 'px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50'
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
