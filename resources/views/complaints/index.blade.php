<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Complaint Management
        </h2>
        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search & Filter
            </button>
            <a href="{{ route('complaints.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Complaint
            </a>
            <a href="{{ route('complaints.analytics') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Analytics
            </a>
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
        </div>
    </x-slot>

    <!-- FILTER SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('complaints.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by ID -->
                        <x-input-filters name="id" label="Complaint ID" type="number" />

                        <!-- Filter by Complaint Number -->
                        <x-input-filters name="complaint_number" label="Complaint Number" />

                        <!-- Filter by Title -->
                        <x-input-filters name="title" label="Title" />

                        <!-- Filter by Status -->
                        <x-status-select />

                        <!-- Filter by Priority -->
                        <x-priority-select />

                        <!-- Filter by Source -->
                        <x-source-select />

                        <!-- Filter by Category -->
                        <x-category-select />

                        <!-- Filter by Branch -->
                        <x-branch-select />

                        <!-- Filter by Region -->
                        <x-regions />

                        <!-- Filter by Division -->
                        <x-division />

                        <!-- Filter by Assigned To -->
                        <x-user-select name="assigned_to" label="Assigned To" :includeUnassigned="true" />

                        <!-- Filter by Assigned By -->
                        <x-user-select name="assigned_by" label="Assigned By" />

                        <!-- Filter by Resolved By -->
                        <x-user-select name="resolved_by" label="Resolved By" />

                        <!-- Filter by SLA Breached -->
                        <x-sla-breached-select />

                        <!-- Filter: Escalated -->
                        <x-escalated-select />

                        <!-- Filter: Harassment Only -->
                        <x-harassment-only-select />

                        <!-- Filter: Has Witnesses -->
                        <x-witnesses-select />

                        <!-- Filter: Confidential -->
                        <x-harassment-confidential-select />

                        <!-- Filter: Harassment Sub Category -->
                        <x-harassment-sub-category-select />

                        <!-- Filter by Complainant Name -->
                        <x-input-filters name="complainant_name" label="Complainant Name" />

                        <!-- Filter by Complainant Email -->
                        <x-input-filters name="complainant_email" label="Complainant Email" type="email" />

                        <!-- Filter by Date From -->
                        <x-date-from />

                        <!-- Filter by Date To -->
                        <x-date-to />

                        <!-- Filter by Assigned Date From -->
                        <div>
                            <label for="filter[assigned_date_from]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Date
                                From</label>
                            <input type="date" name="filter[assigned_date_from]" id="filter[assigned_date_from]"
                                value="{{ request('filter.assigned_date_from') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Filter by Assigned Date To -->
                        <div>
                            <label for="filter[assigned_date_to]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Date
                                To</label>
                            <input type="date" name="filter[assigned_date_to]" id="filter[assigned_date_to]"
                                value="{{ request('filter.assigned_date_to') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Filter by Resolved Date From -->
                        <div>
                            <label for="filter[resolved_date_from]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolved Date
                                From</label>
                            <input type="date" name="filter[resolved_date_from]" id="filter[resolved_date_from]"
                                value="{{ request('filter.resolved_date_from') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Filter by Resolved Date To -->
                        <div>
                            <label for="filter[resolved_date_to]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolved Date
                                To</label>
                            <input type="date" name="filter[resolved_date_to]" id="filter[resolved_date_to]"
                                value="{{ request('filter.resolved_date_to') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort
                                By</label>
                            <select name="sort" id="sort"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Default (Latest)</option>
                                <option value="id" {{ request('sort')==='id' ? 'selected' : '' }}>ID (Ascending)
                                </option>
                                <option value="-id" {{ request('sort')==='-id' ? 'selected' : '' }}>ID (Descending)
                                </option>
                                <option value="complaint_number" {{ request('sort')==='complaint_number' ? 'selected'
                                    : '' }}>Complaint Number (A-Z)</option>
                                <option value="-complaint_number" {{ request('sort')==='-complaint_number' ? 'selected'
                                    : '' }}>Complaint Number (Z-A)</option>
                                <option value="title" {{ request('sort')==='title' ? 'selected' : '' }}>Title (A-Z)
                                </option>
                                <option value="-title" {{ request('sort')==='-title' ? 'selected' : '' }}>Title
                                    (Z-A)
                                </option>
                                <option value="status" {{ request('sort')==='status' ? 'selected' : '' }}>Status
                                    (A-Z)
                                </option>
                                <option value="-status" {{ request('sort')==='-status' ? 'selected' : '' }}>Status
                                    (Z-A)
                                </option>
                                <option value="priority" {{ request('sort')==='priority' ? 'selected' : '' }}>
                                    Priority
                                    (A-Z)</option>
                                <option value="-priority" {{ request('sort')==='-priority' ? 'selected' : '' }}>
                                    Priority
                                    (Z-A)</option>
                                <option value="created_at" {{ request('sort')==='created_at' ? 'selected' : '' }}>
                                    Created (Oldest)</option>
                                <option value="-created_at" {{ request('sort')==='-created_at' ? 'selected' : '' }}>
                                    Created (Latest)</option>
                                <option value="updated_at" {{ request('sort')==='updated_at' ? 'selected' : '' }}>
                                    Updated (Oldest)</option>
                                <option value="-updated_at" {{ request('sort')==='-updated_at' ? 'selected' : '' }}>
                                    Updated (Latest)</option>
                                <option value="assigned_at" {{ request('sort')==='assigned_at' ? 'selected' : '' }}>
                                    Assigned (Oldest)</option>
                                <option value="-assigned_at" {{ request('sort')==='-assigned_at' ? 'selected' : '' }}>
                                    Assigned (Latest)</option>
                                <option value="resolved_at" {{ request('sort')==='resolved_at' ? 'selected' : '' }}>
                                    Resolved (Oldest)</option>
                                <option value="-resolved_at" {{ request('sort')==='-resolved_at' ? 'selected' : '' }}>
                                    Resolved (Latest)</option>
                                <option value="expected_resolution_date" {{ request('sort')==='expected_resolution_date'
                                    ? 'selected' : '' }}>Expected
                                    Resolution (Earliest)</option>
                                <option value="-expected_resolution_date" {{
                                    request('sort')==='-expected_resolution_date' ? 'selected' : '' }}>Expected
                                    Resolution (Latest)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit and Reset Buttons -->
                    <div class="mt-4 flex space-x-4">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Apply Filters
                        </button>
                        <a href="{{ route('complaints.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- STATISTICS DASHBOARD -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $statistics['open_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Open</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $statistics['resolved_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Resolved</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $statistics['overdue_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Overdue</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $statistics['high_priority'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">High Priority</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-700">{{ $statistics['critical_priority'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Critical</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $statistics['sla_breached'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">SLA Breach</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

            @if ($complaints->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="min-w-max w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-3 px-2 text-center">#</th>
                            <th class="py-3 px-2 text-left">Complaint Details</th>
                            <th class="py-3 px-2 text-center">Category</th>
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-2 text-center">Priority</th>
                            <th class="py-3 px-2 text-center">Assigned To</th>
                            <th class="py-3 px-2 text-center">Source</th>
                            <th class="py-3 px-2 text-center">Created</th>
                            <th class="py-3 px-2 text-center">SLA (DUE)</th>
                            <th class="py-3 px-2 text-center">Esc</th>
                            <th class="py-3 px-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-sm leading-normal">
                        @foreach ($complaints as $index => $complaint)
                        <tr
                            class="border-b border-gray-200 hover:bg-gray-50 {{ $complaint->isOverdue() ? 'bg-red-50' : '' }}">
                            <td class="py-3 px-2 text-center font-semibold">{{ $index + 1 }}</td>
                            <td class="py-3 px-2">
                                <div class="flex flex-col">
                                    <div class="font-semibold text-blue-600">
                                        <a href="{{ route('complaints.show', $complaint) }}" class="hover:underline">
                                            {{ $complaint->complaint_number }}
                                        </a>
                                    </div>
                                    <div class="text-gray-800 font-medium">{{ Str::limit($complaint->title, 40) }}
                                    </div>
                                    @if($complaint->complainant_name)
                                    <div class="text-gray-600 text-xs">
                                        <i class="fas fa-user mr-1"></i>{{ $complaint->complainant_name }}
                                    </div>
                                    @endif
                                    @if($complaint->branch)
                                    <div class="text-gray-600 text-xs">
                                        <i class="fas fa-building mr-1"></i>{{ $complaint->branch->name }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-2 text-center">
                                @if($complaint->category)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ Str::limit($complaint->category, 22) }}
                                </span>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @switch($complaint->status)
                                            @case('Open') bg-yellow-100 text-yellow-800 @break
                                            @case('In Progress') bg-blue-100 text-blue-800 @break
                                            @case('Pending') bg-orange-100 text-orange-800 @break
                                            @case('Resolved') bg-green-100 text-green-800 @break
                                            @case('Closed') bg-gray-100 text-gray-800 @break
                                            @case('Reopened') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                    {{ $complaint->status }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @switch($complaint->priority)
                                            @case('Low') bg-green-100 text-green-800 @break
                                            @case('Medium') bg-yellow-100 text-yellow-800 @break
                                            @case('High') bg-orange-100 text-orange-800 @break
                                            @case('Critical') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                    {{ $complaint->priority }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                @if($complaint->assignedTo)
                                <div class="text-sm font-medium">{{ $complaint->assignedTo->name }}</div>
                                @if($complaint->assigned_at)
                                <div class="text-xs text-gray-500">{{ $complaint->assigned_at->diffForHumans() }}
                                </div>
                                @endif
                                @else
                                <span class="text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $complaint->source }}</span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <div class="text-sm">{{ $complaint->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $complaint->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="py-3 px-2 text-center">
                                @if($complaint->sla_breached)
                                <span class="text-red-600 font-semibold text-xs">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Breached
                                </span>
                                @elseif($complaint->expected_resolution_date)
                                <div class="text-xs">
                                    <div
                                        class="{{ $complaint->expected_resolution_date->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $complaint->expected_resolution_date->format('d M Y') }}
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span
                                    class="text-xs font-semibold {{ ($complaint->metrics->escalation_count ?? $complaint->escalations->count()) > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ $complaint->metrics->escalation_count ?? $complaint->escalations->count() }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <a href="{{ route('complaints.show', $complaint) }}"
                                    class="inline-flex items-center px-3 py-1 text-white bg-blue-600 hover:bg-blue-700 rounded-md text-xs font-semibold transition-all duration-200"
                                    title="View Details">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50">
                    {{ $complaints->appends(request()->query())->links() }}
                </div>
            </div>
            @else
            <div class="p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No complaints found</h3>
                <p class="text-gray-600 mb-4">
                    @if(request()->hasAny(['filter']))
                    No complaints match your current filters.
                    @else
                    There are no complaints in the system yet.
                    @endif
                </p>
                <div class="space-x-4">
                    @if(request()->hasAny(['filter']))
                    <a href="{{ route('complaints.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                        Clear Filters
                    </a>
                    @endif
                    <a href="{{ route('complaints.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Create First Complaint
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('modals')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");

            // Add CSS for smooth transitions
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);

            function showFilters() {
                targetDiv.style.display = 'block';
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.opacity = '1';
                    targetDiv.style.transform = 'translateY(0)';
                }, 10);
            }

            function hideFilters() {
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.display = 'none';
                }, 300);
            }

            // Toggle filter visibility
            btn.addEventListener('click', function(event) {
                event.stopPropagation();
                if (targetDiv.style.display === "none" || !targetDiv.style.display) {
                    showFilters();
                } else {
                    hideFilters();
                }
            });

            // Hide filters when clicking outside
            document.addEventListener('click', function(event) {
                if (targetDiv.style.display === 'block' && 
                    !targetDiv.contains(event.target) && 
                    !btn.contains(event.target)) {
                    hideFilters();
                }
            });

            // Prevent clicks inside filter from closing it
            targetDiv.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        // Modal functions for description display
        function openModal(description) {
            const modal = document.getElementById('descriptionModal');
            if (modal) {
                document.getElementById('modalDescription').innerText = description;
                modal.classList.remove('hidden');
            }
        }

        function closeModal() {
            const modal = document.getElementById('descriptionModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Toggle description function (unified version)
        function toggleDescription(link) {
            const fullText = link.previousElementSibling;
            const previewText = fullText ? fullText.previousElementSibling : null;
            
            if (!fullText || !previewText) return;

            if (fullText.style.display !== "none") {
                fullText.style.display = "none";
                previewText.style.display = "block";
                link.innerText = "Read more";
            } else {
                fullText.style.display = "block";
                previewText.style.display = "none";
                link.innerText = "Read less";
            }
        }
    </script>
    @endpush
</x-app-layout>