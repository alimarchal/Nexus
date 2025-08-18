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
            <button id="toggle-bulk"
                class="inline-flex items-center ml-2 px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-800 active:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Bulk Actions
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
                        <div>
                            <label for="filter[id]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Complaint ID</label>
                            <input type="number" name="filter[id]" id="filter[id]" value="{{ request('filter.id') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter complaint ID">
                        </div>

                        <!-- Filter by Complaint Number -->
                        <div>
                            <label for="filter[complaint_number]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Complaint
                                Number</label>
                            <input type="text" name="filter[complaint_number]" id="filter[complaint_number]"
                                value="{{ request('filter.complaint_number') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter complaint number">
                        </div>

                        <!-- Filter by Title -->
                        <div>
                            <label for="filter[title]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <input type="text" name="filter[title]" id="filter[title]"
                                value="{{ request('filter.title') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter title">
                        </div>

                        <!-- Filter by Status -->
                        <div>
                            <label for="filter[status]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="filter[status]" id="filter[status]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="Open" {{ request('filter.status')==='Open' ? 'selected' : '' }}>Open
                                </option>
                                <option value="In Progress" {{ request('filter.status')==='In Progress' ? 'selected'
                                    : '' }}>In
                                    Progress</option>
                                <option value="Pending" {{ request('filter.status')==='Pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="Resolved" {{ request('filter.status')==='Resolved' ? 'selected' : '' }}>
                                    Resolved
                                </option>
                                <option value="Closed" {{ request('filter.status')==='Closed' ? 'selected' : '' }}>
                                    Closed
                                </option>
                                <option value="Reopened" {{ request('filter.status')==='Reopened' ? 'selected' : '' }}>
                                    Reopened
                                </option>
                            </select>
                        </div>

                        <!-- Filter by Priority -->
                        <div>
                            <label for="filter[priority]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select name="filter[priority]" id="filter[priority]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Priorities</option>
                                <option value="Low" {{ request('filter.priority')==='Low' ? 'selected' : '' }}>Low
                                </option>
                                <option value="Medium" {{ request('filter.priority')==='Medium' ? 'selected' : '' }}>
                                    Medium
                                </option>
                                <option value="High" {{ request('filter.priority')==='High' ? 'selected' : '' }}>High
                                </option>
                                <option value="Critical" {{ request('filter.priority')==='Critical' ? 'selected' : ''
                                    }}>
                                    Critical</option>
                            </select>
                        </div>

                        <!-- Filter by Source -->
                        <div>
                            <label for="filter[source]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source</label>
                            <select name="filter[source]" id="filter[source]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Sources</option>
                                <option value="Email" {{ request('filter.source')==='Email' ? 'selected' : '' }}>Email
                                </option>
                                <option value="Phone" {{ request('filter.source')==='Phone' ? 'selected' : '' }}>Phone
                                </option>
                                <option value="Website" {{ request('filter.source')==='Website' ? 'selected' : '' }}>
                                    Website</option>
                                <option value="Walk-in" {{ request('filter.source')==='Walk-in' ? 'selected' : '' }}>
                                    Walk-in</option>
                                <option value="Social Media" {{ request('filter.source')==='Social Media' ? 'selected'
                                    : '' }}>Social Media</option>
                            </select>
                        </div>

                        <!-- Filter by Category -->
                        <div>
                            <label for="filter[category]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <input type="text" name="filter[category]" id="filter[category]"
                                value="{{ request('filter.category') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter category">
                        </div>

                        <!-- Filter by Branch -->
                        <div>
                            <label for="filter[branch_id]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                            <select name="filter[branch_id]" id="filter[branch_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Branches</option>
                                @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ request('filter.branch_id')==$branch->id ?
                                    'selected' : ''
                                    }}>
                                    {{ $branch->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Assigned To -->
                        <div>
                            <label for="filter[assigned_to]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned To</label>
                            <select name="filter[assigned_to]" id="filter[assigned_to]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Users</option>
                                <option value="unassigned" {{ request('filter.assigned_to')==='unassigned' ? 'selected'
                                    : '' }}>Unassigned</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('filter.assigned_to')==$user->id ? 'selected'
                                    : ''
                                    }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Assigned By -->
                        <div>
                            <label for="filter[assigned_by]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned By</label>
                            <select name="filter[assigned_by]" id="filter[assigned_by]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Users</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('filter.assigned_by')==$user->id ? 'selected'
                                    : ''
                                    }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Resolved By -->
                        <div>
                            <label for="filter[resolved_by]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolved By</label>
                            <select name="filter[resolved_by]" id="filter[resolved_by]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Users</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('filter.resolved_by')==$user->id ? 'selected'
                                    : ''
                                    }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by SLA Breached -->
                        <div>
                            <label for="filter[sla_breached]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">SLA Status</label>
                            <select name="filter[sla_breached]" id="filter[sla_breached]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All SLA Status</option>
                                <option value="1" {{ request('filter.sla_breached')==='1' ? 'selected' : '' }}>SLA
                                    Breached</option>
                                <option value="0" {{ request('filter.sla_breached')==='0' ? 'selected' : '' }}>Within
                                    SLA</option>
                            </select>
                        </div>

                        <!-- Filter by Complainant Name -->
                        <div>
                            <label for="filter[complainant_name]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Complainant
                                Name</label>
                            <input type="text" name="filter[complainant_name]" id="filter[complainant_name]"
                                value="{{ request('filter.complainant_name') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter complainant name">
                        </div>

                        <!-- Filter by Complainant Email -->
                        <div>
                            <label for="filter[complainant_email]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Complainant
                                Email</label>
                            <input type="email" name="filter[complainant_email]" id="filter[complainant_email]"
                                value="{{ request('filter.complainant_email') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter complainant email">
                        </div>

                        <!-- Filter by Date From -->
                        <div>
                            <label for="filter[date_from]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created Date
                                From</label>
                            <input type="date" name="filter[date_from]" id="filter[date_from]"
                                value="{{ request('filter.date_from') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Filter by Date To -->
                        <div>
                            <label for="filter[date_to]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created Date
                                To</label>
                            <input type="date" name="filter[date_to]" id="filter[date_to]"
                                value="{{ request('filter.date_to') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

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
                                <option value="-title" {{ request('sort')==='-title' ? 'selected' : '' }}>Title (Z-A)
                                </option>
                                <option value="status" {{ request('sort')==='status' ? 'selected' : '' }}>Status (A-Z)
                                </option>
                                <option value="-status" {{ request('sort')==='-status' ? 'selected' : '' }}>Status (Z-A)
                                </option>
                                <option value="priority" {{ request('sort')==='priority' ? 'selected' : '' }}>Priority
                                    (A-Z)</option>
                                <option value="-priority" {{ request('sort')==='-priority' ? 'selected' : '' }}>Priority
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
                                    ? 'selected' : '' }}>Expected Resolution (Earliest)</option>
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $statistics['open_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Open</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $statistics['resolved_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Resolved</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $statistics['overdue_complaints'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Overdue</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $statistics['high_priority'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">High Priority</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-700">{{ $statistics['critical_priority'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Critical</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $statistics['sla_breached'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">SLA Breach</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BULK ACTIONS SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-4" id="bulk-actions"
            style="display: none">
            <div class="p-6">
                <form method="POST" action="{{ route('complaints.bulk-update') }}" id="bulk-form">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="operation_type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operation
                                Type</label>
                            <select name="operation_type" id="operation_type" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Operation</option>
                                <option value="status_update">Update Status</option>
                                <option value="assignment">Assign to User</option>
                                <option value="priority_change">Change Priority</option>
                                <option value="branch_transfer">Transfer Branch</option>
                                <option value="bulk_comment">Add Comment</option>
                                <option value="bulk_delete">Delete Complaints</option>
                            </select>
                        </div>
                        <div id="operation-fields" class="md:col-span-2">
                            <!-- Dynamic fields will be inserted here based on operation type -->
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-sm text-gray-600 mb-2">
                            Selected complaints: <span id="selected-count">0</span>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            id="bulk-submit" disabled>
                            Execute Bulk Operation
                        </button>
                    </div>
                    <!-- Hidden input to store selected complaint IDs -->
                    <input type="hidden" name="complaint_ids" id="bulk-complaint-ids" value="">
                </form>
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
                            <th class="py-3 px-2 text-center">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300">
                            </th>
                            <th class="py-3 px-2 text-center">#</th>
                            <th class="py-3 px-2 text-left">Complaint Details</th>
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-2 text-center">Priority</th>
                            <th class="py-3 px-2 text-center">Assigned To</th>
                            <th class="py-3 px-2 text-center">Source</th>
                            <th class="py-3 px-2 text-center">Created</th>
                            <th class="py-3 px-2 text-center">SLA</th>
                            <th class="py-3 px-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-sm leading-normal">
                        @foreach ($complaints as $index => $complaint)
                        <tr
                            class="border-b border-gray-200 hover:bg-gray-50 {{ $complaint->isOverdue() ? 'bg-red-50' : '' }}">
                            <td class="py-3 px-2 text-center">
                                <input type="checkbox" name="complaint_ids[]" value="{{ $complaint->id }}"
                                    class="complaint-checkbox rounded border-gray-300">
                            </td>
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
                                <div class="text-sm">{{ $complaint->created_at->format('M d, Y') }}</div>
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
                                    <div class="text-gray-600">Due:</div>
                                    <div
                                        class="{{ $complaint->expected_resolution_date->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $complaint->expected_resolution_date->format('M d') }}
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">
                                <div class="flex justify-center space-x-1">
                                    <a href="{{ route('complaints.show', $complaint) }}"
                                        class="p-2 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition-all duration-300 hover:scale-110"
                                        title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('complaints.edit', $complaint) }}"
                                        class="p-2 text-yellow-600 hover:text-white hover:bg-yellow-600 rounded-full transition-all duration-300 hover:scale-110"
                                        title="Edit Complaint">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                    @if($complaint->attachments->count() > 0)
                                    <span class="p-2 text-green-600"
                                        title="{{ $complaint->attachments->count() }} Attachments">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                        </svg>
                                    </span>
                                    @endif
                                    @if($complaint->comments->count() > 0)
                                    <span class="p-2 text-purple-600"
                                        title="{{ $complaint->comments->count() }} Comments">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                        </svg>
                                    </span>
                                    @endif
                                    <button type="button"
                                        class="delete-button p-2 text-red-600 hover:text-white hover:bg-red-600 rounded-full transition-all duration-300 hover:scale-110"
                                        title="Delete Complaint">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                    <form class="delete-form" method="POST"
                                        action="{{ route('complaints.destroy', $complaint) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
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
    // Bulk actions toggle
    const bulkActionsDiv = document.getElementById("bulk-actions");
    const bulkToggleBtn = document.getElementById("toggle-bulk");
    const operationTypeSelect = document.getElementById("operation_type");
    const operationFields = document.getElementById("operation-fields");
    const bulkSubmit = document.getElementById("bulk-submit");
    const selectedCount = document.getElementById("selected-count");
    const bulkComplaintIds = document.getElementById("bulk-complaint-ids");

    // Show/hide bulk actions
    function showBulkActions() {
        bulkActionsDiv.style.display = 'block';
        bulkActionsDiv.style.opacity = '0';
        bulkActionsDiv.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            bulkActionsDiv.style.opacity = '1';
            bulkActionsDiv.style.transform = 'translateY(0)';
        }, 10);
    }

    function hideBulkActions() {
        bulkActionsDiv.style.opacity = '0';
        bulkActionsDiv.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            bulkActionsDiv.style.display = 'none';
        }, 300);
    }

    bulkToggleBtn.onclick = function(event) {
        event.stopPropagation();
        if (bulkActionsDiv.style.display === "none") {
            showBulkActions();
        } else {
            hideBulkActions();
        }
    };

    // Hide bulk actions when clicking outside
    document.addEventListener('click', function(event) {
        if (bulkActionsDiv.style.display === 'block' && !bulkActionsDiv.contains(event.target) && event.target !== bulkToggleBtn) {
            hideBulkActions();
        }
    });

    // Prevent clicks inside bulk actions from closing it
    bulkActionsDiv.addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Select all functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const complaintCheckboxes = document.querySelectorAll('.complaint-checkbox');

    selectAllCheckbox.addEventListener('change', function() {
        complaintCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Individual checkbox functionality
    complaintCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.complaint-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === complaintCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < complaintCheckboxes.length;
            updateSelectedCount();
        });
    });

    // Update selected count
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.complaint-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCount.textContent = count;
        
        // Update hidden input with selected IDs
        const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
        bulkComplaintIds.value = selectedIds.join(',');
        
        // Enable/disable submit button
        bulkSubmit.disabled = count === 0 || !operationTypeSelect.value;
    }

    // Operation type change handler
    operationTypeSelect.addEventListener('change', function() {
        const operation = this.value;
        operationFields.innerHTML = '';

        switch(operation) {
            case 'status_update':
                operationFields.innerHTML = `
                    <div>
                        <label for="new_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Status</label>
                        <select name="new_status" id="new_status" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Status</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending">Pending</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                            <option value="Reopened">Reopened</option>
                        </select>
                    </div>
                `;
                break;
            
            case 'assignment':
                operationFields.innerHTML = `
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                        <select name="assigned_to" id="assigned_to" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select User</option>
                            @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
                break;
            
            case 'priority_change':
                operationFields.innerHTML = `
                    <div>
                        <label for="new_priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Priority</label>
                        <select name="new_priority" id="new_priority" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                `;
                break;
            
            case 'branch_transfer':
                operationFields.innerHTML = `
                    <div>
                        <label for="new_branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transfer to Branch</label>
                        <select name="new_branch" id="new_branch" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Branch</option>
                            @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
                break;
            
            case 'bulk_comment':
                operationFields.innerHTML = `
                    <div>
                        <label for="comment_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comment</label>
                        <textarea name="comment_text" id="comment_text" rows="3" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Enter comment to add to selected complaints"></textarea>
                    </div>
                `;
                break;
            
            case 'bulk_delete':
                operationFields.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Warning</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>This action will permanently delete the selected complaints. This cannot be undone.</p>
                                </div>
                                <div class="mt-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="confirm_delete" id="confirm_delete" required class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                        <label for="confirm_delete" class="ml-2 block text-sm text-red-800">
                                            I understand this action cannot be undone
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
        }

        // Update submit button state
        updateSelectedCount();
    });

    // Form submission with confirmation
    document.getElementById('bulk-form').addEventListener('submit', function(e) {
        const operation = operationTypeSelect.value;
        const selectedCount = document.querySelectorAll('.complaint-checkbox:checked').length;
        
        if (selectedCount === 0) {
            e.preventDefault();
            alert('Please select at least one complaint.');
            return;
        }

        let confirmMessage = '';
        switch(operation) {
            case 'bulk_delete':
                confirmMessage = `Are you sure you want to delete ${selectedCount} complaint(s)? This action cannot be undone.`;
                break;
            default:
                confirmMessage = `Are you sure you want to perform this operation on ${selectedCount} complaint(s)?`;
        }

        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });

    // Add smooth transitions
    const style = document.createElement('style');
    style.textContent = `
        #bulk-actions {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
    `;
    document.head.appendChild(style);
});
    </script>
    <script>
        const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");

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

            btn.onclick = function(event) {
                event.stopPropagation();
                if (targetDiv.style.display === "none") {
                    showFilters();
                } else {
                    hideFilters();
                }
            };

            // Hide filters when clicking outside
            document.addEventListener('click', function(event) {
                if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn) {
                    hideFilters();
                }
            });
            // Function to open the modal and show the full description
            function openModal(description) {
                // Set the description content in the modal
                document.getElementById('modalDescription').innerText = description;

                // Show the modal
                document.getElementById('descriptionModal').classList.remove('hidden');
            }

            // Function to close the modal
            function closeModal() {
                // Hide the modal
                document.getElementById('descriptionModal').classList.add('hidden');
            }


            // Prevent clicks inside the filter from closing it
            targetDiv.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            // Add CSS for smooth transitions
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
    </script>
    <script>
        function toggleDescription(link) {
                var preview = link.previousElementSibling.previousElementSibling;
                var fullDescription = link.previousElementSibling;

                preview.style.display = 'none';
                fullDescription.style.display = 'inline';
                link.style.display = 'none';
            }
    </script>
    <script>
        function toggleDescription(link) {
                const fullText = link.previousElementSibling; // Get the full description span
                const previewText = fullText.previousElementSibling; // Get the preview text span

                // Toggle the visibility of the full text and preview text
                if (fullText.style.display !== "none") {
                    fullText.style.display = "none"; // Hide full text
                    previewText.style.display = "block"; // Show preview text
                    link.innerText = "Read more"; // Change link text
                } else {
                    fullText.style.display = "block"; // Show full text
                    previewText.style.display = "none"; // Hide preview text
                    link.innerText = "Read less"; // Change link text
                }
            }
    </script>
    @endpush
</x-app-layout>