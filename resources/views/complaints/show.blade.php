<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Complaint #{{ $complaint->reference_number }}
                </h2>
                <span
                    class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
                    {{ $complaint->priority === 'high'
                        ? 'bg-red-100 text-red-800 border border-red-200'
                        : ($complaint->priority === 'medium'
                            ? 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                            : 'bg-green-100 text-green-800 border border-green-200') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="{{ $complaint->priority === 'high'
                                ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                                : ($complaint->priority === 'medium'
                                    ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                    : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z') }}" />
                    </svg>
                    {{ ucfirst($complaint->priority) }} Priority
                </span>

                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm bg-blue-100 text-blue-800 border border-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ $complaint->status->name }}
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Main Complaint Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-800">Complaint Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-100 shadow-sm">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Details
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 block mb-1">Subject</label>
                                        <p class="text-gray-800 font-medium">{{ $complaint->subject }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Submitted By</label>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <p class="text-gray-800">{{ $complaint->creator->name }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Date Submitted</label>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p class="text-gray-800">{{ $complaint->created_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($complaint->description)
                                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                    <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                        Description
                                    </h4>
                                    <div class="prose prose-sm max-w-none">
                                        <p class="text-gray-700">{{ $complaint->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6 border border-purple-100 shadow-sm">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Status Information
                                </h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Current Status</label>
                                            <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full
                                                {{ $complaint->status->code === 'RESOLVED'
                                                    ? 'bg-green-100 text-green-800 border border-green-200'
                                                    : ($complaint->status->code === 'IN_PROGRESS'
                                                        ? 'bg-blue-100 text-blue-800 border border-blue-200'
                                                        : ($complaint->status->code === 'PENDING'
                                                            ? 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                                            : 'bg-gray-100 text-gray-800 border border-gray-200')) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="{{ $complaint->status->code === 'RESOLVED'
                                                            ? 'M5 13l4 4L19 7'
                                                            : ($complaint->status->code === 'IN_PROGRESS'
                                                                ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
                                                                : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z') }}" />
                                                </svg>
                                                {{ $complaint->status->name }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Assigned To</label>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <p class="text-gray-800">
                                                    {{ $complaint->assignedDivision ? $complaint->assignedDivision->name : 'Not Assigned' }}
                                                    {{ $complaint->assignedDivision ? '(' . $complaint->assignedDivision->short_name . ')' : '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Due Date</label>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5
                                                {{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600' : 'text-purple-600' }} mr-2"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p class="{{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-800' }}">
                                                    {{ $complaint->due_date?->format('M d, Y') ?? 'Not set' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 block mb-1">Time Remaining</label>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5
                                                {{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600' : 'text-purple-600' }} mr-2"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="{{ $complaint->due_date && $complaint->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-800' }}">
                                                    {{ $complaint->due_date ? $complaint->due_date->diffForHumans() : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($complaint->attachments->count() > 0)
                                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                    <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        Attachments
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach ($complaint->attachments as $attachment)
                                            <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors group">
                                                <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                                                    <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $attachment->original_filename }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ number_format($attachment->file_size / 1024, 2) }} KB
                                                    </p>
                                                </div>
                                                <a href="{{ route('complaints.attachments.download', $attachment) }}"
                                                   class="inline-flex items-center px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Download
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolution Form -->
            @if ($complaint->status->code !== 'RESOLVED')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">Update Status</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('complaints.update-status', $complaint) }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="status_id" class="block text-sm font-medium text-gray-700">New Status</label>
                                    <select id="status_id" name="status_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                {{ $complaint->status_id == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Reassign To</label>
                                    <select id="assigned_to" name="assigned_to"
                                            class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                <label for="comments" class="block text-sm font-medium text-gray-700">Comments</label>
                                <textarea id="comments" name="comments" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Add your comments here..."></textarea>
                            </div>

                            <div>
                                <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" id="attachment" name="attachment"
                                           class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-950 file:text-white
                                    hover:file:bg-green-800" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Upload any relevant documents related to this status update.
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-800">Status Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach ($complaint->histories()->latest()->get() as $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if (!$loop->last)
                                            <span class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gradient-to-b from-blue-500 to-purple-500"
                                                  aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex items-start space-x-4">
                                            <div>
                                                <div class="relative">
                                                    <span class="h-10 w-10 rounded-full bg-gradient-to-r {{ $history->status->code === 'RESOLVED' ? 'from-green-400 to-green-600' : 'from-blue-400 to-purple-600' }} flex items-center justify-center ring-8 ring-white">
                                                        @if($history->status->code === 'RESOLVED')
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        @elseif($history->status->code === 'IN_PROGRESS')
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                            </svg>
                                                        @else
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M10 2a8 8 0 100 16 8 8 0 000-16z" />
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0 bg-white rounded-lg border border-gray-100 shadow-sm p-4 hover:border-gray-200 transition-colors">
                                                <div>
                                                    <div class="flex justify-between items-center mb-1">
                                                        <p class="text-sm font-medium text-indigo-600">
                                                            Status changed to <span class="font-semibold">{{ $history->status->name }}</span>
                                                        </p>
                                                        <div class="flex space-x-2 text-sm text-gray-500">
                                                            <time datetime="{{ $history->created_at }}">
                                                                {{ $history->created_at->format('M d, Y H:i') }}
                                                            </time>
                                                        </div>
                                                    </div>

                                                    <p class="text-sm text-gray-500">
                                                        By <span class="font-medium text-gray-900">{{ $history->changedBy->name }}</span>
                                                    </p>

                                                    @if ($history->comments)
                                                        <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-100">
                                                            <p class="text-sm text-gray-700">
                                                                <span class="block font-medium text-gray-900 mb-1">Comment:</span>
                                                                {{ $history->comments }}
                                                            </p>
                                                        </div>
                                                    @endif

                                                    @if ($history->changes)
                                                        <div class="mt-3 p-3 bg-blue-50 rounded-md border border-blue-100">
                                                            @foreach (json_decode($history->changes, true) as $field => $change)
                                                                <p class="text-sm text-gray-700">
                                                                    <span class="block font-medium text-gray-900 mb-1">Status History:</span>
                                                                    <span class="text-blue-800">
                                                                        From <span class="font-medium">{{ \App\Models\ComplaintStatusType::find($change['old'])->code ?? 'N/A' }}</span>
                                                                        to <span class="font-medium">{{ \App\Models\ComplaintStatusType::find($change['new'])->code ?? 'N/A' }}</span>
                                                                    </span>
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if ($history->attachment)
                                                        <div class="mt-3">
                                                            <a href="{{ Storage::url($history->attachment) }}"
                                                               class="inline-flex items-center px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-md text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                                                               target="_blank" download>
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                                </svg>
                                                                Download Attachment
                                                            </a>
                                                        </div>
                                                    @endif
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
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Initialize any JavaScript functionality here
            document.addEventListener('DOMContentLoaded', function() {
                // SweetAlert for delete button if exists
                const deleteButton = document.querySelector('.delete-button');
                if (deleteButton) {
                    deleteButton.addEventListener('click', function(e) {
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
                }

                // Initialize select2 if available
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({
                        theme: 'classic',
                        width: '100%'
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
