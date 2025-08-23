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

        <!-- Enhanced Summary Section -->
        <div
            class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-xl rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <!-- Summary Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Audit Overview</h3>
                        <p class="text-blue-100 mt-1">Comprehensive audit information and current status</p>
                    </div>
                    <div class="text-right">
                        @php
                        $statusColors = [
                        'planned' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'reporting' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'issued' => 'bg-green-100 text-green-800 border-green-200',
                        'closed' => 'bg-gray-100 text-gray-800 border-gray-200',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-200'
                        ];
                        @endphp
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border {{ $statusColors[$audit->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                            {{ ucwords(str_replace('_',' ',$audit->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary Content -->
            <div class="p-8">
                <!-- Key Metrics Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div
                        class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $audit->score ?? '—' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Score</div>
                    </div>
                    <div
                        class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        @php
                        $riskColors = [
                        'low' => 'text-green-600 dark:text-green-400',
                        'medium' => 'text-yellow-600 dark:text-yellow-400',
                        'high' => 'text-orange-600 dark:text-orange-400',
                        'critical' => 'text-red-600 dark:text-red-400'
                        ];
                        @endphp
                        <div class="text-3xl font-bold {{ $riskColors[$audit->risk_overall] ?? 'text-gray-400' }}">
                            {{ $audit->risk_overall ? ucfirst($audit->risk_overall) : '—' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Overall Risk</div>
                    </div>
                    <div
                        class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{
                            $audit->findings->count() }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Findings</div>
                    </div>
                    <div
                        class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $audit->risks->count()
                            }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Risks</div>
                    </div>
                </div>

                <!-- Detailed Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Title:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">{{ $audit->title
                                    }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Type:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">{{ $audit->type?->name
                                    ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Lead Auditor:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">{{
                                    $audit->leadAuditor?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-orange-100 dark:bg-orange-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Auditee:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">{{
                                    $audit->auditeeUser?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Planned:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">
                                    {{ $audit->planned_start_date?->format('M d, Y') }}
                                    {{ $audit->planned_end_date? ' - '.$audit->planned_end_date->format('M d, Y') : ''
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div
                                class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Actual:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">
                                    {{ $audit->actual_start_date?->format('M d, Y') ?? '—' }}
                                    {{ $audit->actual_end_date? ' - '.$audit->actual_end_date->format('M d, Y') : '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center space-x-2 mb-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Tags</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @forelse($audit->tags as $tag)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-50 to-blue-100 text-blue-800 border border-blue-200 dark:from-blue-900/50 dark:to-blue-800/50 dark:text-blue-200 dark:border-blue-700">
                                    {{ $tag->name }}
                                </span>
                                @empty
                                <span class="text-gray-500 dark:text-gray-400 text-sm">No tags assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description and Scope -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div
                        class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h7"></path>
                            </svg>
                            Description
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{
                            $audit->description ?: 'No description provided' }}</p>
                    </div>
                    <div
                        class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Scope Summary
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{
                            $audit->scope_summary ?: 'No scope summary provided' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Tabs Container -->
        <div
            class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <!-- Enhanced Tab Navigation -->
            <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-b border-gray-200 dark:border-gray-700">
                <nav class="flex overflow-x-auto scrollbar-hide" aria-label="Tabs">
                    <button onclick="showTab('update-audit')" id="tab-update-audit"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2 active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Update Audit</span>
                    </button>
                    <button onclick="showTab('documents')" id="tab-documents"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Documents</span>
                        @if($audit->documents->count())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                            {{ $audit->documents->count() }}
                        </span>
                        @endif
                    </button>
                    <button onclick="showTab('checklist')" id="tab-checklist"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Checklist</span>
                        @if($checklistItems->count())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                            {{ $checklistItems->count() }}
                        </span>
                        @endif
                    </button>
                    <button onclick="showTab('risks')" id="tab-risks"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Risks</span>
                        @if($audit->risks->count())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                            {{ $audit->risks->count() }}
                        </span>
                        @endif
                    </button>
                    <button onclick="showTab('findings')" id="tab-findings"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Findings</span>
                        @if($audit->findings->count())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
                            {{ $audit->findings->count() }}
                        </span>
                        @endif
                    </button>
                    <button onclick="showTab('team')" id="tab-team"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Team</span>
                    </button>
                    <button onclick="showTab('schedule')" id="tab-schedule"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Schedule</span>
                    </button>
                    <button onclick="showTab('notifications')" id="tab-notifications"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-5-5.5V9.5l5-5.5h-5m-6 10c.621 0 1.125.504 1.125 1.125 0 .621-.504 1.125-1.125 1.125m0 0c-.621 0-1.125-.504-1.125-1.125 0-.621.504-1.125 1.125-1.125m0 0v1.5a2.25 2.25 0 01-4.5 0V9.75a2.25 2.25 0 014.5 0v1.5">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Notifications</span>
                    </button>
                    <button onclick="showTab('metrics')" id="tab-metrics"
                        class="tab-button group min-w-0 flex-1 overflow-hidden py-4 px-6 text-sm font-medium text-center border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        <span class="hidden sm:block">Metrics</span>
                    </button>
                </nav>
            </div>

            <!-- Enhanced Tab Content -->
            <div class="p-8 bg-white dark:bg-gray-800">
                <!-- Update Audit Tab -->
                <div id="content-update-audit" class="tab-content">
                    <div class="max-w-4xl mx-auto">
                        <div
                            class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
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
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Update Audit Details
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Modify audit information and
                                        track changes</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('audits.update',$audit) }}"
                                enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                            <select name="status"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach(['planned','in_progress','reporting','issued','closed','cancelled']
                                                as $s)
                                                <option value="{{ $s }}" @selected($audit->status===$s)>{{
                                                    ucwords(str_replace('_',' ',$s))
                                                    }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Risk
                                                Overall</label>
                                            <select name="risk_overall"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">-- None --</option>
                                                @foreach(['low','medium','high','critical'] as $r)
                                                <option value="{{ $r }}" @selected($audit->risk_overall===$r)>{{
                                                    ucfirst($r) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Planned
                                                Dates</label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <input type="date" name="planned_start_date"
                                                    value="{{ $audit->planned_start_date?->format('Y-m-d') }}"
                                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                <input type="date" name="planned_end_date"
                                                    value="{{ $audit->planned_end_date?->format('Y-m-d') }}"
                                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Documents
                                                (add)</label>
                                            <input type="file" name="documents[]" multiple
                                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/50 dark:file:text-blue-200" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                                            <select name="tag_ids[]" multiple size="4"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($availableTags as $tag)
                                                <option value="{{ $tag->id }}" @selected($audit->
                                                    tags->pluck('id')->contains($tag->id))>{{
                                                    $tag->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Risk Section -->
                                <div
                                    class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                                    <h4
                                        class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                        Quick Risk Entry
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                            <input type="text" name="risk[title]" placeholder="Risk title"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Likelihood</label>
                                            <select name="risk[likelihood]"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                @foreach(['low','medium','high','critical'] as $v)
                                                <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Impact</label>
                                            <select name="risk[impact]"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                @foreach(['low','medium','high','critical'] as $v)
                                                <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <textarea name="risk[description]" rows="3" placeholder="Risk description..."
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-semibold rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div id="content-documents" class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-4">Documents</h3>
                    @if($audit->documents->count())
                    <ul class="text-sm space-y-2">
                        @foreach($audit->documents as $doc)
                        <li class="flex justify-between items-center border-b pb-1">
                            <div>
                                <span class="font-medium">{{ $doc->original_name }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($doc->size_bytes/1024,1) }}
                                    KB)</span>
                                <span class="block text-xs text-gray-400">{{ $doc->uploaded_at?->format('Y-m-d H:i')
                                    }}</span>
                            </div>
                            <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $doc->category }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No documents uploaded.</p>
                    @endif
                </div>

                <!-- Checklist Tab -->
                <div id="content-checklist" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Checklist Items ({{ $checklistItems->count() }})</h3>
                            @if($checklistItems->count())
                            <div class="max-h-64 overflow-y-auto text-xs">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="px-2 py-1 text-left">Ref</th>
                                            <th class="px-2 py-1 text-left">Title</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($checklistItems as $ci)
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <td class="px-2 py-1 whitespace-nowrap">{{ $ci->reference_code }}</td>
                                            <td class="px-2 py-1">{{ $ci->title }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-sm text-gray-500">No checklist items for this type.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Enter Checklist Responses</h3>
                        @if($checklistItems->count())
                        <form method="POST" action="{{ route('audits.save-responses',$audit) }}" class="space-y-4">
                            @csrf
                            <div class="max-h-72 overflow-y-auto border rounded divide-y">
                                @foreach($checklistItems as $ci)
                                @php($resp = $audit->responses->firstWhere('audit_checklist_item_id',$ci->id))
                                <div class="p-3 text-xs">
                                    <div class="font-medium">{{ $ci->reference_code }} - {{ $ci->title }}</div>
                                    <div class="mt-1 grid grid-cols-1 md:grid-cols-6 gap-2">
                                        <input type="text" name="responses[{{ $ci->id }}][response_value]"
                                            placeholder="Response" value="{{ $resp->response_value ?? '' }}"
                                            class="col-span-2 rounded border-gray-300 dark:bg-gray-900" />
                                        <input type="number" step="0.01" name="responses[{{ $ci->id }}][score]"
                                            placeholder="Score" value="{{ $resp->score ?? '' }}"
                                            class="w-24 rounded border-gray-300 dark:bg-gray-900" />
                                        <input type="text" name="responses[{{ $ci->id }}][comment]"
                                            placeholder="Comment" value="{{ $resp->comment ?? '' }}"
                                            class="col-span-3 rounded border-gray-300 dark:bg-gray-900" />
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end">
                                <button class="px-4 py-2 bg-blue-600 text-white text-xs rounded">Save Responses</button>
                            </div>
                        </form>
                        @else
                        <p class="text-sm text-gray-500">No checklist to respond.</p>
                        @endif
                    </div>
                </div>

                <!-- Risks Tab -->
                <div id="content-risks" class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-4">Risks ({{ $audit->risks->count() }})</h3>
                    @if($audit->risks->count())
                    <ul class="text-sm space-y-2">
                        @foreach($audit->risks as $risk)
                        <li class="border-b pb-1">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ $risk->title }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">{{
                                    ucfirst($risk->risk_level) }}</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">L: {{ ucfirst($risk->likelihood) }} |
                                I:
                                {{ ucfirst($risk->impact) }} | Status: {{ ucfirst($risk->status) }}</div>
                            @if($risk->description)
                            <p class="text-xs mt-1 text-gray-700 dark:text-gray-300">{{ $risk->description }}</p>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No risks recorded.</p>
                    @endif
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
                                                    <li class="text-[10px]">{{ Str::limit($up->update_text,60) }} <span
                                                            class="text-gray-400">{{ $up->created_at?->diffForHumans()
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
                                <li class="border-b pb-1"><span class="font-medium">{{ $sc->scope_item }}</span> - <span
                                        class="text-gray-600">{{ $sc->is_in_scope ? 'In Scope' : 'Out of Scope'
                                        }}</span></li>
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
                            <h3 class="text-lg font-semibold mb-4">Child Audits ({{ $audit->children->count() }})</h3>
                            @if($audit->children->count())
                            <ul class="text-sm space-y-1">
                                @foreach($audit->children as $child)
                                <li><a href="{{ route('audits.show',$child) }}" class="text-blue-600 hover:underline">{{
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
                            <div class="text-gray-600">Value: {{ $m->metric_value ?? $m->numeric_value ?? '—' }}</div>
                            <div class="text-[10px] text-gray-400">Calculated: {{ $m->calculated_at?->diffForHumans() }}
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No metrics cached.</p>
                    @endif
                    <form method="POST" action="{{ route('audits.metrics.recalc',$audit) }}" class="mt-4">
                        @csrf
                        <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded">Recalculate Metrics</button>
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
                            <td class="px-3 py-2">{{ $h->from_status ? ucwords(str_replace('_',' ',$h->from_status)) :
                                '—' }}</td>
                            <td class="px-3 py-2 font-semibold">{{ ucwords(str_replace('_',' ',$h->to_status)) }}</td>
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
        function showTab(tabName) {
            // Hide all tab contents with fade effect
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
                content.style.opacity = '0';
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                button.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content with fade effect
            const selectedContent = document.getElementById('content-' + tabName);
            selectedContent.classList.remove('hidden');
            setTimeout(() => {
                selectedContent.style.opacity = '1';
            }, 10);
            
            // Add active class to selected tab button
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.add('active');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50', 'dark:bg-blue-900/30');
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