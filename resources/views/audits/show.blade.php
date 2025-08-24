<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Audit Details
                </h2>
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
				@switch($audit->status)
					@case('planned') bg-gray-100 text-gray-800 border border-gray-200 @break
					@case('in_progress') bg-blue-100 text-blue-800 border border-blue-200 @break
					@case('reporting') bg-indigo-100 text-indigo-800 border border-indigo-200 @break
					@case('issued') bg-green-100 text-green-800 border border-green-200 @break
					@case('closed') bg-emerald-100 text-emerald-800 border border-emerald-200 @break
					@case('cancelled') bg-red-100 text-red-800 border border-red-200 @break
					@default bg-gray-100 text-gray-800 border border-gray-200
				@endswitch">
                    {{ Str::headline($audit->status) }}
                </span>
                @if($audit->risk_overall)
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
				@switch($audit->risk_overall)
					@case('low') bg-green-100 text-green-800 border border-green-200 @break
					@case('medium') bg-yellow-100 text-yellow-800 border border-yellow-200 @break
					@case('high') bg-orange-100 text-orange-800 border border-orange-200 @break
					@case('critical') bg-red-100 text-red-800 border border-red-200 @break
					@default bg-gray-100 text-gray-800 border border-gray-200
				@endswitch">
                    Risk: {{ ucfirst($audit->risk_overall) }}
                </span>
                @endif
                @if($audit->is_template)
                <span
                    class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200">Template</span>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('audits.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                <button id="audit-structured-pdf-btn" type="button"
                    class="inline-flex items-center px-4 py-2 bg-indigo-700 hover:bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    title="Generate printable structured Audit PDF">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v12m0 0l-3.5-3.5M12 16l3.5-3.5M6 20h12" />
                    </svg>
                    Download Structured PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-status-message />

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
                                <h3 class="text-xl font-bold text-white">{{ $audit->title }}</h3>
                                <p class="text-blue-100 font-mono">{{ $audit->reference_no }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm opacity-90">Created</div>
                            <div class="text-white text-lg font-bold">{{ $audit->created_at->format('M d, Y') }}</div>
                            <div class="text-blue-100 text-sm">{{ $audit->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Readability Overrides: enforce 14px font size and black text color across all tab panels -->
                    <style>
                        /* Scope to this page only */
                        .tab-content {
                            font-size: 14px !important;
                            color: #000 !important;
                        }

                        /* Apply to common inline text elements */
                        .tab-content p,
                        .tab-content span,
                        .tab-content li,
                        .tab-content td,
                        .tab-content th,
                        .tab-content label,
                        .tab-content input,
                        .tab-content textarea,
                        .tab-content select,
                        .tab-content button,
                        .tab-content a,
                        .tab-content div {
                            font-size: 14px !important;
                        }

                        /* Ensure form controls inherit black text */
                        .tab-content input,
                        .tab-content textarea,
                        .tab-content select {
                            color: #000 !important;
                        }
                    </style>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
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
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 min-h-[60px]">
                                    @if($audit->description)
                                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{
                                        $audit->description }}</p>
                                    @else
                                    <p class="text-gray-400 italic text-sm">No description added yet.</p>
                                    @endif
                                </div>
                            </div>
                            <div
                                class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14v7m-5-3h10" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Scope Summary</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 min-h-[60px]">
                                    @if($audit->scope_summary)
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $audit->scope_summary }}</p>
                                    @else
                                    <p class="text-gray-400 italic text-sm">No scope summary added yet.</p>
                                    @endif
                                </div>
                            </div>

                            @if($audit->status === 'closed')
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Finalization</h4>
                                </div>
                                <div
                                    class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 space-y-2 text-sm text-gray-700">
                                    <div><span class="font-medium">Actual End:</span> {{
                                        optional($audit->actual_end_date)->format('M d, Y') ?? '—' }}</div>
                                    <div><span class="font-medium">Score:</span> {{ $audit->score ?? '—' }}</div>
                                    <div><span class="font-medium">Total Findings:</span> {{ $audit->findings->count()
                                        }}</div>
                                    <div><span class="font-medium">Open Actions:</span> {{
                                        $audit->actions->where('status','!=','completed')->count() }}</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="space-y-6">
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
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm"><span
                                            class="font-medium text-gray-600">Type</span><span>{{ $audit->type?->name ??
                                            '—' }}</span></div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm"><span
                                            class="font-medium text-gray-600">Lead Auditor</span><span>{{
                                            $audit->leadAuditor?->name ?? '—' }}</span></div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm"><span
                                            class="font-medium text-gray-600">Auditee</span><span>{{
                                            $audit->auditeeUser?->name ?? '—' }}</span></div>
                                    <div class="grid grid-cols-2 gap-2 pt-2">
                                        <div class="bg-white p-2 rounded text-center border border-gray-100">
                                            <div class="text-[10px] uppercase text-gray-500">Planned Start</div>
                                            <div class="text-xs font-semibold text-indigo-600">{{
                                                optional($audit->planned_start_date)->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="bg-white p-2 rounded text-center border border-gray-100">
                                            <div class="text-[10px] uppercase text-gray-500">Planned End</div>
                                            <div class="text-xs font-semibold text-indigo-600">{{
                                                optional($audit->planned_end_date)->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="bg-white p-2 rounded text-center border border-gray-100">
                                            <div class="text-[10px] uppercase text-gray-500">Actual Start</div>
                                            <div class="text-xs font-semibold text-indigo-600">{{
                                                optional($audit->actual_start_date)->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="bg-white p-2 rounded text-center border border-gray-100">
                                            <div class="text-[10px] uppercase text-gray-500">Actual End</div>
                                            <div class="text-xs font-semibold text-indigo-600">{{
                                                optional($audit->actual_end_date)->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm"><span
                                            class="font-medium text-gray-600">Score</span><span>{{ $audit->score ?? '—'
                                            }}</span></div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="bg-white p-2 rounded text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase">Findings</div>
                                            <div class="text-sm font-semibold text-indigo-600">{{
                                                $audit->findings->count() }}</div>
                                        </div>
                                        <div class="bg-white p-2 rounded text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase">Actions</div>
                                            <div class="text-sm font-semibold text-indigo-600">{{
                                                $audit->actions->count() }}</div>
                                        </div>
                                        <div class="bg-white p-2 rounded text-center shadow-sm border border-gray-100">
                                            <div class="text-[10px] text-gray-500 uppercase">Risks</div>
                                            <div class="text-sm font-semibold text-indigo-600">{{ $audit->risks->count()
                                                }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($audit->metrics->count())
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Metrics</h4>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    @foreach($audit->metrics->take(6) as $metric)
                                    <div class="bg-white rounded p-2 border border-gray-100 shadow-sm">
                                        <div class="font-medium text-gray-700 truncate">{{
                                            Str::headline($metric->metric_key) }}</div>
                                        <div class="text-indigo-600 font-bold text-sm">{{ $metric->metric_value ??
                                            $metric->numeric_value ?? '—' }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6 overflow-x-auto" aria-label="Tabs">
                        <button
                            class="tab-button border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600"
                            data-tab="history">History & Timeline ({{ $audit->statusHistories->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="auditors">Auditors ({{ $audit->auditors->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="scopes">Scopes ({{ $audit->scopes->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="checklist">Checklist ({{ ($assessmentItems ?? collect())->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="findings">Findings ({{ $audit->findings->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="actions">Actions ({{ $audit->actions->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="documents">Documents ({{ $audit->documents->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="risks">Risks ({{ $audit->risks->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="schedules">Schedules ({{ $audit->schedules->count() }})</button>
                        <!-- Removed notifications, schedules, tags tabs moved to Operations -->
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="metrics">Metrics ({{ $audit->metrics->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="operations">Operations</button>
                    </nav>
                </div>

                <div id="history-tab" class="tab-content p-3">
                    <div class="relative">
                        <div
                            class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-indigo-300 via-gray-200 to-transparent pointer-events-none">
                        </div>
                        <ul class="space-y-6">
                            @php $__seq = $statusHistory->count(); @endphp
                            @forelse($statusHistory as $h)
                            <li class="relative pl-12 group">
                                <span class="absolute left-0 flex items-center justify-center w-8 h-8 rounded-full ring-4 ring-white shadow-sm
										@switch($h->to_status)
											@case('planned') bg-gray-500 text-white @break
											@case('in_progress') bg-blue-600 text-white @break
											@case('reporting') bg-indigo-600 text-white @break
											@case('issued') bg-green-600 text-white @break
											@case('closed') bg-emerald-600 text-white @break
											@case('cancelled') bg-red-600 text-white @break
											@default bg-gray-600 text-white
										@endswitch">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <div
                                    class="bg-white/70 backdrop-blur-sm border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            @if($h->from_status && $h->from_status !== $h->to_status)
                                            {{ Str::headline($h->from_status) }} → {{ Str::headline($h->to_status) }}
                                            @else
                                            {{ Str::headline($h->to_status) }}
                                            @endif
                                        </h4>
                                        <time class="text-xs text-gray-500">{{ optional($h->changed_at)->format('M d, Y
                                            H:i') }}</time>
                                    </div>
                                    @if($h->note)
                                    <p class="mt-2 text-sm text-gray-600 leading-snug">{{ $h->note }}</p>
                                    @endif
                                    <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500">
                                        <span>by {{ $h->changer?->name ?? 'System' }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">#{{ $__seq
                                            }}</span>
                                    </div>
                                </div>
                            </li>
                            @php $__seq--; @endphp
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

                <div id="auditors-tab" class="tab-content p-3" style="display:none;">
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                            <div class="relative overflow-x-auto rounded-lg">
                                <table class="min-w-max w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-green-800 text-white uppercase text-sm">
                                            <th class="py-3 px-2 text-center">#</th>
                                            <th class="py-3 px-2 text-left">Auditor</th>
                                            <th class="py-3 px-2 text-center">Role</th>
                                            <th class="py-3 px-2 text-center">Primary</th>
                                            <th class="py-3 px-2 text-center">Added</th>
                                            <th class="py-3 px-2 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black text-sm leading-normal">
                                        @forelse($audit->auditors->sortBy('created_at') as $idx => $aud)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="py-3 px-2 text-center font-semibold">{{ $idx+1 }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-800 w-64">
                                                <div>{{ $aud->user?->name ?? '—' }}</div>
                                                <div class="text-[11px] text-gray-500 mt-1">{{ $aud->user?->email ?? '—'
                                                    }}</div>
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs capitalize">{{ $aud->role }}</td>
                                            <td class="py-3 px-2 text-center text-xs">
                                                @if($aud->is_primary)
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-semibold">Yes</span>
                                                @else
                                                <span class="text-gray-500 text-xs">No</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                $aud->created_at?->format('d-m-Y') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">
                                                <form method="POST"
                                                    action="{{ route('audits.auditors.delete', [$audit, $aud]) }}"
                                                    onsubmit="return confirm('Remove auditor?')" class="inline-block">
                                                    @csrf @method('DELETE')
                                                    <button
                                                        class="px-2 py-1 text-white bg-red-600 hover:bg-red-700 rounded-md text-[11px] font-semibold">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="py-6 px-4 text-center text-gray-500">No auditors
                                                assigned.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                        <form method="POST" action="{{ route('audits.assign-auditors', $audit) }}"
                            class="grid md:grid-cols-6 gap-4">@csrf
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold mb-1">User</label>
                                <select name="user_id" required class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">— Select —</option>
                                    @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1">Role</label>
                                <select name="role" class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="lead">Lead</option>
                                    <option value="member" selected>Member</option>
                                    <option value="observer">Observer</option>
                                </select>
                            </div>
                            <div class="flex items-center pt-5">
                                <label
                                    class="inline-flex items-center text-xs font-medium text-gray-700 select-none cursor-pointer">
                                    <input type="checkbox" name="is_primary" value="1"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">Primary</span>
                                </label>
                            </div>
                            <div class="md:col-span-2 flex items-end justify-end">
                                <button
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold w-full md:w-auto">Save
                                    Auditor</button>
                            </div>
                            <div class="md:col-span-6 -mt-2">
                                <p class="text-[11px] text-gray-500">Primary flag is unique. Changes are logged.</p>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="scopes-tab" class="tab-content p-3" style="display:none;">
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                            <div class="relative overflow-x-auto rounded-lg">
                                <table class="min-w-max w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-green-800 text-white uppercase text-sm">
                                            <th class="py-3 px-2 text-center">#</th>
                                            <th class="py-3 px-2 text-left">Item</th>
                                            <th class="py-3 px-2 text-center">In Scope</th>
                                            <th class="py-3 px-2 text-left">Description</th>
                                            <th class="py-3 px-2 text-center">Added</th>
                                            <th class="py-3 px-2 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black text-sm leading-normal">
                                        @forelse($audit->scopes->sortBy('created_at') as $idx => $scope)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 align-top">
                                            <td class="py-3 px-2 text-center font-semibold">{{ $idx+1 }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-800 w-56">{{ $scope->scope_item
                                                }}</td>
                                            <td class="py-3 px-2 text-center text-xs">
                                                @if($scope->is_in_scope)
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-semibold">Yes</span>
                                                @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-semibold">No</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-xs text-gray-700 w-80">
                                                @if($scope->description)
                                                {{ Str::limit($scope->description,160) }}
                                                @else
                                                <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                $scope->created_at?->format('d-m-Y') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">
                                                <form method="POST"
                                                    action="{{ route('audits.scopes.delete', [$audit, $scope]) }}"
                                                    onsubmit="return confirm('Delete scope item?')"
                                                    class="inline-block">@csrf @method('DELETE')
                                                    <button
                                                        class="px-2 py-1 text-white bg-red-600 hover:bg-red-700 rounded-md text-[11px] font-semibold">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="py-6 px-4 text-center text-gray-500">No scope items
                                                defined.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                        <form method="POST" action="{{ route('audits.scopes.add', $audit) }}"
                            class="grid md:grid-cols-6 gap-4">@csrf
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold mb-1">Scope Item</label>
                                <input name="scope_item" required placeholder="e.g. Branch cash processes"
                                    class="w-full border-gray-300 rounded-md text-sm" />
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold mb-1">Description</label>
                                <textarea name="description" rows="2" placeholder="Optional details"
                                    class="w-full border-gray-300 rounded-md text-sm"></textarea>
                            </div>
                            <div class="flex items-center pt-5">
                                <label
                                    class="inline-flex items-center text-xs font-medium text-gray-700 cursor-pointer select-none">
                                    <input type="checkbox" name="is_in_scope" value="1" checked
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="ml-2">In Scope</span>
                                </label>
                            </div>
                            <div class="md:col-span-6 flex justify-end pt-1">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold">Add
                                    Scope</button>
                            </div>
                            <div class="md:col-span-6 -mt-2">
                                <p class="text-[11px] text-gray-500">Scope changes are logged for reference.</p>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="checklist-tab" class="tab-content p-3" style="display:none;">
                    @php($items = $assessmentItems ?? collect())
                    @php($inlineItems = ($items)->filter(fn($i)=> ($i->metadata['inline_for_audit'] ?? null) ===
                    $audit->id))
                    @php($responsesByItem = $audit->responses?->groupBy('audit_checklist_item_id') ?? collect())
                    <div class="space-y-8">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-gray-800 tracking-wide">Assessment Items & Evidence
                            </h3>
                            <span class="text-[11px] text-gray-500">{{ $items->count() }} items • {{
                                $responsesByItem->count() }} responded</span>
                        </div>
                        @if($items->count())
                        <form method="POST" action="{{ route('audits.save-responses', $audit) }}" class="space-y-6">
                            @csrf
                            <div class="grid gap-5 lg:grid-cols-2">
                                @foreach($items as $item)
                                @php($respSet = $responsesByItem->get($item->id) ?? collect())
                                @php($resp = $respSet->first())
                                <div
                                    class="group relative rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="min-w-0">
                                            <h5 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                                <span>{{ $item->reference_code ? '['.$item->reference_code.'] ' : ''
                                                    }}{{ $item->title }}</span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-50 text-indigo-700">{{
                                                    Str::headline($item->response_type) }}</span>
                                            </h5>
                                            @if($item->criteria)
                                            <p class="mt-1 text-[11px] text-gray-600 line-clamp-2">{{
                                                Str::limit($item->criteria, 180) }}</p>
                                            @endif
                                        </div>
                                        @if($item->max_score)
                                        <span
                                            class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600">Max:
                                            {{ $item->max_score }}</span>
                                        @endif
                                    </div>
                                    @if($item->guidance)
                                    <details class="mb-3">
                                        <summary
                                            class="cursor-pointer text-[11px] text-indigo-600 hover:text-indigo-700">
                                            Guidance</summary>
                                        <div class="mt-1 rounded bg-indigo-50/60 p-2 text-[11px] text-indigo-800">{{
                                            Str::limit($item->guidance, 400) }}</div>
                                    </details>
                                    @endif
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3 text-[11px] text-gray-500">
                                            <span>Last: {{ $resp?->responded_at?->diffForHumans() ?? '—' }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span>{{ $resp ? 'By '.$resp->responder?->name : 'No response yet' }}</span>
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-12 items-end">
                                            <div class="sm:col-span-3 space-y-1">
                                                <label
                                                    class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide">Response</label>
                                                <select name="responses[{{ $item->id }}][response_value]"
                                                    class="w-full rounded-md border-gray-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">—</option>
                                                    @php($opts =
                                                    ['yes'=>'Yes','no'=>'No','compliant'=>'Compliant','noncompliant'=>'Non-Compliant','na'=>'N/A'])
                                                    @foreach($opts as $val => $lbl)
                                                    <option value="{{ $val }}" @if($resp && $resp->
                                                        response_value===$val) selected @endif>{{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="sm:col-span-2 space-y-1">
                                                <label
                                                    class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide">Score</label>
                                                <input type="number" step="0.01"
                                                    name="responses[{{ $item->id }}][score]"
                                                    value="{{ $resp->score ?? '' }}"
                                                    class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500" />
                                            </div>
                                            <div class="sm:col-span-7 space-y-1">
                                                <label
                                                    class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide">Comment</label>
                                                <input name="responses[{{ $item->id }}][comment]"
                                                    value="{{ $resp->comment ?? '' }}"
                                                    placeholder="Observation / evidence note"
                                                    class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="group inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                    <svg class="h-4 w-4 text-indigo-200 group-hover:text-white transition" fill="none"
                                        stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Save Responses</span>
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-500">Responses update audit score (if scoring defined).</p>
                        </form>
                        @php($inlineItems = ($items)->filter(fn($i)=> ($i->metadata['inline_for_audit'] ?? null) ===
                        $audit->id))
                        <div class="mt-10">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xs font-semibold tracking-wide text-gray-700 uppercase">Inline Items
                                    (Custom for this Audit)</h4><span class="text-[10px] text-gray-400">{{
                                    $inlineItems->count() }}</span>
                            </div>
                            @if($inlineItems->count())
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach($inlineItems as $item)
                                <div class="relative rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                    <div class="flex justify-between items-start gap-3 mb-1">
                                        <h5 class="text-sm font-semibold text-gray-800">{{ $item->title }}</h5>
                                        <form method="POST"
                                            action="{{ route('audits.inline-items.delete', [$audit, $item]) }}"
                                            onsubmit="return confirm('Delete custom item?')">@csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700 text-xs"
                                                type="submit">&times;</button>
                                        </form>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mb-2">Type: {{
                                        Str::headline($item->response_type) }} @if($item->max_score) • Max {{
                                        $item->max_score }} @endif</div>
                                    @if($item->criteria)<div class="text-[11px] text-gray-600 line-clamp-3">{{
                                        Str::limit($item->criteria,160) }}</div>@endif
                                    @if($item->guidance)<div class="mt-2 text-[10px] text-indigo-600 line-clamp-3">{{
                                        Str::limit($item->guidance,160) }}</div>@endif
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-[11px] text-gray-500">No inline items added yet.</p>
                            @endif
                            <div class="relative mt-6">
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-100/40 via-white to-indigo-50 pointer-events-none">
                                </div>
                                <div class="relative rounded-xl border border-indigo-200/70 shadow-sm overflow-hidden">
                                    <div
                                        class="px-5 pt-5 pb-3 flex items-center justify-between bg-gradient-to-r from-white to-indigo-50/60">
                                        <div>
                                            <h5 class="text-sm font-semibold text-indigo-900">Add Inline Item</h5>
                                            <p class="mt-1 text-[11px] text-indigo-600/80">Create an ad-hoc assessment
                                                point for this audit only.</p>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('audits.inline-items.add', $audit) }}"
                                        class="p-5 grid gap-4 md:grid-cols-12">@csrf
                                        <div class="md:col-span-4 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Title</label>
                                            <input name="title" required
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="e.g. Cash count evidence" />
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Type</label>
                                            <select name="response_type"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="yes_no">Yes / No</option>
                                                <option value="compliant_noncompliant">Compliant / Noncompliant</option>
                                                <option value="rating">Rating</option>
                                                <option value="text">Text</option>
                                                <option value="numeric">Numeric</option>
                                                <option value="evidence">Evidence</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Max
                                                Score</label>
                                            <input type="number" name="max_score" min="0" max="100"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional" />
                                        </div>
                                        <div class="md:col-span-4 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Criteria</label>
                                            <input name="criteria"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional criteria" />
                                        </div>
                                        <div class="md:col-span-12 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Guidance</label>
                                            <textarea name="guidance" rows="2"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional guidance text"></textarea>
                                        </div>
                                        <div class="md:col-span-12 flex justify-end pt-2">
                                            <button
                                                class="group inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                                <svg class="h-4 w-4 text-indigo-200 group-hover:text-white transition"
                                                    fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Add Item</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-10 text-center">
                            <div
                                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white shadow">
                                <svg class="h-7 w-7 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 13h6m-7 4h8M5 8h14M4 6l1.5 12.5A2 2 0 007.48 20h9.04a2 2 0 001.98-1.5L20 6" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">No assessment items for this audit type</p>
                            <p class="mt-1 text-xs text-gray-500">Define checklist items under audit type configuration.
                            </p>
                        </div>
                        <!-- Inline items area even when no type-based items -->
                        <div class="mt-8">
                            <h4 class="text-xs font-semibold tracking-wide text-gray-700 uppercase mb-3">Inline Items
                                (Custom)</h4>
                            @if($inlineItems->count())
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 mb-8">
                                @foreach($inlineItems as $item)
                                <div class="relative rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                    <div class="flex justify-between items-start gap-3 mb-1">
                                        <h5 class="text-sm font-semibold text-gray-800">{{ $item->title }}</h5>
                                        <form method="POST"
                                            action="{{ route('audits.inline-items.delete', [$audit, $item]) }}"
                                            onsubmit="return confirm('Delete custom item?')">@csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700 text-xs"
                                                type="submit">&times;</button>
                                        </form>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mb-2">Type: {{
                                        Str::headline($item->response_type) }} @if($item->max_score) • Max {{
                                        $item->max_score }} @endif</div>
                                    @if($item->criteria)<div class="text-[11px] text-gray-600 line-clamp-3">{{
                                        Str::limit($item->criteria,160) }}</div>@endif
                                    @if($item->guidance)<div class="mt-2 text-[10px] text-indigo-600 line-clamp-3">{{
                                        Str::limit($item->guidance,160) }}</div>@endif
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-[11px] text-gray-500">No inline items added yet.</p>
                            @endif
                            <div class="relative">
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-100/40 via-white to-indigo-50 pointer-events-none">
                                </div>
                                <div class="relative rounded-xl border border-indigo-200/70 shadow-sm overflow-hidden">
                                    <div
                                        class="px-5 pt-5 pb-3 flex items-center justify-between bg-gradient-to-r from-white to-indigo-50/60">
                                        <div>
                                            <h5 class="text-sm font-semibold text-indigo-900">Add Inline Item</h5>
                                            <p class="mt-1 text-[11px] text-indigo-600/80">Create an ad-hoc checklist
                                                entry for this audit.</p>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('audits.inline-items.add', $audit) }}"
                                        class="p-5 grid gap-4 md:grid-cols-12">@csrf
                                        <div class="md:col-span-4 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Title</label>
                                            <input name="title" required
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="e.g. Cash count evidence" />
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Type</label>
                                            <select name="response_type"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="yes_no">Yes / No</option>
                                                <option value="compliant_noncompliant">Compliant / Noncompliant</option>
                                                <option value="rating">Rating</option>
                                                <option value="text">Text</option>
                                                <option value="numeric">Numeric</option>
                                                <option value="evidence">Evidence</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Max
                                                Score</label>
                                            <input type="number" name="max_score" min="0" max="100"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional" />
                                        </div>
                                        <div class="md:col-span-4 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Criteria</label>
                                            <input name="criteria"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional criteria" />
                                        </div>
                                        <div class="md:col-span-12 space-y-1">
                                            <label
                                                class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wide">Guidance</label>
                                            <textarea name="guidance" rows="2"
                                                class="w-full rounded-md border-indigo-300 bg-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Optional guidance text"></textarea>
                                        </div>
                                        <div class="md:col-span-12 flex justify-end pt-2">
                                            <button
                                                class="group inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                                <svg class="h-4 w-4 text-indigo-200 group-hover:text-white transition"
                                                    fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Add Item</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="findings-tab" class="tab-content p-3" style="display:none;">
                    <div class="space-y-8">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-800">Findings ({{ $audit->findings->count() }})
                            </h4>
                            <button onclick="document.getElementById('addFindingPanel').classList.toggle('hidden')"
                                class="text-xs px-3 py-1.5 rounded-md bg-indigo-600 text-white">New Finding</button>
                        </div>
                        <div id="addFindingPanel"
                            class="hidden border border-indigo-200 rounded-xl bg-gradient-to-br from-indigo-50/80 via-white to-indigo-50 p-5 shadow-sm">
                            <h5 class="text-sm font-semibold text-indigo-900 mb-3">Add Finding</h5>
                            <form method="POST" action="{{ route('audits.findings.add', $audit) }}"
                                enctype="multipart/form-data" class="space-y-4 text-sm">@csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Title</label>
                                    <input name="title" required class="w-full rounded-md border-indigo-300 px-3 py-2"
                                        placeholder="Finding title" />
                                </div>
                                <div class="grid md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Category</label>
                                        <select name="category" class="w-full rounded-md border-indigo-300 px-2 py-2">
                                            <option value="">Select</option>
                                            @foreach(['process','compliance','safety','financial','operational','other']
                                            as $c)
                                            <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Severity</label>
                                        <select name="severity" class="w-full rounded-md border-indigo-300 px-2 py-2">
                                            <option value="">Select</option>
                                            @foreach(['low','medium','high','critical'] as $s)
                                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                                        <select name="status" class="w-full rounded-md border-indigo-300 px-2 py-2">
                                            @foreach(['open','in_progress','implemented','verified','closed','void'] as
                                            $st)
                                            <option value="{{ $st }}">{{ Str::headline($st) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Owner</label>
                                        <select name="owner_user_id"
                                            class="w-full rounded-md border-indigo-300 px-2 py-2">
                                            <option value="">Select</option>
                                            @foreach($allUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="3"
                                        class="w-full rounded-md border-indigo-300 px-3 py-2"
                                        placeholder="Description"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Risk
                                        Description</label>
                                    <textarea name="risk_description" rows="3"
                                        class="w-full rounded-md border-indigo-300 px-3 py-2"
                                        placeholder="Risk description"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Root Cause</label>
                                    <textarea name="root_cause" rows="3"
                                        class="w-full rounded-md border-indigo-300 px-3 py-2"
                                        placeholder="Root cause"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Recommendation</label>
                                    <textarea name="recommendation" rows="3"
                                        class="w-full rounded-md border-indigo-300 px-3 py-2"
                                        placeholder="Recommendation"></textarea>
                                </div>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Target Closure
                                            Date (dd-mm-yyyy)</label>
                                        <input type="date" name="target_closure_date"
                                            class="w-full rounded-md border-indigo-300 px-3 py-2 tracking-wider" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Actual Closure
                                            Date (dd-mm-yyyy)</label>
                                        <input type="date" name="actual_closure_date"
                                            class="w-full rounded-md border-indigo-300 px-3 py-2 tracking-wider" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Attachments</label>
                                    <input type="file" name="attachments[]" multiple class="w-full text-xs" />
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button
                                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-md font-semibold text-sm">Save
                                        Finding</button>
                                </div>
                            </form>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            @forelse($audit->findings as $finding)
                            <div
                                class="group relative rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden">
                                <div
                                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-400 via-indigo-600 to-indigo-400 opacity-60">
                                </div>
                                <div class="p-4 space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span
                                                    class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700">{{
                                                    $finding->reference_no ?? Str::upper(Str::substr($finding->id,0,6))
                                                    }}</span>
                                                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-semibold @class([
                                                        'bg-green-100 text-green-700'=> $finding->severity==='low',
                                                        'bg-yellow-100 text-yellow-700'=> $finding->severity==='medium',
                                                        'bg-orange-100 text-orange-700'=> $finding->severity==='high',
                                                        'bg-red-100 text-red-700'=> $finding->severity==='critical',
                                                        'bg-gray-100 text-gray-600'=> !$finding->severity,
                                                    ])">{{ ucfirst($finding->severity ?? 'n/a') }}</span>
                                                <span
                                                    class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-medium bg-indigo-100 text-indigo-700">{{
                                                    Str::headline($finding->status ?? 'n/a') }}</span>
                                            </div>
                                            <h5 class="text-sm font-semibold text-gray-800 leading-snug break-words">{{
                                                $finding->title }}</h5>
                                        </div>
                                        <div class="flex flex-col items-end gap-1">
                                            <button onclick="toggleFindingEdit('{{ $finding->id }}')"
                                                class="text-[11px] text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                                        </div>
                                    </div>
                                    @if($finding->description)<p
                                        class="text-[11px] text-gray-600 leading-relaxed line-clamp-4">{{
                                        Str::limit($finding->description,300) }}</p>@endif
                                    @if($finding->attachments->count())
                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                        @foreach($finding->attachments->take(4) as $att)
                                        <a href="{{ route('audits.findings.attachments.download', [$audit,$finding,$att]) }}"
                                            class="group inline-flex items-center max-w-full px-2 py-1 rounded-md border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-[10px] font-medium text-indigo-700 shadow-sm"
                                            title="Download {{ $att->original_name }}">
                                            <svg class="w-3.5 h-3.5 mr-1 text-indigo-500 group-hover:text-indigo-700"
                                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M8 12l4 4m0 0l4-4m-4 4V4" />
                                            </svg>
                                            <span class="truncate max-w-[90px]" style="direction:ltr">{{
                                                Str::limit($att->original_name, 22) }}</span>
                                        </a>
                                        @endforeach
                                        @if($finding->attachments->count() > 4)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-gray-600 text-[10px] font-medium border border-gray-200">+{{
                                            $finding->attachments->count()-4 }} more</span>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="grid grid-cols-2 gap-2 text-[10px] text-gray-500">
                                        <div><span class="font-semibold text-gray-700">Category:</span> {{
                                            ucfirst($finding->category ?? '—') }}</div>
                                        <div><span class="font-semibold text-gray-700">Owner:</span> {{
                                            $finding->owner?->name ?? '—' }}</div>
                                        <div><span class="font-semibold text-gray-700">Target:</span> {{
                                            optional($finding->target_closure_date)->format('d-m-Y') ?? '—' }}</div>
                                        <div><span class="font-semibold text-gray-700">Actual:</span> {{
                                            optional($finding->actual_closure_date)->format('d-m-Y') ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-black pt-1 border-t font-medium">
                                        <div>Attachments {{ $finding->attachments->count() }}</div>
                                    </div>
                                    <div id="finding-edit-{{ $finding->id }}"
                                        class="hidden mt-3 border rounded-lg bg-indigo-50/50 p-3">
                                        <form method="POST"
                                            action="{{ route('audits.findings.update', [$audit,$finding]) }}"
                                            class="space-y-3">@csrf @method('PATCH')
                                            <div class="grid md:grid-cols-12 gap-3 text-[11px]">
                                                <input name="title" value="{{ $finding->title }}" required
                                                    class="md:col-span-12 lg:col-span-8 rounded-md border-indigo-300"
                                                    placeholder="Title" />
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:col-span-12">
                                                    <select name="category"
                                                        class="rounded-md border-indigo-300 text-[11px]">
                                                        <option value="">Category</option>
                                                        @foreach(['process','compliance','safety','financial','operational','other']
                                                        as $c)
                                                        <option value="{{ $c }}" @selected($finding->category==$c)>{{
                                                            ucfirst($c) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="severity"
                                                        class="rounded-md border-indigo-300 text-[11px]">
                                                        <option value="">Severity</option>
                                                        @foreach(['low','medium','high','critical'] as $s)
                                                        <option value="{{ $s }}" @selected($finding->severity==$s)>{{
                                                            ucfirst($s) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="status"
                                                        class="rounded-md border-indigo-300 text-[11px]">
                                                        @foreach(['open','in_progress','implemented','verified','closed','void']
                                                        as $st)
                                                        <option value="{{ $st }}" @selected($finding->status==$st)>{{
                                                            Str::headline($st) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="owner_user_id"
                                                        class="rounded-md border-indigo-300 text-[11px]">
                                                        <option value="">Owner</option>
                                                        @foreach($allUsers as $u)
                                                        <option value="{{ $u->id }}" @selected($finding->
                                                            owner_user_id==$u->id)>{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <textarea name="description" rows="2"
                                                    class="md:col-span-12 rounded-md border-indigo-300"
                                                    placeholder="Description">{{ $finding->description }}</textarea>
                                                <textarea name="risk_description" rows="2"
                                                    class="md:col-span-12 rounded-md border-indigo-300"
                                                    placeholder="Risk Description">{{ $finding->risk_description }}</textarea>
                                                <textarea name="root_cause" rows="2"
                                                    class="md:col-span-12 rounded-md border-indigo-300"
                                                    placeholder="Root Cause">{{ $finding->root_cause }}</textarea>
                                                <textarea name="recommendation" rows="2"
                                                    class="md:col-span-12 rounded-md border-indigo-300"
                                                    placeholder="Recommendation">{{ $finding->recommendation }}</textarea>
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:col-span-12">
                                                    <div>
                                                        <label
                                                            class="block text-[10px] font-semibold text-gray-600 mb-1">Target</label>
                                                        <input type="date" name="target_closure_date"
                                                            value="{{ optional($finding->target_closure_date)->format('Y-m-d') }}"
                                                            class="w-full rounded-md border-indigo-300" />
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[10px] font-semibold text-gray-600 mb-1">Actual</label>
                                                        <input type="date" name="actual_closure_date"
                                                            value="{{ optional($finding->actual_closure_date)->format('Y-m-d') }}"
                                                            class="w-full rounded-md border-indigo-300" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex justify-end space-x-2 pt-1">
                                                <button type="button" onclick="toggleFindingEdit('{{ $finding->id }}')"
                                                    class="px-3 py-1 rounded-md bg-gray-200">Cancel</button>
                                                <button
                                                    class="px-3 py-1 rounded-md bg-indigo-600 text-white">Save</button>
                                            </div>
                                        </form>
                                        <div class="mt-4 border-t pt-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <h6 class="text-[11px] font-semibold text-gray-700">Attachments ({{
                                                    $finding->attachments->count() }})</h6>
                                                <form method="POST"
                                                    action="{{ route('audits.findings.attachments.add', [$audit,$finding]) }}"
                                                    enctype="multipart/form-data"
                                                    class="flex items-center gap-2 text-[11px]">@csrf
                                                    <input type="file" name="file" required multiple
                                                        class="text-[10px]" />
                                                    <button
                                                        class="px-2 py-1 bg-indigo-600 text-white rounded">Upload</button>
                                                </form>
                                            </div>
                                            <div class="grid gap-2 md:grid-cols-2">
                                                @forelse($finding->attachments as $att)
                                                <div class="flex items-start gap-3 p-2 rounded-md border bg-white">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-[11px] font-medium text-gray-800 truncate">{{
                                                            $att->original_name }}</div>
                                                        <div class="text-[10px] text-gray-500">{{ $att->mime_type ??
                                                            'n/a' }} • {{ number_format(($att->size_bytes ?? 0)/1024,1)
                                                            }} KB • {{ optional($att->uploaded_at)->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ route('audits.findings.attachments.download', [$audit,$finding,$att]) }}"
                                                            class="text-[10px] text-indigo-600 hover:underline">DL</a>
                                                    </div>
                                                </div>
                                                @empty
                                                <div class="text-[11px] text-gray-500">No attachments.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full text-sm text-gray-500">No findings recorded.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div id="actions-tab" class="tab-content p-3" style="display:none;">
                    <div class="space-y-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                            <div class="relative overflow-x-auto rounded-lg">
                                <table class="min-w-max w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-green-800 text-white uppercase text-sm">
                                            <th class="py-3 px-2 text-center">#</th>
                                            <th class="py-3 px-2 text-left">Action</th>
                                            <th class="py-3 px-2 text-center">Type</th>
                                            <th class="py-3 px-2 text-center">Priority</th>
                                            <th class="py-3 px-2 text-center">Status</th>
                                            <th class="py-3 px-2 text-center">Owner</th>
                                            <th class="py-3 px-2 text-center">Due</th>
                                            <th class="py-3 px-2 text-center">Created</th>
                                            <th class="py-3 px-2 text-center">Updates</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black text-sm leading-normal">
                                        @forelse($audit->actions->sortByDesc('created_at') as $i => $action)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 align-top">
                                            <td class="py-3 px-2 text-center font-semibold">{{ $i+1 }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-800 w-64">
                                                <div class="flex items-center gap-2 mb-1 flex-wrap text-[11px]">
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-semibold">{{
                                                        $action->reference_no ??
                                                        Str::upper(Str::substr($action->id,0,6)) }}</span>
                                                </div>
                                                <div>{{ $action->title }}</div>
                                                @if($action->description)
                                                <div class="text-[11px] text-gray-600 mt-1">{{
                                                    Str::limit($action->description,140) }}</div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                Str::headline($action->action_type) }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ ucfirst($action->priority ??
                                                '—') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ Str::headline($action->status)
                                                }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ $action->owner?->name ?? '—' }}
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                optional($action->due_date)->format('d-m-Y') ?? '—' }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                $action->created_at->format('d-m-Y') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">
                                                @if($action->updates->count())
                                                <div class="space-y-1 max-w-[240px] mx-auto text-left">
                                                    @foreach($action->updates->sortByDesc('created_at')->take(3) as
                                                    $upd)
                                                    <div
                                                        class="p-1.5 bg-gray-50 border rounded text-[10px] flex justify-between gap-2">
                                                        <div class="min-w-0"><span class="font-medium">{{
                                                                $upd->creator?->name ?? 'System' }}:</span> {{
                                                            Str::limit($upd->update_text, 70) }}</div>
                                                        <div class="shrink-0 text-gray-500">{{
                                                            $upd->created_at->format('d-m H:i') }}</div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @else
                                                <span class="text-gray-400 text-xs">—</span>
                                                @endif
                                                <form method="POST"
                                                    action="{{ route('audits.actions.updates.add', [$audit, $action]) }}"
                                                    class="mt-2 flex gap-1">@csrf
                                                    <input name="update_text" required placeholder="Add update"
                                                        class="flex-1 border-gray-300 rounded-md text-[10px] px-2 py-1" />
                                                    <select name="status_after"
                                                        class="border-gray-300 rounded-md text-[10px]">
                                                        <option value="">Status</option>
                                                        @foreach(['open','in_progress','implemented','verified','closed','cancelled']
                                                        as $st)
                                                        <option value="{{ $st }}">{{ Str::headline($st) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button
                                                        class="px-2 py-1 bg-indigo-600 text-white rounded text-[10px]">Save</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="py-6 px-4 text-center text-gray-500">No actions yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($audit->findings->count())
                        <div class="bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                            <h5 class="text-sm font-semibold text-gray-800 mb-3">Add Action</h5>
                            <form method="POST"
                                action="{{ route('audits.actions.add', [$audit, $audit->findings->first()]) }}"
                                class="grid md:grid-cols-6 gap-4">@csrf
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Finding</label>
                                    <select name="audit_finding_id" required
                                        class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">Select...</option>
                                        @foreach($audit->findings as $f)
                                        <option value="{{ $f->id }}">{{ $f->reference_no ??
                                            Str::upper(Str::substr($f->id,0,6)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Title</label>
                                    <input name="title" required class="w-full border-gray-300 rounded-md text-sm"
                                        placeholder="Action title" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Type</label>
                                    <select name="action_type" class="w-full border-gray-300 rounded-md text-sm">
                                        @foreach(['corrective','preventive','remediation','improvement'] as $t)
                                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Priority</label>
                                    <select name="priority" class="w-full border-gray-300 rounded-md text-sm">
                                        @foreach(['low','medium','high','critical'] as $p)
                                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Owner</label>
                                    <select name="owner_user_id" class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">—</option>
                                        @foreach($allUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Due Date</label>
                                    <input type="date" name="due_date"
                                        class="w-full border-gray-300 rounded-md text-sm" />
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-xs font-semibold mb-1">Description</label>
                                    <textarea name="description" rows="2"
                                        class="w-full border-gray-300 rounded-md text-sm"
                                        placeholder="Optional details"></textarea>
                                </div>
                                <div class="md:col-span-6 flex justify-end pt-1">
                                    <button
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold">Save
                                        Action</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="documents-tab" class="tab-content p-3" style="display:none;">

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                        <div class="relative overflow-x-auto rounded-lg">
                            <table class="min-w-max w-full table-auto text-sm">
                                <thead>
                                    <tr class="bg-green-800 text-white uppercase text-sm">
                                        <th class="py-3 px-2 text-center">#</th>
                                        <th class="py-3 px-2 text-left">Document</th>
                                        <th class="py-3 px-2 text-center">Type</th>
                                        <th class="py-3 px-2 text-center">Uploaded</th>
                                        <th class="py-3 px-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-black text-sm leading-normal">
                                    @forelse($audit->documents as $i => $doc)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2 text-center font-semibold">{{ $i+1 }}</td>
                                        <td class="py-3 px-2 font-medium text-gray-800">
                                            <div>{{ $doc->original_name }}</div>
                                            @if($doc->category)
                                            <div class="text-[11px] text-gray-500 mt-1">Category: {{ $doc->category }}
                                            </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-center text-xs">{{ $doc->mime_type ?? 'n/a' }}</td>
                                        <td class="py-3 px-2 text-center text-xs">{{ $doc->created_at->format('d-m-Y')
                                            }}</td>
                                        <td class="py-3 px-2 text-center text-xs">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('audits.documents.download', [$audit, $doc]) }}"
                                                    class="px-2 py-1 text-white bg-blue-600 hover:bg-blue-700 rounded-md text-[11px] font-semibold">Download</a>
                                                <form method="POST"
                                                    action="{{ route('audits.documents.delete', [$audit, $doc]) }}"
                                                    onsubmit="return confirm('Delete document?')">
                                                    @csrf @method('DELETE')
                                                    <button
                                                        class="px-2 py-1 text-white bg-red-600 hover:bg-red-700 rounded-md text-[11px] font-semibold">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-6 px-4 text-center text-gray-500">No documents
                                            uploaded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                            <form method="POST" action="{{ route('audits.documents.store', $audit) }}"
                                enctype="multipart/form-data" class="grid md:grid-cols-4 gap-4">@csrf
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold mb-1">Files</label>
                                    <input type="file" name="files[]" multiple required
                                        class="border-gray-300 rounded-md text-sm w-full" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Category</label>
                                    <input name="category" placeholder="Optional"
                                        class="border-gray-300 rounded-md text-sm w-full" />
                                </div>
                                <div class="md:col-span-1 flex items-end justify-end">
                                    <button
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold w-full md:w-auto">Upload</button>
                                </div>
                                <div class="md:col-span-4 -mt-2">
                                    <p class="text-[11px] text-gray-500">Stored at: private Complaints/{{
                                        $audit->reference_no }}/documents</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="risks-tab" class="tab-content p-3" style="display:none;">
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                            <div class="relative overflow-x-auto rounded-lg">
                                <table class="min-w-max w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-green-800 text-white uppercase text-sm">
                                            <th class="py-3 px-2 text-center">#</th>
                                            <th class="py-3 px-2 text-left">Title & Description</th>
                                            <th class="py-3 px-2 text-center">Likelihood</th>
                                            <th class="py-3 px-2 text-center">Impact</th>
                                            <th class="py-3 px-2 text-center">Level</th>
                                            <th class="py-3 px-2 text-center">Status</th>
                                            <th class="py-3 px-2 text-center">Owner</th>
                                            <th class="py-3 px-2 text-center">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black text-sm leading-normal">
                                        @forelse($audit->risks->sortByDesc('created_at') as $idx => $risk)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 align-top">
                                            <td class="py-3 px-2 text-center font-semibold">{{ $idx+1 }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-800 w-60">
                                                <div>{{ $risk->title }}</div>
                                                @if($risk->description)
                                                <div class="text-[11px] text-gray-600 mt-1">{{
                                                    Str::limit($risk->description,120) }}</div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{ ucfirst($risk->likelihood ??
                                                '—') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ ucfirst($risk->impact ?? '—')
                                                }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ ucfirst($risk->risk_level ??
                                                '—') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ Str::headline($risk->status ??
                                                'identified') }}</td>
                                            <td class="py-3 px-2 text-center text-xs">{{ $risk->owner?->name ?? '—' }}
                                            </td>
                                            <td class="py-3 px-2 text-center text-xs">{{
                                                $risk->created_at->format('d-m-Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="py-6 px-4 text-center text-gray-500">No risks logged.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                        <form method="POST" action="{{ route('audits.risks.add', $audit) }}"
                            class="grid md:grid-cols-6 gap-4">@csrf
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold mb-1">Title</label>
                                <input name="title" required class="w-full border-gray-300 rounded-md text-sm"
                                    placeholder="Risk title" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1">Likelihood</label>
                                <select name="likelihood" class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">—</option>
                                    @foreach(['low','medium','high'] as $v)
                                    <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1">Impact</label>
                                <select name="impact" class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">—</option>
                                    @foreach(['low','medium','high'] as $v)
                                    <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1">Level</label>
                                <select name="risk_level" class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">—</option>
                                    @foreach(['low','medium','high','critical'] as $v)
                                    <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1">Status</label>
                                <select name="status" class="w-full border-gray-300 rounded-md text-sm">
                                    @foreach(['identified','assessed','treated','retired'] as $v)
                                    <option value="{{ $v }}">{{ Str::headline($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold mb-1">Owner</label>
                                <select name="owner_user_id" class="w-full border-gray-300 rounded-md text-sm">
                                    <option value="">—</option>
                                    @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-xs font-semibold mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full border-gray-300 rounded-md text-sm"
                                    placeholder="Optional details"></textarea>
                            </div>
                            <div class="md:col-span-6 flex justify-end pt-1">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold">Save
                                    Risk</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Removed notifications, schedules, tags tab content -->

                <div id="metrics-tab" class="tab-content p-3" style="display:none;">
                    @php($metricDescriptions = [
                    'findings_total' => 'Count of all findings for this audit (audit_findings where audit_id =
                    current).',
                    'actions_open' => 'Count of actions whose status is not completed/closed (open, in_progress,
                    implemented, verified, etc).',
                    'risks_total' => 'Count of all risks linked to this audit.'
                    ])
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h4 class="text-xl font-semibold text-gray-800">Metrics</h4>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('audits.metrics.recalc', $audit) }}" class="flex">
                                    @csrf
                                    <button
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium">Recalculate</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed">Below are cached audit metrics. Each card shows
                            the latest calculated value and how it is derived. Values are recalculated server-side;
                            manual entries allow adding custom one-off metrics.</p>
                        <div class="grid md:grid-cols-3 gap-5">
                            @forelse($audit->metrics as $m)
                            <div class="p-4 border rounded-lg bg-white shadow-sm text-sm space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-semibold text-gray-800 break-words">{{
                                        Str::headline($m->metric_key) }}</div>
                                    <span
                                        class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium">{{
                                        $m->ttl_seconds ? ($m->ttl_seconds.'s TTL') : 'No TTL' }}</span>
                                </div>
                                <div class="text-2xl font-bold text-indigo-600">{{ $m->metric_value ?? $m->numeric_value
                                    ?? '—' }}</div>
                                <div class="text-[13px] text-gray-600 leading-snug">
                                    <span class="font-medium text-gray-700">How calculated:</span>
                                    {{ $metricDescriptions[$m->metric_key] ?? 'Custom metric entered manually by a
                                    user.' }}
                                </div>
                                <div
                                    class="flex flex-wrap items-center justify-between text-[12px] text-gray-500 pt-1 border-t">
                                    <span>Updated {{ optional($m->calculated_at)->diffForHumans() ?: '—' }}</span>
                                    @if($m->calculated_at)<span class="text-gray-400">{{
                                        optional($m->calculated_at)->format('d-m-Y H:i') }}</span>@endif
                                </div>
                                @if(!isset($metricDescriptions[$m->metric_key]))
                                <div
                                    class="text-[11px] text-amber-600 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                                    Manual / custom metric – will not auto-recalculate.</div>
                                @endif
                            </div>
                            @empty
                            <p class="text-sm text-gray-500 col-span-full">No metrics cached.</p>
                            @endforelse
                        </div>
                        <div class="p-5 border rounded-xl bg-gray-50/70">
                            <h5 class="text-base font-semibold mb-4 text-gray-800">Add / Update Metric</h5>
                            <form method="POST" action="{{ route('audits.metrics.add', $audit) }}"
                                class="grid md:grid-cols-12 gap-4 text-sm"
                                onsubmit="if(document.getElementById('metric_key_select').value==='__custom'){ document.getElementById('metric_key').value = document.getElementById('custom_metric_key').value.trim(); }">
                                @csrf
                                <div class="md:col-span-3 space-y-1">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Metric
                                        Key</label>
                                    <select id="metric_key_select" class="w-full border-gray-300 rounded-md"
                                        onchange="const sel=this.value;const customRow=document.getElementById('custom_metric_key_row');if(sel==='__custom'){customRow.classList.remove('hidden')}else{customRow.classList.add('hidden');document.getElementById('metric_key').value=sel;}">
                                        <option value="findings_total">findings_total</option>
                                        <option value="actions_open">actions_open</option>
                                        <option value="risks_total">risks_total</option>
                                        <option value="__custom">Custom…</option>
                                    </select>
                                    <input type="hidden" id="metric_key" name="metric_key" value="findings_total" />
                                </div>
                                <div id="custom_metric_key_row" class="md:col-span-3 space-y-1 hidden">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Custom
                                        Key</label>
                                    <input id="custom_metric_key" placeholder="e.g. overdue_actions"
                                        class="w-full border-gray-300 rounded-md" />
                                </div>
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Decimal
                                        Value</label>
                                    <input name="metric_value" placeholder="e.g. 87.5"
                                        class="w-full border-gray-300 rounded-md" />
                                </div>
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Integer
                                        Value</label>
                                    <input name="numeric_value" placeholder="e.g. 42"
                                        class="w-full border-gray-300 rounded-md" />
                                </div>
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-wide">TTL
                                        (seconds)</label>
                                    <input name="ttl_seconds" placeholder="3600"
                                        class="w-full border-gray-300 rounded-md" />
                                </div>
                                <div class="md:col-span-12 flex justify-end pt-2">
                                    <button
                                        class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold">Save
                                        Metric</button>
                                </div>
                                <div class="md:col-span-12 text-[12px] text-gray-500 leading-snug">Predefined metrics
                                    are auto-managed during recalculation; custom metrics remain static unless updated
                                    manually.</div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="operations-tab" class="tab-content p-3" style="display:none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <h4 class="text-md font-medium mb-3">Update Status</h4>
                            <form method="POST" action="{{ route('audits.update-status', $audit) }}">@csrf
                                @method('PATCH')
                                <div class="space-y-3">
                                    <select name="status" required class="w-full border-gray-300 rounded-md">
                                        <option value="">Select status</option>
                                        <option value="planned">Planned</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="reporting">Reporting</option>
                                        <option value="issued">Issued</option>
                                        <option value="closed">Closed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <input type="text" name="note" placeholder="Reason / note (optional)"
                                        class="w-full border-gray-300 rounded-md">
                                    <div class="flex justify-end"><button type="submit"
                                            class="px-3 py-1 bg-yellow-600 text-white rounded text-xs">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Basic Info</h4>
                                <form method="POST" action="{{ route('audits.update-basic-info', $audit) }}">@csrf
                                    @method('PATCH')
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Title</label>
                                            <input name="title" value="{{ $audit->title }}"
                                                class="w-full border-gray-300 rounded-md text-sm" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Description</label>
                                            <textarea name="description" rows="2"
                                                class="w-full border-gray-300 rounded-md text-sm">{{ $audit->description }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Scope Summary</label>
                                            <textarea name="scope_summary" rows="2"
                                                class="w-full border-gray-300 rounded-md text-sm">{{ $audit->scope_summary }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-xs font-semibold mb-1">Planned Start
                                                    Date</label>
                                                <input type="date" name="planned_start_date"
                                                    value="{{ $audit->planned_start_date }}"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold mb-1">Planned End Date</label>
                                                <input type="date" name="planned_end_date"
                                                    value="{{ $audit->planned_end_date }}"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-xs font-semibold mb-1">Actual Start
                                                    Date</label>
                                                <input type="date" name="actual_start_date"
                                                    value="{{ $audit->actual_start_date }}"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold mb-1">Actual End Date</label>
                                                <input type="date" name="actual_end_date"
                                                    value="{{ $audit->actual_end_date }}"
                                                    class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Score</label>
                                            <input name="score" value="{{ $audit->score }}"
                                                class="w-full border-gray-300 rounded-md text-sm" placeholder="Score">
                                        </div>
                                        <div class="flex justify-end"><button
                                                class="px-3 py-1 bg-blue-600 text-white rounded text-xs">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Tags Management -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Tags</h4>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @forelse($audit->tags as $tag)
                                    <div class="flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-medium"
                                        style="background: {{ $tag->color ?? '#eee' }}20; border:1px solid {{ $tag->color ?? '#ccc' }};">
                                        <span style="color: {{ $tag->color ?? '#555' }}">{{ $tag->name }}</span>
                                        <form method="POST" action="{{ route('audits.tags.remove', [$audit, $tag]) }}">
                                            @csrf @method('DELETE')<button class="text-[10px] text-red-600"
                                                onclick="return confirm('Remove tag?')">&times;</button></form>
                                    </div>
                                    @empty
                                    <p class="text-sm text-gray-500">No tags.</p>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('audits.tags.add', $audit) }}"
                                    class="flex gap-3 items-end">
                                    @csrf
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold mb-1">Select Tag</label>
                                        <select name="tag_name" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Choose --</option>
                                            @foreach(\App\Models\AuditTag::orderBy('name')->get() as $allTag)
                                            <option value="{{ $allTag->name }}">{{ $allTag->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add Tag</button>
                                </form>
                            </div>
                            <!-- Notifications Management -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Notifications</h4>
                                <div class="space-y-3 mb-4">
                                    @forelse($audit->notifications as $note)
                                    <div
                                        class="p-2 border rounded bg-gray-50 flex justify-between items-center text-xs">
                                        <div>
                                            <div class="font-medium text-gray-700">{{ $note->subject ?? $note->template
                                                ?? 'Notification' }}</div>
                                            <div class="text-[10px] text-gray-500">Channel: {{ $note->channel ?? 'n/a'
                                                }} • Status: {{ $note->status ?? 'n/a' }} @if($note->sent_at) • Sent {{
                                                $note->sent_at->format('M d H:i') }} @endif</div>
                                        </div>
                                        <div class="flex gap-1">
                                            <form method="POST"
                                                action="{{ route('audits.notifications.resend', [$audit, $note]) }}">
                                                @csrf <button
                                                    class="px-2 py-0.5 bg-blue-600 text-white rounded">↻</button></form>
                                            <form method="POST"
                                                action="{{ route('audits.notifications.delete', [$audit, $note]) }}">
                                                @csrf @method('DELETE') <button
                                                    class="px-2 py-0.5 bg-red-600 text-white rounded"
                                                    onclick="return confirm('Delete?')">✕</button></form>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-sm text-gray-500">No notifications.</p>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('audits.notifications.add', $audit) }}"
                                    class="grid md:grid-cols-3 gap-2">@csrf
                                    <input name="subject" placeholder="Subject"
                                        class="border-gray-300 rounded-md text-xs md:col-span-2">
                                    <select name="channel" class="border-gray-300 rounded-md text-xs">
                                        <option value="email">Email</option>
                                        <option value="system">System</option>
                                    </select>
                                    <textarea name="body" rows="2"
                                        class="border-gray-300 rounded-md text-xs md:col-span-3"
                                        placeholder="Body (optional)"></textarea>
                                    <div class="md:col-span-3 flex justify-end"><button
                                            class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Queue</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="schedules-tab" class="tab-content p-3" style="display:none;">
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                            <div class="relative overflow-x-auto rounded-lg">
                                <table class="min-w-max w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-green-800 text-white uppercase text-sm">
                                            <th class="py-3 px-2 text-center">#</th>
                                            <th class="py-3 px-2 text-left">Frequency</th>
                                            <th class="py-3 px-2 text-center">Scheduled Date</th>
                                            <th class="py-3 px-2 text-center">Next Run Date</th>
                                            <th class="py-3 px-2 text-center">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black text-sm leading-normal">
                                        @forelse($audit->schedules as $idx => $sch)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="py-3 px-2 text-center font-semibold">{{ $idx+1 }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-800">{{ ucfirst($sch->frequency)
                                                }}</td>
                                            <td class="py-3 px-2 text-center">{{ $sch->scheduled_date?->format('d-m-Y')
                                                ?? '—' }}</td>
                                            <td class="py-3 px-2 text-center">{{ $sch->next_run_date?->format('d-m-Y')
                                                ?? '—' }}</td>
                                            <td class="py-3 px-2 text-center text-xs text-gray-600">{{
                                                $sch->created_at?->format('d-m-Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="py-6 px-4 text-center text-gray-500">No schedules.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('audits.schedules.add', $audit) }}"
                        class="grid md:grid-cols-5 gap-4 bg-white p-4 border border-gray-200 shadow-md sm:rounded-lg">
                        @csrf
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-1">Frequency</label>
                            <select name="frequency" class="w-full border-gray-300 rounded-md text-sm">
                                @foreach(['once','monthly','quarterly','semiannual','annual'] as $opt)
                                <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Scheduled Date</label>
                            <input type="date" name="scheduled_date" required
                                class="w-full border-gray-300 rounded-md text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Next Run (optional)</label>
                            <input type="date" name="next_run_date" class="w-full border-gray-300 rounded-md text-sm" />
                        </div>
                        <div class="flex items-end justify-end">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold">Add
                                Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function(){
			function initTabs(){
				const tabButtons = document.querySelectorAll('.tab-button');
				const tabContents = document.querySelectorAll('.tab-content');
				if(!tabButtons.length) return;
				tabContents.forEach(c=>c.style.display='none');
				const first = document.getElementById('history-tab');
				if(first) first.style.display='block';
				tabButtons.forEach(btn=>{
					if(btn.dataset.tabInit) return; btn.dataset.tabInit='1';
					btn.addEventListener('click', e=>{
						e.preventDefault();
						const tabName = btn.getAttribute('data-tab');
						tabButtons.forEach(b=>{ b.classList.remove('border-indigo-500','text-indigo-600'); b.classList.add('border-transparent','text-gray-500'); });
						tabContents.forEach(tc=>tc.style.display='none');
						btn.classList.add('border-indigo-500','text-indigo-600');
						btn.classList.remove('border-transparent','text-gray-500');
						const tgt = document.getElementById(tabName+'-tab');
						if(tgt) tgt.style.display='block';
					});
				});
			}
			if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', initTabs); else initTabs();
		})();
        function toggleFindingEdit(id){
          const row = document.getElementById('finding-edit-'+id);
          if(row){ row.classList.toggle('hidden'); }
        }
    </script>
    @endpush
    @push('scripts')
    <script>
        // Structured PDF generator for Audit (mirrors Complaints implementation)
(function(){
    // Helper to safely access nested properties without optional chaining (for older browsers)
    function gv(obj, path){ var parts = path.split('.'); var cur = obj; for(var i=0;i<parts.length;i++){ if(!cur || typeof cur!=='object') return undefined; cur = cur[parts[i]]; } return cur; }
    function log(){ try{ console.log('[AUDIT PDF]', ...arguments);}catch(e){} }
    function status(msg){
        var btn=document.getElementById('audit-structured-pdf-btn');
        if(btn){ btn.dataset.stage=msg; btn.title='PDF: '+msg; }
        var box=document.getElementById('audit-pdf-status-box');
        if(!box){ box=document.createElement('div'); box.id='audit-pdf-status-box'; box.style.position='fixed'; box.style.bottom='10px'; box.style.right='10px'; box.style.zIndex='9999'; box.style.background='#111'; box.style.color='#fff'; box.style.fontSize='11px'; box.style.padding='6px 10px'; box.style.borderRadius='6px'; box.style.boxShadow='0 2px 6px rgba(0,0,0,.3)'; box.style.opacity='0.85'; box.innerHTML='Audit PDF: init'; document.body.appendChild(box);} box.innerHTML='Audit PDF: '+msg; }
    function loadScriptOnce(id, src, readyTest){
        return new Promise((res, rej)=>{
            const existing=document.getElementById(id);
            const isReady = function(){ try{ return !readyTest || readyTest(); }catch(e){ return false; } };
            if(existing){
                log('script already present', id);
                if(isReady()) return res(true);
                existing.addEventListener('load', ()=>{ if(isReady()) res(true); else rej(new Error('Library not ready after load '+id)); });
                existing.addEventListener('error', ()=>rej(new Error('Load failed '+src)));
                return;
            }
            const s=document.createElement('script'); s.id=id; s.src=src; s.async=true;
            s.onload=()=>{ log('loaded', id); if(isReady()) res(true); else rej(new Error('Library not ready '+id)); };
            s.onerror=()=>{ log('failed load', id, src); rej(new Error('Load failed '+src)); };
            document.head.appendChild(s);
        });
    }
    async function ensureLibs(){
        async function attempt(id, primary, fallback, test){
            try{ await loadScriptOnce(id, primary, test); }
            catch(e){ if(fallback) { log('primary failed, trying fallback', id); await loadScriptOnce(id+'-fb', fallback, test); } else throw e; }
        }
        if(!(window.jspdf && window.jspdf.jsPDF)) await attempt('jspdf-core','https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js','https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js', ()=>window.jspdf && window.jspdf.jsPDF);
        if(!(window.jspdf && window.jspdf.jsPDF)) throw new Error('jsPDF load failed (after load attempt)');
        if(!window.jspdf.jsPDF.API.autoTable) await attempt('jspdf-autotable','https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js','https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js', ()=>window.jspdf && window.jspdf.jsPDF && !!window.jspdf.jsPDF.API.autoTable);
    }
    function fmt(dt){ if(!dt) return '-'; try{ return new Date(dt).toLocaleString('en-GB',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});}catch(e){return dt;} }
    function addTitle(doc, txt, y){ doc.setFont('helvetica','bold'); doc.setFontSize(12); doc.setTextColor(30); doc.text(txt.toUpperCase(),40,y); doc.setFont('helvetica','normal'); return y+8; }
    function footer(doc, audit){ const pc=doc.getNumberOfPages(); for(let i=1;i<=pc;i++){ doc.setPage(i); const t=`Audit # ${audit.reference_no||audit.id||'-'} | Page ${i} of ${pc}`; doc.setFontSize(8); doc.setTextColor(90); const w=doc.internal.pageSize.getWidth(); const tw=doc.getTextWidth(t); doc.text(t,(w/2)-(tw/2), doc.internal.pageSize.getHeight()-12);} }
    async function runPdf(){ const btn=document.getElementById('audit-structured-pdf-btn'); if(!btn){ log('runPdf: no button'); return; }
        log('runPdf invoked'); status('clicked');
        try{ btn.disabled=true; btn.classList.add('opacity-50'); btn.textContent='Building...'; status('loading libs'); await ensureLibs(); log('libs ready'); status('libs ready'); const { jsPDF } = window.jspdf;
            const url = @json(route('audits.full',$audit)); log('fetching', url); status('fetching data'); const res = await fetch(url,{headers:{'Accept':'application/json'}, credentials:'same-origin'}); log('fetch status', res.status); if(!res.ok) throw new Error('Fetch failed '+res.status); const data= await res.json(); log('data received keys', Object.keys(data)); status('building pdf'); const a = data.audit || {};
            const doc = new jsPDF('p','pt');
            // Header
            doc.setFont('helvetica','bold'); doc.setFontSize(16); doc.setTextColor(20); doc.text('THE BANK OF AZAD JAMMU AND KASHMIR',40,40);
            doc.setFontSize(13); doc.text('AUDIT REPORT',40,60); doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(70);
            doc.text('Generated: '+fmt(data.exported_at),40,74); doc.text('Reference: '+(a.reference_no||'-'),300,74); doc.text('Status: '+(a.status||'-'),480,74);
            doc.setDrawColor(0); doc.setLineWidth(0.5); doc.line(40,78,555,78);
            let y=95;
            const summaryRows=[
                ['Reference', a.reference_no||'-','Status', a.status||'-'],
                ['Title', a.title||'-','Risk Overall', a.risk_overall||'-'],
                ['Type', (a.type && a.type.name) || '-','Score', a.score==null?'-':a.score],
                ['Planned Start', fmt(a.planned_start_date),'Planned End', fmt(a.planned_end_date)],
                ['Actual Start', fmt(a.actual_start_date),'Actual End', fmt(a.actual_end_date)],
                ['Lead Auditor', (a.lead_auditor && a.lead_auditor.name) || '-','Auditee', (a.auditee_user && a.auditee_user.name) || '-'],
                ['Parent Audit', (a.parent && a.parent.reference_no) || '-','Children', (a.children||[]).length]
            ];
            doc.autoTable({ startY:y, head:[['FIELD','VALUE','FIELD','VALUE']], body:summaryRows, styles:{fontSize:8,cellPadding:3,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]}, columnStyles:{0:{cellWidth:94},1:{cellWidth:172},2:{cellWidth:94},3:{cellWidth:146}}, tableWidth:510, theme:'grid'});
            y=doc.lastAutoTable.finalY+15;
            y=addTitle(doc,'Description', y); doc.setFontSize(9); let block = doc.splitTextToSize(a.description||'-',515); doc.text(block,40,y); y+=block.length*11+10;
            if(a.scope_summary){ y=addTitle(doc,'Scope Summary', y); block=doc.splitTextToSize(a.scope_summary,515); doc.text(block,40,y); y+=block.length*11+10; }
            if(Array.isArray(a.auditors) && a.auditors.length){ y=addTitle(doc,'Audit Team', y); const tRows=a.auditors.map(function(m){ return [ (m.user && m.user.name) || '-', m.role||'-', m.is_primary?'Yes':'No']; }); doc.autoTable({ startY:y, head:[['NAME','ROLE','PRIMARY']], body:tRows, styles:{fontSize:8,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]}, theme:'grid'}); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.scopes)&&a.scopes.length){ y=addTitle(doc,'Scopes', y); const sRows=a.scopes.map(s=>[s.scope_item, s.is_in_scope?'In':'Out', (s.description||'').substring(0,80)]); doc.autoTable({ startY:y, head:[['ITEM','IN/OUT','DESC']], body:sRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.findings)&&a.findings.length){ y=addTitle(doc,'Findings', y); const fRows=a.findings.map(function(f){ return [f.id, (f.title||'').substring(0,40), f.status||'-', f.risk_level||f.severity||'-', (f.owner && f.owner.name) || '-']; }); doc.autoTable({ startY:y, head:[['ID','TITLE','STATUS','RISK','OWNER']], body:fRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.actions)&&a.actions.length){ y=addTitle(doc,'Actions', y); const actRows=a.actions.map(ac=>[ac.id, (ac.title||'').substring(0,38), ac.status||'-', fmt(ac.due_date)]); doc.autoTable({ startY:y, head:[['ID','TITLE','STATUS','DUE']], body:actRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.risks)&&a.risks.length){ y=addTitle(doc,'Risks', y); const rRows=a.risks.map(r=>[r.id,(r.title||'').substring(0,40), r.risk_level||r.likelihood+'/' + r.impact, r.status||'-']); doc.autoTable({ startY:y, head:[['ID','TITLE','LEVEL','STATUS']], body:rRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.schedules)&&a.schedules.length){ y=addTitle(doc,'Schedules', y); const schRows=a.schedules.map(s=>[s.id, (s.title||s.schedule_type||'-').substring(0,34), fmt(s.start_date||s.scheduled_for), fmt(s.end_date)]); doc.autoTable({ startY:y, head:[['ID','TITLE','START','END']], body:schRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.documents)&&a.documents.length){ y=addTitle(doc,'Documents', y); const dRows=a.documents.map(d=>[(d.original_name||d.stored_name||'-').substring(0,40), (d.category||'-'), (d.mime_type||'-').split('/')[1]||'-', (Math.round((d.size_bytes||0)/1024)+' KB')]); doc.autoTable({ startY:y, head:[['NAME','CATEGORY','TYPE','SIZE']], body:dRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(data.assessment_items) && data.assessment_items.length){ y=addTitle(doc,'Checklist Responses (Summary)', y); const respMap={}; (a.responses||[]).forEach(r=>{ respMap[r.audit_checklist_item_id]=r; }); const ciRows = data.assessment_items.slice(0,140).map(ci=>{ const r=respMap[ci.id]||{}; return [ (ci.reference_code||ci.id).toString(), (ci.title||'').substring(0,34), r.response_value||'-', r.score==null?'-':r.score, (r.comment||'').substring(0,30) ]; }); doc.autoTable({ startY:y, head:[['REF','ITEM','RESP','SCORE','COMMENT']], body:ciRows, styles:{fontSize:6.5,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.status_histories)&&a.status_histories.length){ y=addTitle(doc,'Status History', y); const hRows=a.status_histories.map(function(h){ return [fmt(h.changed_at), (h.from_status||'-')+' → '+(h.to_status||'-'), (h.changer && h.changer.name) || 'System', (h.note||'').substring(0,50)]; }); doc.autoTable({ startY:y, head:[['WHEN','TRANSITION','BY','NOTE']], body:hRows, styles:{fontSize:6.5,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.notifications)&&a.notifications.length){ y=addTitle(doc,'Notifications', y); const nRows=a.notifications.map(function(n){ return [n.channel||'-', fmt(n.sent_at||n.created_at), (n.status||'-'), (n.subject||'').substring(0,40)]; }); doc.autoTable({ startY:y, head:[['CHANNEL','SENT','STATUS','SUBJECT']], body:nRows, styles:{fontSize:6.5,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.tags)&&a.tags.length){ y=addTitle(doc,'Tags', y); const tRows=a.tags.map(t=>[t.name]); doc.autoTable({ startY:y, head:[['TAG']], body:tRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]}, tableWidth:180 }); y=doc.lastAutoTable.finalY+15; }
            if(Array.isArray(a.metrics)&&a.metrics.length){ y=addTitle(doc,'Metrics Cache', y); const mRows=a.metrics.map(m=>[m.metric_key||'-', m.metric_value||m.numeric_value||'-']); doc.autoTable({ startY:y, head:[['KEY','VALUE']], body:mRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            if(data.derived){ y=addTitle(doc,'Derived Metrics', y); const dRows=Object.entries(data.derived).map(([k,v])=>[k.replace(/_/g,' '), v]); doc.autoTable({ startY:y, head:[['METRIC','VALUE']], body:dRows, styles:{fontSize:7,cellPadding:2,lineColor:[0,0,0],lineWidth:0.1}, headStyles:{fillColor:[0,0,0],textColor:[255,255,255]} }); y=doc.lastAutoTable.finalY+15; }
            footer(doc,a); var fname='audit-'+(a.reference_no||a.id||'export')+'.pdf'; log('saving', fname); status('saving '+fname); doc.save(fname); status('done');
        }catch(e){ log('error', e); status('error: '+e.message); console.error(e); alert('Failed to build structured PDF: '+e.message+' (see console for details)'); }
        finally{ if(btn){ btn.disabled=false; btn.classList.remove('opacity-50'); btn.textContent='Download Structured PDF'; } }
    }
    async function init(){ const btn=document.getElementById('audit-structured-pdf-btn'); if(!btn||btn.dataset.init){ log('init: button missing or already init'); } else { btn.dataset.init='1'; btn.addEventListener('click', runPdf, { once:false }); log('init: listener bound'); status('ready'); }
        window.generateAuditPdf = runPdf; // expose global for manual trigger if needed
    }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
    </script>
    @endpush
</x-app-layout>