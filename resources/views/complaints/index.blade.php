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

                        <!-- Filter by Region -->
                        <div>
                            <label for="filter[region_id]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                            <select name="filter[region_id]" id="filter[region_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Regions</option>
                                @foreach($regions ?? [] as $region)
                                <option value="{{ $region->id }}" {{ request('filter.region_id')==$region->id ?
                                    'selected' : '' }}>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Division -->
                        <div>
                            <label for="filter[division_id]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                            <select name="filter[division_id]" id="filter[division_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Divisions</option>
                                @foreach($divisions ?? [] as $division)
                                <option value="{{ $division->id }}" {{ request('filter.division_id')==$division->id ?
                                    'selected' : '' }}>{{ $division->short_name ?? $division->name }}</option>
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

                        <!-- Filter: Escalated -->
                        <div>
                            <label for="filter[escalated]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Escalated</label>
                            <select name="filter[escalated]" id="filter[escalated]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.escalated')==='1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ request('filter.escalated')==='0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <!-- Filter: Harassment Only -->
                        <div>
                            <label for="filter[harassment_only]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harassment</label>
                            <select name="filter[harassment_only]" id="filter[harassment_only]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.harassment_only')==='1' ? 'selected' : '' }}>Only
                                    Harassment</option>
                            </select>
                        </div>

                        <!-- Filter: Has Witnesses -->
                        <div>
                            <label for="filter[has_witnesses]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Witnesses</label>
                            <select name="filter[has_witnesses]" id="filter[has_witnesses]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.has_witnesses')==='1' ? 'selected' : '' }}>Has
                                </option>
                                <option value="0" {{ request('filter.has_witnesses')==='0' ? 'selected' : '' }}>None
                                </option>
                            </select>
                        </div>

                        <!-- Filter: Confidential -->
                        <div>
                            <label for="filter[harassment_confidential]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confidential</label>
                            <select name="filter[harassment_confidential]" id="filter[harassment_confidential]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.harassment_confidential')==='1' ? 'selected' : ''
                                    }}>Yes</option>
                                <option value="0" {{ request('filter.harassment_confidential')==='0' ? 'selected' : ''
                                    }}>No</option>
                            </select>
                        </div>

                        <!-- Filter: Harassment Sub Category -->
                        <div>
                            <label for="filter[harassment_sub_category]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harassment Sub
                                Category</label>
                            <input type="text" name="filter[harassment_sub_category]"
                                id="filter[harassment_sub_category]"
                                value="{{ request('filter.harassment_sub_category') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="e.g. Verbal" />
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
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-2 text-center">Priority</th>
                            <th class="py-3 px-2 text-center">Assigned To</th>
                            <th class="py-3 px-2 text-center">Source</th>
                            <th class="py-3 px-2 text-center">Created</th>
                            <th class="py-3 px-2 text-center">SLA</th>
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
    <!-- Bulk selection scripts removed -->

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

    <!-- Action button scripts removed -->
    @endpush
</x-app-layout>