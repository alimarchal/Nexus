<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100">Audit {{ $audit->reference_no }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $audit->title }}</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('audits.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <a href="{{ route('audits.edit',$audit) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8 space-y-8">
        <x-status-message />

        <!-- Improved Summary Section with Better Readability -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <!-- Summary Header -->
            <div class="bg-gray-900 dark:bg-gray-800 px-6 py-4 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-white">Audit Overview</h3>
                        <p class="text-gray-300 mt-1 text-base">Complete audit information and status</p>
                    </div>
                    <div class="text-right">
                        @php
                        $statusColors = [
                        'planned' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'reporting' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'issued' => 'bg-green-100 text-green-800 border-green-300',
                        'closed' => 'bg-gray-100 text-gray-800 border-gray-300',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-300'
                        ];
                        @endphp
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-lg text-base font-bold border-2 {{ $statusColors[$audit->status] ?? 'bg-gray-100 text-gray-800 border-gray-300' }}">
                            {{ ucwords(str_replace('_',' ',$audit->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary Content -->
            <div class="p-6">
                <!-- Key Metrics Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center border border-blue-200 dark:border-blue-800">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $audit->score ?? '—' }}
                        </div>
                        <div class="text-base text-blue-600 dark:text-blue-400 font-medium mt-1">Score</div>
                    </div>
                    <div
                        class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 text-center border border-orange-200 dark:border-orange-800">
                        @php
                        $riskColors = [
                        'low' => 'text-green-700 dark:text-green-300',
                        'medium' => 'text-yellow-700 dark:text-yellow-300',
                        'high' => 'text-orange-700 dark:text-orange-300',
                        'critical' => 'text-red-700 dark:text-red-300'
                        ];
                        @endphp
                        <div class="text-2xl font-bold {{ $riskColors[$audit->risk_overall] ?? 'text-gray-500' }}">
                            {{ $audit->risk_overall ? ucfirst($audit->risk_overall) : '—' }}
                        </div>
                        <div class="text-base text-orange-600 dark:text-orange-400 font-medium mt-1">Risk Level</div>
                    </div>
                    <div
                        class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center border border-purple-200 dark:border-purple-800">
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{
                            $audit->findings->count() }}</div>
                        <div class="text-base text-purple-600 dark:text-purple-400 font-medium mt-1">Findings</div>
                    </div>
                    <div
                        class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 text-center border border-red-200 dark:border-red-800">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $audit->risks->count() }}
                        </div>
                        <div class="text-base text-red-600 dark:text-red-400 font-medium mt-1">Risks</div>
                    </div>
                </div>

                <!-- Audit Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">Basic Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Title:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold text-right">{{
                                    Str::limit($audit->title, 25) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Type:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $audit->type?->name ??
                                    'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Reference:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $audit->reference_no
                                    }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">Team Members</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Lead Auditor:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold text-right">{{
                                    Str::limit($audit->leadAuditor?->name ?? 'N/A', 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Auditee:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold text-right">{{
                                    Str::limit($audit->auditeeUser?->name ?? 'N/A', 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Team Size:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold">{{
                                    $audit->auditors->count() }} members</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">Timeline</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400 font-medium block">Planned:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold text-sm">
                                    {{ $audit->planned_start_date?->format('M d, Y') ?? 'Not set' }}
                                    @if($audit->planned_end_date)
                                    <br>to {{ $audit->planned_end_date->format('M d, Y') }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400 font-medium block">Actual:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold text-sm">
                                    {{ $audit->actual_start_date?->format('M d, Y') ?? 'Not started' }}
                                    @if($audit->actual_end_date)
                                    <br>to {{ $audit->actual_end_date->format('M d, Y') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags and Description -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse($audit->tags as $tag)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/50 dark:text-blue-200 dark:border-blue-700">
                                {{ $tag->name }}
                            </span>
                            @empty
                            <span class="text-gray-500 dark:text-gray-400 text-base">No tags assigned</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">Description</h4>
                        <p class="text-gray-700 dark:text-gray-300 text-base leading-relaxed">
                            {{ Str::limit($audit->description ?: 'No description provided', 150) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Organized Tabs Design -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <!-- Tab Navigation - Vertical Sidebar Style -->
            <div class="flex flex-col md:flex-row">
                <!-- Sidebar Navigation -->
                <div
                    class="md:w-64 bg-gray-50 dark:bg-gray-700 rounded-l-xl border-r border-gray-200 dark:border-gray-600">
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Audit Management</h3>
                        <nav class="space-y-2">
                            <button onclick="showTab('update-audit')" id="tab-update-audit"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600 active">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                <span>Update Audit</span>
                            </button>

                            <button onclick="showTab('documents')" id="tab-documents"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Documents</span>
                                    @if($audit->documents->count())
                                    <span
                                        class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-blue-900/50 dark:text-blue-200">
                                        {{ $audit->documents->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>

                            <button onclick="showTab('checklist')" id="tab-checklist"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Checklist</span>
                                    @if($checklistItems->count())
                                    <span
                                        class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-green-900/50 dark:text-green-200">
                                        {{ $checklistItems->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>

                            <button onclick="showTab('risks')" id="tab-risks"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Risk Management</span>
                                    @if($audit->risks->count())
                                    <span
                                        class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-red-900/50 dark:text-red-200">
                                        {{ $audit->risks->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>

                            <button onclick="showTab('findings')" id="tab-findings"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Findings & Actions</span>
                                    @if($audit->findings->count())
                                    <span
                                        class="ml-2 bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-purple-900/50 dark:text-purple-200">
                                        {{ $audit->findings->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>

                            <button onclick="showTab('team')" id="tab-team"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Team & Scope</span>
                                    <span
                                        class="ml-2 bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-indigo-900/50 dark:text-indigo-200">
                                        {{ $audit->auditors->count() + $audit->scopes->count() }}
                                    </span>
                                </div>
                            </button>

                            <button onclick="showTab('schedule')" id="tab-schedule"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span>Schedule & Timeline</span>
                            </button>

                            <button onclick="showTab('notifications')" id="tab-notifications"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-5-5.5V9.5l5-5.5h-5m-6 10c.621 0 1.125.504 1.125 1.125 0 .621-.504 1.125-1.125 1.125m0 0c-.621 0-1.125-.504-1.125-1.125 0-.621.504-1.125 1.125-1.125m0 0v1.5a2.25 2.25 0 01-4.5 0V9.75a2.25 2.25 0 014.5 0v1.5">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Notifications</span>
                                    @if($audit->notifications->count())
                                    <span
                                        class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-yellow-900/50 dark:text-yellow-200">
                                        {{ $audit->notifications->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>

                            <button onclick="showTab('metrics')" id="tab-metrics"
                                class="tab-button w-full flex items-center px-4 py-3 text-left rounded-lg font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-white hover:shadow-sm dark:hover:bg-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <div class="flex-1 flex items-center justify-between">
                                    <span>Analytics & Metrics</span>
                                    @if($audit->metrics->count())
                                    <span
                                        class="ml-2 bg-teal-100 text-teal-800 text-xs font-semibold px-2 py-1 rounded-full dark:bg-teal-900/50 dark:text-teal-200">
                                        {{ $audit->metrics->count() }}
                                    </span>
                                    @endif
                                </div>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1 p-8">
                    <!-- Current Tab Title -->
                    <div class="mb-6">
                        <h2 id="current-tab-title" class="text-2xl font-bold text-gray-900 dark:text-gray-100">Update
                            Audit</h2>
                        <p id="current-tab-description" class="text-gray-600 dark:text-gray-400 mt-1">Modify audit
                            information and track changes</p>
                    </div>
                    <!-- Update Audit Tab -->
                    <div id="content-update-audit" class="tab-content">
                        <div class="max-w-4xl mx-auto">
                            <!-- Basic Info Update -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Update Basic
                                            Information</h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Modify core audit details
                                            and information</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.update-basic-info', $audit) }}"
                                    class="space-y-6">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div>
                                                <label
                                                    class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Title</label>
                                                <input type="text" name="title" value="{{ $audit->title }}"
                                                    class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                                <textarea name="description" rows="4"
                                                    class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $audit->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <label
                                                    class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Overall
                                                    Risk Level</label>
                                                <select name="risk_overall"
                                                    class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">-- None --</option>
                                                    @foreach(['low','medium','high','critical'] as $r)
                                                    <option value="{{ $r }}" @selected($audit->risk_overall===$r)>{{
                                                        ucfirst($r) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Planned
                                                    Dates</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label
                                                            class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Start
                                                            Date</label>
                                                        <input type="date" name="planned_start_date"
                                                            value="{{ $audit->planned_start_date?->format('Y-m-d') }}"
                                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm text-gray-600 dark:text-gray-400 mb-1">End
                                                            Date</label>
                                                        <input type="date" name="planned_end_date"
                                                            value="{{ $audit->planned_end_date?->format('Y-m-d') }}"
                                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end pt-4">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Update Basic Info
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Status Update Section -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Update Status
                                        </h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Change audit status and
                                            track progress</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.update-status', $audit) }}"
                                    class="space-y-4">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Current
                                                Status</label>
                                            <div class="text-base px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                                {{ ucwords(str_replace('_', ' ', $audit->status)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">New
                                                Status</label>
                                            <select name="status" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500">
                                                @foreach(['planned','in_progress','reporting','issued','closed','cancelled']
                                                as $s)
                                                <option value="{{ $s }}" @selected($audit->status===$s)>{{
                                                    ucwords(str_replace('_',' ',$s)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Status
                                            Change Notes</label>
                                        <textarea name="notes" rows="3"
                                            placeholder="Add notes about this status change..."
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div id="content-documents" class="tab-content hidden">
                        <div class="max-w-6xl mx-auto">
                            <!-- Document Upload Section -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Upload Documents
                                        </h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Add new audit-related
                                            documents and files</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.documents.add', $audit) }}"
                                    enctype="multipart/form-data" class="space-y-6">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                                Files</label>
                                            <input type="file" name="documents[]" multiple required
                                                class="w-full text-base text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-base file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-200 border border-gray-300 dark:border-gray-600 rounded-lg" />
                                            <p class="text-sm text-gray-500 mt-2">Upload multiple files. Max size: 10MB
                                                per file.</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Document
                                                Category</label>
                                            <select name="category" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">Select Category</option>
                                                <option value="evidence">Evidence</option>
                                                <option value="report">Report</option>
                                                <option value="correspondence">Correspondence</option>
                                                <option value="supporting">Supporting Documents</option>
                                                <option value="templates">Templates</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                        <textarea name="description" rows="3"
                                            placeholder="Brief description of the documents..."
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            Upload Documents
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Existing Documents List -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Document Library
                                        </h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Manage existing audit
                                            documents</p>
                                    </div>
                                    <div class="text-base font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $audit->documents->count() }} Documents
                                    </div>
                                </div>

                                @if($audit->documents->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Document</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Category</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Size</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Uploaded</th>
                                                <th
                                                    class="px-6 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($audit->documents as $doc)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div
                                                                class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                                <svg class="h-6 w-6 text-gray-400" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div
                                                                class="text-base font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $doc->original_name }}</div>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{
                                                                $doc->description ?? 'No description' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                        {{ ucfirst($doc->category ?? 'uncategorized') }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-base text-gray-900 dark:text-gray-100">
                                                    {{ number_format($doc->size_bytes/1024, 1) }} KB
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-base text-gray-500 dark:text-gray-400">
                                                    {{ $doc->uploaded_at?->format('M d, Y H:i') ??
                                                    $doc->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                                                    <div class="flex items-center justify-end space-x-2">
                                                        <a href="#"
                                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                            Download
                                                        </a>
                                                        <button onclick="editDocument({{ $doc->id }})"
                                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                            Edit
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('audits.documents.delete', [$audit, $doc]) }}"
                                                            class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                onclick="return confirm('Are you sure?')"
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <h3 class="mt-2 text-base font-medium text-gray-900 dark:text-gray-100">No documents
                                    </h3>
                                    <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Get started by uploading
                                        your first document.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Tab -->
                    <div id="content-checklist" class="tab-content hidden">
                        <div class="max-w-6xl mx-auto">
                            <!-- Add New Checklist Item Section -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Add Checklist
                                            Item</h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Create new audit checklist
                                            items and requirements</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.checklist.add', $audit) }}"
                                    class="space-y-6">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Reference
                                                Code</label>
                                            <input type="text" name="reference_code" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500"
                                                placeholder="e.g., CHK-001" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                            <select name="category" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500">
                                                <option value="">Select Category</option>
                                                <option value="compliance">Compliance</option>
                                                <option value="operational">Operational</option>
                                                <option value="financial">Financial</option>
                                                <option value="documentation">Documentation</option>
                                                <option value="process">Process</option>
                                                <option value="system">System</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Checklist
                                            Item Title</label>
                                        <input type="text" name="title" required
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500"
                                            placeholder="Enter checklist item title..." />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                        <textarea name="description" rows="3"
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500"
                                            placeholder="Detailed description of the checklist requirement..."></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Priority
                                                Level</label>
                                            <select name="priority"
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Max
                                                Score</label>
                                            <input type="number" name="max_score" step="0.01" value="100"
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500" />
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Add Checklist Item
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Checklist Response Section -->
                            @if($checklistItems->count())
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Checklist
                                            Responses</h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Record responses for each
                                            checklist item</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.save-responses', $audit) }}"
                                    class="space-y-4">
                                    @csrf
                                    <div class="space-y-4">
                                        @foreach($checklistItems as $ci)
                                        @php($resp = $audit->responses->firstWhere('audit_checklist_item_id', $ci->id))
                                        <div
                                            class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex-1">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $ci->reference_code }} - {{ $ci->title }}</h4>
                                                    @if($ci->description)
                                                    <p class="text-base text-gray-600 dark:text-gray-400 mt-1">{{
                                                        $ci->description }}</p>
                                                    @endif
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($ci->priority === 'critical') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                                    @elseif($ci->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200
                                                    @elseif($ci->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                                    @else bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 @endif">
                                                    {{ ucfirst($ci->priority ?? 'medium') }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Response</label>
                                                    <input type="text" name="responses[{{ $ci->id }}][response_value]"
                                                        value="{{ $resp->response_value ?? '' }}"
                                                        placeholder="Enter response..."
                                                        class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Score</label>
                                                    <input type="number" step="0.01"
                                                        name="responses[{{ $ci->id }}][score]"
                                                        value="{{ $resp->score ?? '' }}"
                                                        placeholder="0-{{ $ci->max_score ?? 100 }}"
                                                        class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                                    <select name="responses[{{ $ci->id }}][status]"
                                                        class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="pending" @selected(($resp->status ?? 'pending')
                                                            === 'pending')>Pending</option>
                                                        <option value="compliant" @selected(($resp->status ?? '') ===
                                                            'compliant')>Compliant</option>
                                                        <option value="non_compliant" @selected(($resp->status ?? '')
                                                            === 'non_compliant')>Non-Compliant</option>
                                                        <option value="partial" @selected(($resp->status ?? '') ===
                                                            'partial')>Partially Compliant</option>
                                                        <option value="not_applicable" @selected(($resp->status ?? '')
                                                            === 'not_applicable')>Not Applicable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comments</label>
                                                <textarea name="responses[{{ $ci->id }}][comment]" rows="2"
                                                    placeholder="Additional comments or observations..."
                                                    class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $resp->comment ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="flex justify-end pt-4">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Save All Responses
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endif

                            <!-- Existing Checklist Items -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Checklist Library
                                        </h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Manage audit checklist
                                            items</p>
                                    </div>
                                    <div class="text-base font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $checklistItems->count() }} Items
                                    </div>
                                </div>

                                @if($checklistItems->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Item</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Category</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Priority</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Response Status</th>
                                                <th
                                                    class="px-6 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($checklistItems as $ci)
                                            @php($resp = $audit->responses->firstWhere('audit_checklist_item_id',
                                            $ci->id))
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4">
                                                    <div class="text-base font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $ci->reference_code }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $ci->title
                                                        }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                        {{ ucfirst($ci->category ?? 'general') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($ci->priority === 'critical') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                                        @elseif($ci->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200
                                                        @elseif($ci->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                                        @else bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 @endif">
                                                        {{ ucfirst($ci->priority ?? 'medium') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($resp)
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($resp->status === 'compliant') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200
                                                        @elseif($resp->status === 'non_compliant') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                                        @elseif($resp->status === 'partial') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                                        {{ ucwords(str_replace('_', ' ', $resp->status)) }}
                                                    </span>
                                                    @else
                                                    <span class="text-base text-gray-500 dark:text-gray-400">No
                                                        Response</span>
                                                    @endif
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-right text-base font-medium">
                                                    <div class="flex items-center justify-end space-x-2">
                                                        <button onclick="editChecklistItem({{ $ci->id }})"
                                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                            Edit
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('audits.checklist.delete', [$audit, $ci]) }}"
                                                            class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                onclick="return confirm('Are you sure?')"
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                        </path>
                                    </svg>
                                    <h3 class="mt-2 text-base font-medium text-gray-900 dark:text-gray-100">No checklist
                                        items</h3>
                                    <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Start by creating your
                                        first checklist item.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Risk Management Tab -->
                    <div id="content-risks" class="tab-content hidden">
                        <div class="max-w-6xl mx-auto">
                            <!-- Add New Risk Section -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Add New Risk</h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Identify and assess
                                            potential audit risks</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.risks.add', $audit) }}" class="space-y-6">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Risk
                                                Title</label>
                                            <input type="text" name="title" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500"
                                                placeholder="Enter risk title..." />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Risk
                                                Category</label>
                                            <select name="category" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                <option value="">Select Category</option>
                                                <option value="operational">Operational</option>
                                                <option value="compliance">Compliance</option>
                                                <option value="financial">Financial</option>
                                                <option value="strategic">Strategic</option>
                                                <option value="reputational">Reputational</option>
                                                <option value="technology">Technology</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Likelihood</label>
                                            <select name="likelihood" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                <option value="">Select Likelihood</option>
                                                @foreach(['low','medium','high','critical'] as $v)
                                                <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Impact</label>
                                            <select name="impact" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                <option value="">Select Impact</option>
                                                @foreach(['low','medium','high','critical'] as $v)
                                                <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                            <select name="status" required
                                                class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                <option value="identified">Identified</option>
                                                <option value="assessing">Under Assessment</option>
                                                <option value="mitigating">Mitigating</option>
                                                <option value="monitoring">Monitoring</option>
                                                <option value="closed">Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Risk
                                            Description</label>
                                        <textarea name="description" rows="4" required
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500"
                                            placeholder="Describe the risk in detail..."></textarea>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Mitigation
                                            Measures</label>
                                        <textarea name="mitigation" rows="3"
                                            class="w-full text-base rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500"
                                            placeholder="Describe proposed mitigation measures..."></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-base font-semibold rounded-lg shadow-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Add Risk
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Existing Risks List -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Risk Register
                                        </h3>
                                        <p class="text-base text-gray-600 dark:text-gray-400">Monitor and manage
                                            identified risks</p>
                                    </div>
                                    <div class="text-base font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $audit->risks->count() }} Risks
                                    </div>
                                </div>

                                @if($audit->risks->count())
                                <div class="space-y-4">
                                    @foreach($audit->risks as $risk)
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3 mb-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $risk->title }}</h4>
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                        @if($risk->risk_level === 'critical') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                                        @elseif($risk->risk_level === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200
                                                        @elseif($risk->risk_level === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                                        @else bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 @endif">
                                                        {{ ucfirst($risk->risk_level ?? 'Unknown') }}
                                                    </span>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 text-base">
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Category:</span>
                                                        <span
                                                            class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{
                                                            ucfirst($risk->category ?? 'N/A') }}</span>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="text-gray-500 dark:text-gray-400">Likelihood:</span>
                                                        <span
                                                            class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{
                                                            ucfirst($risk->likelihood ?? 'N/A') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Impact:</span>
                                                        <span
                                                            class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{
                                                            ucfirst($risk->impact ?? 'N/A') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                                        <span
                                                            class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{
                                                            ucfirst($risk->status ?? 'N/A') }}</span>
                                                    </div>
                                                </div>
                                                @if($risk->description)
                                                <p class="text-base text-gray-700 dark:text-gray-300 mb-2">{{
                                                    $risk->description }}</p>
                                                @endif
                                                @if($risk->mitigation)
                                                <div
                                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                                    <p
                                                        class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-1">
                                                        Mitigation Measures:</p>
                                                    <p class="text-base text-blue-800 dark:text-blue-300">{{
                                                        $risk->mitigation }}</p>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="flex flex-col space-y-2 ml-4">
                                                <button onclick="editRisk({{ $risk->id }})"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                                    Edit
                                                </button>
                                                <form method="POST"
                                                    action="{{ route('audits.risks.delete', [$audit, $risk]) }}"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    <h3 class="mt-2 text-base font-medium text-gray-900 dark:text-gray-100">No risks
                                        identified</h3>
                                    <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Start by adding your
                                        first risk assessment.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Findings & Actions Tab -->
                    <div id="content-findings" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Findings & Actions</h3>
                        @php($findings = $audit->findings)
                        @if($findings->count())
                        <div class="overflow-x-auto text-xs">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="px-2 py-1 text-left">Ref</th>
                                        <th class="px-2 py-1 text-left">Title</th>
                                        <th class="px-2 py-1 text-left">Severity</th>
                                        <th class="px-2 py-1 text-left">Status</th>
                                        <th class="px-2 py-1 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($findings as $f)
                                    <tr class="border-b border-gray-200 dark:border-gray-700 align-top">
                                        <td class="px-2 py-1 whitespace-nowrap">{{ $f->reference_no ?? $f->id }}</td>
                                        <td class="px-2 py-1">{{ Str::limit($f->title,50) }}</td>
                                        <td class="px-2 py-1">{{ ucfirst($f->severity ?? '-') }}</td>
                                        <td class="px-2 py-1">{{ ucfirst($f->status ?? '-') }}</td>
                                        <td class="px-2 py-1">
                                            @if($f->actions->count())
                                            <ul class="space-y-1">
                                                @foreach($f->actions as $act)
                                                <li>
                                                    <span class="font-medium">{{ Str::limit($act->title,40) }}</span>
                                                    <span class="text-gray-500">({{ ucfirst($act->status) }})</span>
                                                    @if($act->updates->count())
                                                    <ul class="ml-3 list-disc">
                                                        @foreach($act->updates->take(3) as $up)
                                                        <li class="text-[10px]">{{ Str::limit($up->update_text,60) }}
                                                            <span class="text-gray-400">{{
                                                                $up->created_at?->diffForHumans()
                                                                }}</span>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </li>
                                                @endforeach
                                            </ul>
                                            @else
                                            <span class="text-gray-400">No actions</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-sm text-gray-500">No findings recorded.</p>
                        @endif
                        <form method="POST" action="{{ route('audits.findings.add',$audit) }}"
                            class="mt-4 text-xs space-y-2">
                            @csrf
                            <input type="text" name="title" placeholder="New finding title"
                                class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                            <select name="severity" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                <option value="">Severity</option>
                                @foreach(['low','medium','high','critical'] as $sv)
                                <option value="{{ $sv }}">{{ ucfirst($sv) }}</option>
                                @endforeach
                            </select>
                            <textarea name="description" rows="2" placeholder="Description"
                                class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                            <button class="px-3 py-1 bg-blue-600 text-white rounded">Add Finding</button>
                        </form>
                    </div>

                    <!-- Team & Scope Tab -->
                    <div id="content-team" class="tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Auditors ({{ $audit->auditors->count() }})</h3>
                                @if($audit->auditors->count())
                                <ul class="text-sm space-y-2">
                                    @foreach($audit->auditors as $aud)
                                    <li class="border-b pb-1 flex justify-between">
                                        <span>{{ $aud->user?->name ?? '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ ucfirst($aud->role ?? 'member')
                                            }}@if($aud->is_primary) •
                                            Primary @endif</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-sm text-gray-500">No auditors assigned.</p>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Scopes ({{ $audit->scopes->count() }})</h3>
                                @if($audit->scopes->count())
                                <ul class="text-xs space-y-1">
                                    @foreach($audit->scopes as $sc)
                                    <li class="border-b pb-1"><span class="font-medium">{{ $sc->scope_item }}</span> -
                                        <span class="text-gray-600">{{ $sc->is_in_scope ? 'In Scope' : 'Out of Scope'
                                            }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-sm text-gray-500">No scope items defined.</p>
                                @endif
                                <form method="POST" action="{{ route('audits.scopes.add',$audit) }}"
                                    class="mt-4 space-y-2 text-xs">
                                    @csrf
                                    <input type="text" name="scope_item" placeholder="Scope item"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                                    <textarea name="description" rows="2" placeholder="Description"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                                    <label class="inline-flex items-center space-x-2 text-xs"><input type="checkbox"
                                            name="is_in_scope" value="1" checked><span>In Scope</span></label>
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded">Add Scope</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Tab -->
                    <div id="content-schedule" class="tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Schedules ({{ $audit->schedules->count() }})</h3>
                                @if($audit->schedules->count())
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="px-2 py-1 text-left">Frequency</th>
                                            <th class="px-2 py-1 text-left">Scheduled</th>
                                            <th class="px-2 py-1 text-left">Next Run</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($audit->schedules as $sch)
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <td class="px-2 py-1">{{ ucfirst($sch->frequency) }}</td>
                                            <td class="px-2 py-1">{{ $sch->scheduled_date?->format('Y-m-d') }}</td>
                                            <td class="px-2 py-1">{{ $sch->next_run_date?->format('Y-m-d') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p class="text-sm text-gray-500">No schedules.</p>
                                @endif
                                <form method="POST" action="{{ route('audits.schedules.add',$audit) }}"
                                    class="mt-4 text-xs space-y-2">
                                    @csrf
                                    <input type="text" name="frequency" placeholder="Frequency"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                                    <input type="date" name="scheduled_date"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded">Add Schedule</button>
                                </form>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Child Audits ({{ $audit->children->count() }})
                                </h3>
                                @if($audit->children->count())
                                <ul class="text-sm space-y-1">
                                    @foreach($audit->children as $child)
                                    <li><a href="{{ route('audits.show',$child) }}"
                                            class="text-blue-600 hover:underline">{{
                                            $child->reference_no }}</a> - {{ Str::limit($child->title,40) }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-sm text-gray-500">No child audits.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="content-notifications" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Notifications ({{ $audit->notifications->count() }})</h3>
                        @if($audit->notifications->count())
                        <div class="overflow-x-auto text-xs">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="px-2 py-1 text-left">Channel</th>
                                        <th class="px-2 py-1 text-left">Subject</th>
                                        <th class="px-2 py-1 text-left">Status</th>
                                        <th class="px-2 py-1 text-left">Sent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($audit->notifications as $n)
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-2 py-1">{{ ucfirst($n->channel) }}</td>
                                        <td class="px-2 py-1">{{ Str::limit($n->subject,40) }}</td>
                                        <td class="px-2 py-1">{{ ucfirst($n->status) }}</td>
                                        <td class="px-2 py-1">{{ $n->sent_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-sm text-gray-500">No notifications.</p>
                        @endif
                        <form method="POST" action="{{ route('audits.notifications.add',$audit) }}"
                            class="mt-4 text-xs space-y-2">
                            @csrf
                            <input type="text" name="channel" placeholder="Channel (email)"
                                class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                            <input type="text" name="subject" placeholder="Subject"
                                class="w-full rounded border-gray-300 dark:bg-gray-900" required />
                            <textarea name="body" rows="2" placeholder="Body"
                                class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                            <button class="px-3 py-1 bg-blue-600 text-white rounded">Queue Notification</button>
                        </form>
                    </div>

                    <!-- Metrics Tab -->
                    <div id="content-metrics" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Metrics</h3>
                        @php($metrics = $audit->metrics)
                        @if($metrics->count())
                        <ul class="text-xs grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($metrics as $m)
                            <li class="border rounded p-2">
                                <div class="font-medium">{{ $m->metric_key }}</div>
                                <div class="text-gray-600">Value: {{ $m->metric_value ?? $m->numeric_value ?? '—' }}
                                </div>
                                <div class="text-[10px] text-gray-400">Calculated: {{
                                    $m->calculated_at?->diffForHumans() }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-sm text-gray-500">No metrics cached.</p>
                        @endif
                        <form method="POST" action="{{ route('audits.metrics.recalc',$audit) }}" class="mt-4">
                            @csrf
                            <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded">Recalculate
                                Metrics</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Status History Section (separate from tabs) -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Status History</h3>
                @if($statusHistory->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <th class="px-3 py-2 text-left">Changed At</th>
                                <th class="px-3 py-2 text-left">From</th>
                                <th class="px-3 py-2 text-left">To</th>
                                <th class="px-3 py-2 text-left">By</th>
                                <th class="px-3 py-2 text-left">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusHistory as $h)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-3 py-2">{{ $h->changed_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2">{{ $h->from_status ? ucwords(str_replace('_',' ',$h->from_status))
                                    :
                                    '—' }}</td>
                                <td class="px-3 py-2 font-semibold">{{ ucwords(str_replace('_',' ',$h->to_status)) }}
                                </td>
                                <td class="px-3 py-2">{{ $h->changer?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $h->note ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500">No history records.</p>
                @endif
            </div>
        </div>

        <script>
            // Tab Configuration
        const tabConfig = {
            'update-audit': {
                title: 'Update Audit',
                description: 'Modify audit information and track changes'
            },
            'documents': {
                title: 'Document Management',
                description: 'Upload, organize and manage audit-related documents'
            },
            'checklist': {
                title: 'Audit Checklist',
                description: 'Track compliance requirements and audit items'
            },
            'risks': {
                title: 'Risk Management',
                description: 'Identify, assess and mitigate audit risks'
            },
            'findings': {
                title: 'Findings & Actions',
                description: 'Record audit findings and corrective actions'
            },
            'team': {
                title: 'Team & Scope',
                description: 'Manage audit team members and define scope'
            },
            'schedule': {
                title: 'Schedule & Timeline',
                description: 'Plan and track audit schedules and milestones'
            },
            'notifications': {
                title: 'Notifications',
                description: 'Manage alerts and communication settings'
            },
            'metrics': {
                title: 'Analytics & Metrics',
                description: 'View performance metrics and audit analytics'
            }
        };

        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => {
                content.style.display = 'none';
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => {
                button.classList.remove('active', 'bg-white', 'shadow-sm', 'text-blue-600', 'dark:bg-gray-600', 'dark:text-blue-400');
                button.classList.add('text-gray-700', 'dark:text-gray-300');
            });
            
            // Show selected tab content
            const targetContent = document.getElementById(`content-${tabName}`);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('active');
            }
            
            // Add active class to selected button
            const targetButton = document.getElementById(`tab-${tabName}`);
            if (targetButton) {
                targetButton.classList.add('active', 'bg-white', 'shadow-sm', 'text-blue-600', 'dark:bg-gray-600', 'dark:text-blue-400');
                targetButton.classList.remove('text-gray-700', 'dark:text-gray-300');
            }

            // Update tab title and description
            if (tabConfig[tabName]) {
                const titleElement = document.getElementById('current-tab-title');
                const descriptionElement = document.getElementById('current-tab-description');
                
                if (titleElement) titleElement.textContent = tabConfig[tabName].title;
                if (descriptionElement) descriptionElement.textContent = tabConfig[tabName].description;
            }
        }

        // Initialize first tab as active
        document.addEventListener('DOMContentLoaded', function() {
            showTab('update-audit');
            
            // Add smooth transitions to all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.transition = 'opacity 0.3s ease-in-out';
            });
        });
        </script>

        <style>
            .tab-button.active {
                border-color: #3B82F6 !important;
                color: #2563EB !important;
                background-color: rgba(59, 130, 246, 0.05);
            }

            .dark .tab-button.active {
                background-color: rgba(59, 130, 246, 0.1);
                color: #60A5FA !important;
            }

            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            /* Enhanced form styling */
            .form-input:focus {
                transform: translateY(-1px);
                box-shadow: 0 10px 25px rgba(59, 130, 246, 0.1);
            }

            /* Card hover effects */
            .card-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            /* Status badge animations */
            .status-badge {
                animation: pulse-subtle 2s infinite;
            }

            @keyframes pulse-subtle {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.9;
                }
            }
        </style>
</x-app-layout>