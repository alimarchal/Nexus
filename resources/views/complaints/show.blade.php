<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Complaint Details
                </h2>
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
                @switch($complaint->status)
                    @case('Open') bg-yellow-100 text-yellow-800 border border-yellow-200 @break
                    @case('In Progress') bg-blue-100 text-blue-800 border border-blue-200 @break
                    @case('Pending') bg-orange-100 text-orange-800 border border-orange-200 @break
                    @case('Resolved') bg-green-100 text-green-800 border border-green-200 @break
                    @case('Closed') bg-gray-100 text-gray-800 border border-gray-200 @break
                    @case('Reopened') bg-red-100 text-red-800 border border-red-200 @break
                    @default bg-gray-100 text-gray-800 border border-gray-200
                @endswitch">
                    {{ $complaint->status }}
                </span>
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
                @switch($complaint->priority)
                    @case('Low') bg-green-100 text-green-800 border border-green-200 @break
                    @case('Medium') bg-yellow-100 text-yellow-800 border border-yellow-200 @break
                    @case('High') bg-orange-100 text-orange-800 border border-orange-200 @break
                    @case('Critical') bg-red-100 text-red-800 border border-red-200 @break
                    @default bg-gray-100 text-gray-800 border border-gray-200
                @endswitch">
                    {{ $complaint->priority }} Priority
                </span>
                @if($complaint->sla_breached)
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5C3.312 16.333 4.275 18 5.814 18z" />
                    </svg>
                    SLA Breached
                </span>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
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
            <x-status-message />

            <!-- Main Complaint Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $complaint->title }}</h3>
                                <p class="text-blue-100">{{ $complaint->complaint_number }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm opacity-90">Created</div>
                            <div class="text-white text-lg font-bold">{{ $complaint->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-blue-100 text-sm">{{ $complaint->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Information -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Description -->
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Description</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                    <p
                                        class="text-gray-700 leading-relaxed whitespace-pre-wrap break-all break-words overflow-hidden">
                                        {{
                                        $complaint->description }}</p>
                                </div>
                            </div>

                            <!-- Complainant Information -->
                            @if($complaint->complainant_name || $complaint->complainant_email ||
                            $complaint->complainant_phone)
                            <div
                                class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Complainant Information</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($complaint->complainant_name)
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Name</label>
                                        <p class="text-gray-900 font-semibold">{{ $complaint->complainant_name }}</p>
                                    </div>
                                    @endif
                                    @if($complaint->complainant_email)
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Email</label>
                                        <p class="text-gray-900 font-semibold">
                                            <a href="mailto:{{ $complaint->complainant_email }}"
                                                class="text-blue-600 hover:underline">
                                                {{ $complaint->complainant_email }}
                                            </a>
                                        </p>
                                    </div>
                                    @endif
                                    @if($complaint->complainant_phone)
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Phone</label>
                                        <p class="text-gray-900 font-semibold">
                                            <a href="tel:{{ $complaint->complainant_phone }}"
                                                class="text-blue-600 hover:underline">
                                                {{ $complaint->complainant_phone }}
                                            </a>
                                        </p>
                                    </div>
                                    @endif
                                    @if($complaint->complainant_account_number)
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Account
                                            Number</label>
                                        <p class="text-gray-900 font-mono font-semibold bg-gray-50 px-2 py-1 rounded">
                                            {{ $complaint->complainant_account_number }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Resolution -->
                            @if($complaint->resolution)
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Resolution</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{
                                        $complaint->resolution }}</p>
                                    @if($complaint->resolved_by && $complaint->resolved_at)
                                    <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                                        <div class="text-sm text-gray-600">
                                            Resolved by: <span class="font-medium">{{ $complaint->resolvedBy->name
                                                }}</span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $complaint->resolved_at->format('M d, Y \a\t H:i') }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Sidebar Information -->
                        <div class="space-y-6">
                            <!-- Quick Info -->
                            <div
                                class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Quick Info</h4>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Source</span>
                                        <span class="text-sm text-gray-800 bg-gray-100 px-2 py-1 rounded">{{
                                            $complaint->source }}</span>
                                    </div>
                                    @if($complaint->category)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Category</span>
                                        <span class="text-sm text-gray-800">{{ $complaint->category }}</span>
                                    </div>
                                    @endif
                                    @if($complaint->branch)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Branch</span>
                                        <span class="text-sm text-gray-800">{{ $complaint->branch->name }}</span>
                                    </div>
                                    @endif
                                    @if($complaint->region)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Region</span>
                                        <span class="text-sm text-gray-800">{{ $complaint->region->name }}</span>
                                    </div>
                                    @endif
                                    @if($complaint->division)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Division</span>
                                        <span class="text-sm text-gray-800">{{ $complaint->division->short_name ??
                                            $complaint->division->name }}</span>
                                    </div>
                                    @endif
                                    <div class="grid grid-cols-3 gap-2 pt-2">
                                        <div
                                            class="bg-white rounded-lg p-2 text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase tracking-wide">Same Branch
                                            </div>
                                            <div class="text-sm font-semibold text-indigo-600">
                                                {{ \App\Models\Complaint::where('branch_id',
                                                $complaint->branch_id)->count() }}
                                            </div>
                                        </div>
                                        <div
                                            class="bg-white rounded-lg p-2 text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase tracking-wide">Same Region
                                            </div>
                                            <div class="text-sm font-semibold text-indigo-600">
                                                {{ $complaint->region_id ? \App\Models\Complaint::where('region_id',
                                                $complaint->region_id)->count() : 0 }}
                                            </div>
                                        </div>
                                        <div
                                            class="bg-white rounded-lg p-2 text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase tracking-wide">Same Division
                                            </div>
                                            <div class="text-sm font-semibold text-indigo-600">
                                                {{ $complaint->division_id ? \App\Models\Complaint::where('division_id',
                                                $complaint->division_id)->count() : 0 }}
                                            </div>
                                        </div>
                                    </div>
                                    @if($complaint->expected_resolution_date)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Expected Resolution</span>
                                        <span
                                            class="text-sm {{ $complaint->expected_resolution_date->isPast() && !$complaint->isResolved() ? 'text-red-600 font-semibold' : 'text-gray-800' }}">
                                            {{ $complaint->expected_resolution_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Assignment Info -->
                            <div
                                class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border border-blue-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Assignment</h4>
                                </div>
                                <div class="space-y-3">
                                    @if($complaint->assignedTo)
                                    <div class="p-3 bg-white rounded-lg shadow-sm border-l-4 border-blue-400">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">Assigned To</span>
                                                <p class="text-lg font-bold text-blue-600">{{
                                                    $complaint->assignedTo->name }}</p>
                                            </div>
                                            <div class="p-2 bg-blue-100 rounded-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                        @if($complaint->assigned_at)
                                        <div class="mt-2 text-xs text-gray-500">
                                            Assigned {{ $complaint->assigned_at->diffForHumans() }}
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mr-2"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5C3.312 16.333 4.275 18 5.814 18z" />
                                            </svg>
                                            <span class="text-sm font-medium text-yellow-800">Unassigned</span>
                                        </div>
                                    </div>
                                    @endif

                                    @if($complaint->assignedBy)
                                    <div class="p-3 bg-white rounded-lg shadow-sm">
                                        <div class="flex justify-between">
                                            <span class="text-sm font-medium text-gray-600">Assigned By</span>
                                            <span class="text-sm text-gray-800">{{ $complaint->assignedBy->name
                                                }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Metrics -->
                            @if($complaint->metrics)
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Metrics</h4>
                                </div>
                                <div class="space-y-3">
                                    @if($complaint->metrics->time_to_first_response)
                                    <div
                                        class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border-l-4 border-green-400">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">First Response</span>
                                            <p class="text-lg font-bold text-green-600">{{
                                                $complaint->metrics->formatted_response_time }}</p>
                                        </div>
                                    </div>
                                    @endif
                                    @if($complaint->metrics->time_to_resolution)
                                    <div
                                        class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border-l-4 border-blue-400">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Resolution Time</span>
                                            <p class="text-lg font-bold text-blue-600">{{
                                                $complaint->metrics->formatted_resolution_time }}</p>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="grid grid-cols-3 gap-3">
                                        <div class="p-3 bg-white rounded-lg shadow-sm text-center">
                                            <div class="text-lg font-bold text-purple-600">{{
                                                $complaint->metrics->escalation_count }}</div>
                                            <div class="text-xs text-gray-600">Escalations</div>
                                        </div>
                                        <div class="p-3 bg-white rounded-lg shadow-sm text-center">
                                            <div class="text-lg font-bold text-orange-600">{{
                                                $complaint->metrics->assignment_count }}</div>
                                            <div class="text-xs text-gray-600">Assignments</div>
                                        </div>
                                        <div class="p-3 bg-white rounded-lg shadow-sm text-center">
                                            <div class="text-lg font-bold text-red-600">{{
                                                $complaint->metrics->reopened_count }}</div>
                                            <div class="text-xs text-gray-600">Reopened</div>
                                        </div>
                                    </div>
                                    @if($complaint->metrics->customer_satisfaction_score)
                                    <div class="p-3 bg-white rounded-lg shadow-sm border-l-4 border-yellow-400">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-600">Customer Satisfaction</span>
                                            <div class="flex items-center">
                                                <span class="text-lg font-bold text-yellow-600">{{
                                                    $complaint->metrics->customer_satisfaction_score }}/5</span>
                                                <div class="ml-2 flex">
                                                    @for($i = 1; $i <= 5; $i++) <svg
                                                        class="w-4 h-4 {{ $i <= $complaint->metrics->customer_satisfaction_score ? 'text-yellow-400' : 'text-gray-300' }}"
                                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                        </svg>
                                                        @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Watchers -->
                            @if($complaint->watchers->count() > 0)
                            <div
                                class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Watchers</h4>
                                </div>
                                <div class="space-y-2">
                                    @foreach($complaint->watchers as $watcher)
                                    <div class="flex items-center p-2 bg-white rounded-lg shadow-sm">
                                        <div
                                            class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-purple-600 font-semibold text-sm">{{
                                                substr($watcher->user->name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">{{ $watcher->user->name
                                            }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            class="tab-button border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600"
                            data-tab="history">
                            History & Timeline ({{ $complaint->histories->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="comments">
                            Comments ({{ $complaint->comments->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="attachments">
                            Attachments ({{ $complaint->attachments->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="escalations">
                            Escalations ({{ $complaint->escalations->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="assignments">
                            Assignments ({{ $complaint->assignments->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="watchers">
                            Watchers ({{ $complaint->watchers->count() }})
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="satisfaction">
                            Satisfaction
                        </button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="operations">
                            Operations
                        </button>
                    </nav>
                </div>

                <!-- History Tab -->
                <div id="history-tab" class="tab-content p-6">
                    <div class="relative">
                        <div
                            class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-indigo-300 via-gray-200 to-transparent pointer-events-none">
                        </div>
                        <ul class="space-y-6">
                            @forelse($complaint->histories as $history)
                            <li class="relative pl-12 group">
                                <span class="absolute left-0 flex items-center justify-center w-8 h-8 rounded-full ring-4 ring-white dark:ring-gray-800 shadow-sm
                                    @switch($history->action_type)
                                        @case('Created') bg-blue-600 text-white @break
                                        @case('Assigned') @case('Reassigned') bg-green-600 text-white @break
                                        @case('Status Changed') bg-yellow-500 text-white @break
                                        @case('Resolved') bg-emerald-600 text-white @break
                                        @case('Escalated') bg-red-600 text-white @break
                                        @case('Priority Changed') bg-orange-500 text-white @break
                                        @case('Branch Transfer') bg-teal-600 text-white @break
                                        @case('Region Transfer') bg-indigo-600 text-white @break
                                        @case('Division Transfer') bg-pink-600 text-white @break
                                        @default bg-gray-500 text-white
                                    @endswitch">
                                    @switch($history->action_type)
                                    @case('Created')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v12m6-6H6" />
                                    </svg>
                                    @break
                                    @case('Assigned')
                                    @case('Reassigned')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    @break
                                    @case('Resolved')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    @break
                                    @case('Branch Transfer')
                                    @case('Region Transfer')
                                    @case('Division Transfer')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h10M7 12h10M7 17h10" />
                                    </svg>
                                    @break
                                    @case('Escalated')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    @break
                                    @default
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endswitch
                                </span>
                                <div
                                    class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{
                                            $history->action_type }}</h4>
                                        <time class="text-xs text-gray-500">{{ $history->performed_at->format('M d, Y
                                            H:i') }}</time>
                                    </div>
                                    @if($history->old_value || $history->new_value)
                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                        @if($history->old_value && $history->new_value)
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{
                                            $history->old_value }}</span>
                                        <span class="mx-1 text-gray-400">→</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $history->new_value
                                            }}</span>
                                        @elseif($history->new_value)
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $history->new_value
                                            }}</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($history->comments)
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-snug">{{
                                        $history->comments }}</p>
                                    @endif
                                    <div
                                        class="mt-3 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                        <span>by {{ $history->performedBy->name }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">#{{
                                            $history->id }}</span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-10">
                                <div class="inline-flex flex-col items-center text-gray-500">
                                    <svg class="h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-3 text-sm">No history records found</p>
                                </div>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Comments Tab -->
                <div id="comments-tab" class="tab-content p-6" style="display: none;">
                    <!-- Add Comment Form -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Add Comment</h4>
                        <form method="POST" action="{{ route('complaints.add-comment', $complaint) }}">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <textarea name="comment_text" rows="3" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                                        placeholder="Enter your comment..."></textarea>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <select name="comment_type" required
                                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="Internal">Internal</option>
                                            <option value="Customer">Customer</option>
                                            <option value="System">System</option>
                                        </select>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_private" value="1"
                                                class="rounded border-gray-300 text-indigo-600">
                                            <span class="ml-2 text-sm text-gray-700">Private</span>
                                        </label>
                                    </div>
                                    <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Add Comment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Comments List -->
                    <div class="space-y-4">
                        @forelse($complaint->comments as $comment)
                        <div
                            class="p-4 border border-gray-200 rounded-lg {{ $comment->is_private ? 'bg-yellow-50 border-yellow-200' : 'bg-white' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <span class="text-gray-600 font-semibold text-sm">{{
                                            substr($comment->creator->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900">{{ $comment->creator->name }}</span>
                                            <span class="px-2 py-1 text-xs rounded-full
                                                        @switch($comment->comment_type)
                                                            @case('Internal') bg-blue-100 text-blue-800 @break
                                                            @case('Customer') bg-green-100 text-green-800 @break
                                                            @case('System') bg-gray-100 text-gray-800 @break
                                                        @endswitch">
                                                {{ $comment->comment_type }}
                                            </span>
                                            @if($comment->is_private)
                                            <span
                                                class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Private</span>
                                            @endif
                                        </div>
                                        <time class="text-xs text-gray-500">{{ $comment->created_at->format('M d, Y
                                            H:i') }}</time>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $comment->comment_text }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="mt-2">No comments yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Attachments Tab -->
                <div id="attachments-tab" class="tab-content p-6" style="display: none;">
                    <div class="space-y-6">
                        <!-- Upload New Attachments -->
                        <div class="p-4 border border-indigo-200 rounded-lg bg-indigo-50/60">
                            <h4 class="text-sm font-semibold text-gray-800 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Add / Upload Attachments
                            </h4>
                            <p class="text-xs text-gray-600 mb-3">Attach additional supporting files (screenshots,
                                documents, logs). Files are stored securely and appear in the list below after upload.
                                You can select multiple files at once.</p>
                            <form method="POST" action="{{ route('complaints.add-attachments', $complaint) }}"
                                enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Choose Files</label>
                                    <input type="file" name="attachments[]" multiple
                                        class="w-full text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-200" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-gray-500">Accepted any type. Large files may take
                                        longer to process.</span>
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded shadow-sm flex items-center">
                                        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- Existing Attachments List -->
                        @forelse($complaint->attachments as $attachment)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $attachment->file_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $attachment->formatted_file_size }} •
                                        Uploaded {{ $attachment->created_at->format('M d, Y') }} by {{
                                        $attachment->creator->name }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('complaints.download-attachment', $attachment) }}"
                                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Download
                                </a>
                                <form method="POST" action="{{ route('complaints.delete-attachment', $attachment) }}"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Are you sure you want to delete this attachment?')"
                                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <p class="mt-2">No attachments</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Escalations Tab -->
                <div id="escalations-tab" class="tab-content p-6" style="display: none;">
                    <div class="space-y-4">
                        @forelse($complaint->escalations as $escalation)
                        <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">L{{ $escalation->escalation_level
                                            }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            Escalated from {{ $escalation->escalatedFrom->name }} to {{
                                            $escalation->escalatedTo->name }}
                                        </div>
                                        <time class="text-sm text-gray-500">{{ $escalation->escalated_at->format('M d, Y
                                            H:i') }}</time>
                                    </div>
                                </div>
                                @if($escalation->resolved_at)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Resolved</span>
                                @else
                                <span
                                    class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                @endif
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-700">{{ $escalation->escalation_reason }}</p>
                                @if($escalation->resolved_at)
                                <div class="mt-2 text-xs text-gray-500">
                                    Resolved on {{ $escalation->resolved_at->format('M d, Y H:i') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <p class="mt-2">No escalations</p>
                        </div>
                        @endforelse
                        <!-- Inline Escalation Form -->
                        <div class="mt-6 p-4 border border-red-200 rounded-lg bg-white">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">New Escalation</h4>
                            <form method="POST" action="{{ route('complaints.escalate', $complaint) }}">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Escalate To</label>
                                        <select name="escalated_to" class="w-full border-gray-300 rounded-md" required>
                                            <option value="">Select user</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                                        <select name="escalation_level" class="w-full border-gray-300 rounded-md"
                                            required>
                                            @for($i=1;$i<=5;$i++) <option value="{{ $i }}">Level {{ $i }}</option>
                                                @endfor
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                                        <textarea name="escalation_reason" rows="2"
                                            class="w-full border-gray-300 rounded-md" required></textarea>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-3">
                                    <button type="submit"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm">Escalate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div id="assignments-tab" class="tab-content p-6" style="display: none;">
                    <div class="space-y-4">
                        @forelse($complaint->assignments as $assignment)
                        <div
                            class="p-4 border border-gray-200 rounded-lg {{ $assignment->is_active ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-8 h-8 {{ $assignment->is_active ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 {{ $assignment->is_active ? 'text-green-600' : 'text-gray-600' }}"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $assignment->assignedTo->name }}
                                            <span class="px-2 py-1 text-xs rounded-full
                                                        @switch($assignment->assignment_type)
                                                            @case('Primary') bg-blue-100 text-blue-800 @break
                                                            @case('Secondary') bg-gray-100 text-gray-800 @break
                                                            @case('Observer') bg-purple-100 text-purple-800 @break
                                                        @endswitch">
                                                {{ $assignment->assignment_type }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Assigned by {{ $assignment->assignedBy->name }} on {{
                                            $assignment->assigned_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                @if($assignment->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                                @endif
                            </div>
                            @if($assignment->reason)
                            <div class="mt-3">
                                <p class="text-sm text-gray-700">{{ $assignment->reason }}</p>
                            </div>
                            @endif
                            @if($assignment->unassigned_at)
                            <div class="mt-2 text-xs text-gray-500">
                                Unassigned on {{ $assignment->unassigned_at->format('M d, Y H:i') }}
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-2">No assignments</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Operations Tab -->
                <div id="operations-tab" class="tab-content p-6" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status Update -->
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <h4 class="text-md font-medium mb-3">Update Status</h4>
                            <form method="POST" action="{{ route('complaints.update-status', $complaint) }}">
                                @csrf
                                @method('PATCH')
                                <div class="space-y-3">
                                    <select name="status" required class="w-full border-gray-300 rounded-md">
                                        <option value="">Select status</option>
                                        <option value="Open">Open</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Closed">Closed</option>
                                        <option value="Reopened">Reopened</option>
                                    </select>
                                    <input type="text" name="status_change_reason" placeholder="Reason (optional)"
                                        class="w-full border-gray-300 rounded-md">
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="px-3 py-1 bg-yellow-600 text-white rounded">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Assignment / Priority / Branch -->
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Assign to User</h4>
                                <form method="POST" action="{{ route('complaints.update', $complaint) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="space-y-3">
                                        <select name="assigned_to" required class="w-full border-gray-300 rounded-md">
                                            <option value="">Select user</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $complaint->assigned_to == $user->id ?
                                                'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="assignment_reason" placeholder="Reason (optional)"
                                            class="w-full border-gray-300 rounded-md">
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="px-3 py-1 bg-blue-600 text-white rounded">Assign</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Priority / Location Transfer</h4>
                                <form method="POST" action="{{ route('complaints.update', $complaint) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="space-y-3">
                                        <select name="priority" class="w-full border-gray-300 rounded-md">
                                            <option value="">Select priority</option>
                                            <option value="Low">Low</option>
                                            <option value="Medium">Medium</option>
                                            <option value="High">High</option>
                                            <option value="Critical">Critical</option>
                                        </select>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <div>
                                                <select name="branch_id"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                                    <option value="">Branch: Not Applicable</option>
                                                    @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ $complaint->branch_id ==
                                                        $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <select name="region_id"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                                    <option value="">Region: Not Applicable</option>
                                                    @foreach($regions as $region)
                                                    <option value="{{ $region->id }}" {{ $complaint->region_id ==
                                                        $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <select name="division_id"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                                    <option value="">Division: Not Applicable</option>
                                                    @foreach($divisions as $division)
                                                    <option value="{{ $division->id }}" {{ $complaint->division_id ==
                                                        $division->id ? 'selected' : '' }}>{{ $division->short_name ??
                                                        $division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <input type="text" name="priority_change_reason"
                                            placeholder="Priority change reason (required if raising to Critical)"
                                            class="w-full border-gray-300 rounded-md" />
                                        <div class="flex flex-wrap gap-2 justify-end text-xs">
                                            <button type="submit" name="_transfer_scope" value="priority"
                                                class="px-3 py-1 bg-indigo-600 text-white rounded">Update
                                                Priority</button>
                                            <button type="submit" name="_transfer_scope" value="branch"
                                                class="px-3 py-1 bg-blue-600 text-white rounded">Transfer
                                                Branch</button>
                                            <button type="submit" name="_transfer_scope" value="region"
                                                class="px-3 py-1 bg-purple-600 text-white rounded">Transfer
                                                Region</button>
                                            <button type="submit" name="_transfer_scope" value="division"
                                                class="px-3 py-1 bg-pink-600 text-white rounded">Transfer
                                                Division</button>
                                            <button type="submit" name="_transfer_scope" value="all"
                                                class="px-3 py-1 bg-green-600 text-white rounded">Save All</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Watchers Tab -->
                <div id="watchers-tab" class="tab-content p-6" style="display:none;">
                    <div
                        class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg text-xs text-gray-700 leading-relaxed">
                        <strong class="text-purple-700">What is a Watcher?</strong> A watcher is a user who is
                        subscribed to this complaint for visibility and updates. Watchers are NOT responsible for
                        resolving the issue (unlike the assignee) but they receive updates, can monitor progress, and
                        provide input when necessary (e.g. managers, stakeholders, subject-matter experts). Use the list
                        below to add or remove watchers without affecting assignment or workflow status.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Current Watchers</h4>
                            <div class="space-y-2">
                                @forelse($complaint->watchers as $watcher)
                                <div
                                    class="p-2 bg-white border border-gray-200 rounded flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ $watcher->user->name }}</span>
                                </div>
                                @empty
                                <p class="text-xs text-gray-500">No watchers.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Update Watchers</h4>
                            <form method="POST" action="{{ route('complaints.update-watchers', $complaint) }}">
                                @csrf
                                <select name="watchers[]" multiple size="8" class="w-full border-gray-300 rounded-md">
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $complaint->
                                        watchers->pluck('user_id')->contains($user->id) ?
                                        'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex justify-end mt-3">
                                    <button type="submit"
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Save
                                        Watchers</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Satisfaction Tab -->
                <div id="satisfaction-tab" class="tab-content p-6" style="display:none;">
                    <div class="max-w-lg space-y-6">
                        <div class="bg-white p-4 border border-gray-200 rounded">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Customer Satisfaction Score</h4>
                            <p class="text-xs text-gray-500 mb-3">Set a score from 1 (lowest) to 5 (highest). Only for
                                resolved/closed complaints.</p>
                            <form method="POST" action="{{ route('complaints.update-satisfaction', $complaint) }}">
                                @csrf
                                <select name="customer_satisfaction_score"
                                    class="w-full border-gray-300 rounded-md mb-3" required>
                                    <option value="">Select score</option>
                                    @for($i=1;$i<=5;$i++) <option value="{{ $i }}" {{ optional($complaint->
                                        metrics)->customer_satisfaction_score == $i ? 'selected' : '' }}>{{ $i }}
                                        </option>
                                        @endfor
                                </select>
                                <div class="flex justify-end">
                                    <button type="submit"
                                        class="px-3 py-1 bg-yellow-600 text-white rounded text-sm">Update
                                        Score</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <!-- Escalation Modal -->
    <div id="escalation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Escalate Complaint</h3>
                <form method="POST" action="{{ route('complaints.escalate', $complaint) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="escalated_to" class="block text-sm font-medium text-gray-700">Escalate
                                To</label>
                            <select name="escalated_to" id="escalated_to" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="escalation_level" class="block text-sm font-medium text-gray-700">Escalation
                                Level</label>
                            <select name="escalation_level" id="escalation_level" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">Level {{ $i }}</option>
                                    @endfor
                            </select>
                        </div>
                        <div>
                            <label for="escalation_reason" class="block text-sm font-medium text-gray-700">Escalation
                                Reason</label>
                            <textarea name="escalation_reason" id="escalation_reason" rows="3" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" id="cancel-escalation"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Escalate Complaint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
        function initTabs() {
            console.log('Initializing tab functionality...');

            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            console.log('Found tab buttons:', tabButtons.length);
            console.log('Found tab contents:', tabContents.length);

            if (tabButtons.length === 0) {
                console.error('No tab buttons found! Check if elements have .tab-button class');
                return;
            }

            // Initialize tabs - hide all then show history by default
            if (tabContents.length > 0) {
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });

                const historyTab = document.getElementById('history-tab');
                if (historyTab) {
                    historyTab.style.display = 'block';
                }
            }

            // Add click/keyboard listeners, but avoid duplicate listeners
            tabButtons.forEach((button, index) => {
                if (button.dataset.tabInit) return; // already initialized
                button.dataset.tabInit = '1';

                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const tabName = this.getAttribute('data-tab');
                    if (!tabName) return;

                    // Remove active classes from all tab buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-indigo-500', 'text-indigo-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.style.display = 'none';
                    });

                    // Activate clicked button
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-indigo-500', 'text-indigo-600');

                    // Show corresponding tab content
                    const targetTab = document.getElementById(tabName + '-tab');
                    if (targetTab) {
                        targetTab.style.display = 'block';
                    } else {
                        console.error('Target tab not found:', tabName + '-tab');
                    }
                });

                // Keyboard support
                button.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Escalation modal functionality
            const escalateBtn = document.getElementById('escalate-btn');
            const escalationModal = document.getElementById('escalation-modal');
            const cancelEscalation = document.getElementById('cancel-escalation');

            if (escalateBtn && escalationModal) {
                if (!escalateBtn.dataset.modalInit) {
                    escalateBtn.dataset.modalInit = '1';
                    escalateBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        escalationModal.classList.remove('hidden');
                    });
                }
            }

            if (cancelEscalation && escalationModal) {
                if (!cancelEscalation.dataset.modalInit) {
                    cancelEscalation.dataset.modalInit = '1';
                    cancelEscalation.addEventListener('click', function(e) {
                        e.preventDefault();
                        escalationModal.classList.add('hidden');
                    });
                }
            }

            if (escalationModal && !escalationModal.dataset.outsideInit) {
                escalationModal.dataset.outsideInit = '1';
                escalationModal.addEventListener('click', function(e) {
                    if (e.target === escalationModal) {
                        escalationModal.classList.add('hidden');
                    }
                });
            }

            console.log('Tab functionality initialized successfully');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTabs);
        } else {
            // Document is already loaded — initialize immediately
            initTabs();
        }
    })();
    </script>

    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush
</x-app-layout>