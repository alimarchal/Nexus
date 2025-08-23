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
                            data-tab="checklist">Checklist & Responses</button>
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
                        <!-- Removed notifications, schedules, tags tabs moved to Operations -->
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="metrics">Metrics ({{ $audit->metrics->count() }})</button>
                        <button
                            class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700"
                            data-tab="operations">Operations</button>
                    </nav>
                </div>

                <div id="history-tab" class="tab-content p-6">
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

                <div id="auditors-tab" class="tab-content p-6" style="display:none;">
                    <!-- Auditor team heading removed per request -->
                    @if($audit->auditors->count())
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($audit->auditors as $aud)
                        <div
                            class="relative group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <h5 class="text-sm font-semibold text-gray-900 truncate flex items-center gap-2">
                                        <span>{{ $aud->user?->name ?? '—' }}</span>
                                        @if($aud->is_primary)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-medium">Primary</span>
                                        @endif
                                    </h5>
                                    <p class="text-xs text-gray-500 truncate">{{ $aud->user?->email ?? '—' }}</p>
                                </div>
                                <form method="POST" action="{{ route('audits.auditors.delete', [$audit, $aud]) }}"
                                    onsubmit="return confirm('Remove auditor?')" class="shrink-0">@csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="opacity-60 group-hover:opacity-100 transition text-red-600 hover:text-red-700"
                                        title="Remove auditor">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] font-medium">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 capitalize">{{
                                    $aud->role }}</span>
                                <span class="text-gray-300">•</span>
                                <span class="text-gray-500">Added {{ $aud->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="mb-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700">No auditors assigned yet</p>
                        <p class="mt-1 text-xs text-gray-500">Add the first auditor using the form below.</p>
                    </div>
                    @endif
                    <div class="mt-10 relative">
                        <div
                            class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-100/70 via-white to-indigo-50 pointer-events-none">
                        </div>
                        <div class="relative rounded-2xl border border-indigo-200/70 shadow-sm overflow-hidden">
                            <div
                                class="px-6 pt-6 pb-4 flex items-center justify-between flex-wrap gap-4 bg-gradient-to-r from-white to-indigo-50/60">
                                <div>
                                    <h5 class="text-sm font-semibold text-indigo-900 tracking-wide">Add / Update Auditor
                                    </h5>
                                    <p class="mt-1 text-[11px] text-indigo-600/80">Add a new auditor or change role /
                                        primary flag of an existing one.</p>
                                </div>
                                <div
                                    class="hidden md:flex items-center gap-2 text-[10px] text-indigo-600/70 font-medium">
                                    <span class="inline-flex items-center gap-1"><span
                                            class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span> Primary is
                                        unique</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('audits.assign-auditors', $audit) }}"
                                class="p-6 grid gap-6 lg:grid-cols-12">
                                @csrf
                                <div class="lg:col-span-5 space-y-1">
                                    <label
                                        class="text-[11px] uppercase tracking-wide font-semibold text-indigo-800">User</label>
                                    <select name="user_id" required
                                        class="w-full rounded-md border-indigo-300 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                        <option value="">Select a user...</option>
                                        @foreach($allUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lg:col-span-3 space-y-1">
                                    <label
                                        class="text-[11px] uppercase tracking-wide font-semibold text-indigo-800">Role</label>
                                    <div class="relative">
                                        <select name="role"
                                            class="w-full rounded-md border-indigo-300 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm pr-8">
                                            <option value="lead">Lead</option>
                                            <option value="member" selected>Member</option>
                                            <option value="observer">Observer</option>
                                        </select>
                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-indigo-400">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.185l3.71-3.954a.75.75 0 011.08 1.04l-4.25 4.53a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="lg:col-span-2 flex items-center pt-5">
                                    <label
                                        class="inline-flex items-center text-xs font-medium text-indigo-800 select-none cursor-pointer">
                                        <input type="checkbox" name="is_primary" value="1"
                                            class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2">Primary</span>
                                    </label>
                                </div>
                                <div class="lg:col-span-2 flex items-end justify-end">
                                    <button type="submit"
                                        class="group relative inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                        <svg class="h-4 w-4 text-indigo-200 group-hover:text-white transition"
                                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Save Auditor</span>
                                    </button>
                                </div>
                                <div
                                    class="lg:col-span-12 border-t border-dashed border-indigo-200/60 pt-4 text-[10px] text-indigo-500 flex flex-wrap gap-4 justify-between">
                                    <span>Auditor entries are logged to history for traceability.</span>
                                    <span>Changing primary will remove primary flag from others.</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="scopes-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-8">
                        @if($audit->scopes->count())
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($audit->scopes as $scope)
                            <div
                                class="group relative rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="min-w-0">
                                        <h5 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <span>{{ $scope->scope_item }}</span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $scope->is_in_scope ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">{{
                                                $scope->is_in_scope ? 'In' : 'Out' }} Scope</span>
                                        </h5>
                                        @if($scope->description)
                                        <p class="mt-1 text-xs text-gray-600 line-clamp-2">{{
                                            Str::limit($scope->description, 140) }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('audits.scopes.delete', [$audit, $scope]) }}"
                                        onsubmit="return confirm('Delete scope item?')" class="shrink-0">@csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="opacity-60 group-hover:opacity-100 transition text-red-600 hover:text-red-700"
                                            title="Remove scope">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="flex items-center justify-between mt-3 text-[10px] text-gray-500">
                                    <span>Added {{ $scope->created_at?->diffForHumans() }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100">#{{ $loop->iteration }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow">
                                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">No scope items yet</p>
                            <p class="mt-1 text-xs text-gray-500">Define the boundaries of this audit below.</p>
                        </div>
                        @endif
                        <div class="relative mt-4">
                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-100/50 via-white to-indigo-50 pointer-events-none">
                            </div>
                            <div class="relative rounded-2xl border border-indigo-200/70 shadow-sm overflow-hidden">
                                <div
                                    class="px-6 pt-6 pb-4 flex items-center justify-between flex-wrap gap-4 bg-gradient-to-r from-white to-indigo-50/60">
                                    <div>
                                        <h5 class="text-sm font-semibold text-indigo-900 tracking-wide">Add Scope Item
                                        </h5>
                                        <p class="mt-1 text-[11px] text-indigo-600/80">Capture a new process, location
                                            or element for this audit.</p>
                                    </div>
                                    <div
                                        class="hidden md:flex items-center gap-2 text-[10px] text-indigo-600/70 font-medium">
                                        <span class="inline-flex items-center gap-1"><span
                                                class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                            In-scope by default</span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('audits.scopes.add', $audit) }}"
                                    class="p-6 grid gap-6 lg:grid-cols-12">
                                    @csrf
                                    <div class="lg:col-span-5 space-y-1">
                                        <label
                                            class="text-[11px] uppercase tracking-wide font-semibold text-indigo-800">Scope
                                            Item</label>
                                        <input name="scope_item" required placeholder="e.g. Branch cash processes"
                                            class="w-full rounded-md border-indigo-300 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                                    </div>
                                    <div class="lg:col-span-5 space-y-1">
                                        <label
                                            class="text-[11px] uppercase tracking-wide font-semibold text-indigo-800">Description</label>
                                        <textarea name="description" rows="2" placeholder="Optional details"
                                            class="w-full rounded-md border-indigo-300 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"></textarea>
                                    </div>
                                    <div class="lg:col-span-2 flex items-center pt-5">
                                        <label
                                            class="inline-flex items-center text-xs font-medium text-indigo-800 cursor-pointer select-none">
                                            <input type="checkbox" name="is_in_scope" value="1" checked
                                                class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span class="ml-2">In Scope</span>
                                        </label>
                                    </div>
                                    <div class="lg:col-span-12 flex items-end justify-end">
                                        <button type="submit"
                                            class="group relative inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                            <svg class="h-4 w-4 text-indigo-200 group-hover:text-white transition"
                                                fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Add Scope</span>
                                        </button>
                                    </div>
                                    <div
                                        class="lg:col-span-12 border-t border-dashed border-indigo-200/60 pt-4 text-[10px] text-indigo-500 flex flex-wrap gap-4 justify-between">
                                        <span>Scope changes are logged to the timeline.</span>
                                        <span>Use descriptions to clarify inclusion boundaries.</span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="checklist-tab" class="tab-content p-6" style="display:none;">
                    <div class="text-sm text-gray-600 mb-4">Audit Checklist Items (by type) with any captured responses.
                    </div>
                    @php($items = $audit->checklistItems ?? collect())
                    @php($responsesByItem = $audit->responses?->groupBy('audit_checklist_item_id') ?? collect())
                    <div class="space-y-4">
                        @forelse($items as $item)
                        <div class="border rounded p-4 bg-white shadow-sm">
                            <div class="flex justify-between">
                                <div class="font-medium text-gray-800">{{ $item->reference_code ?
                                    '['.$item->reference_code.'] ' : '' }}{{ $item->title }}</div>
                                <div class="text-xs text-gray-500">{{ $item->response_type ?? '—' }}</div>
                            </div>
                            @if($item->criteria)
                            <div class="text-xs text-gray-500 mt-1">Criteria: {{ Str::limit($item->criteria, 160) }}
                            </div>
                            @endif
                            @php($respSet = $responsesByItem->get($item->id) ?? collect())
                            @if($respSet->count())
                            <div class="mt-3 space-y-2">
                                @foreach($respSet as $resp)
                                <div class="p-2 bg-gray-50 border rounded text-xs flex justify-between">
                                    <div class="truncate"><span class="font-medium">Response:</span> {{
                                        Str::limit($resp->response_value ?? '—', 120) }} @if($resp->comment) • {{
                                        Str::limit($resp->comment, 80) }} @endif</div>
                                    <div class="text-gray-500 ml-2 shrink-0">{{ optional($resp->responded_at)->format('M
                                        d H:i') }}</div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="mt-2 text-xs text-gray-500">No responses.</div>
                            @endif
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No checklist items linked to this audit type yet.</p>
                        @endforelse
                    </div>
                </div>

                <div id="findings-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-4">
                        @forelse($audit->findings as $finding)
                        <div class="p-4 border rounded bg-white shadow-sm">
                            <div class="flex justify-between">
                                <div class="font-semibold text-gray-800">{{ $finding->reference_no ?? 'Finding
                                    #'.$finding->id }}</div>
                                <div class="text-xs text-gray-500">Severity: {{ ucfirst($finding->severity ?? 'n/a') }}
                                    • Status: {{ ucfirst($finding->status ?? 'n/a') }}</div>
                            </div>
                            <div class="mt-2 text-xs text-gray-600">Actions: {{ $finding->actions->count() }} •
                                Attachments: {{ $finding->attachments->count() }} • Risks: {{ $finding->risks->count()
                                }}</div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No findings recorded.</p>
                        @endforelse
                        <div class="mt-6 p-4 border border-indigo-200 rounded-lg bg-indigo-50/60">
                            <h5 class="text-sm font-semibold text-gray-800 mb-2">Add Finding</h5>
                            <form method="POST" action="{{ route('audits.findings.add', $audit) }}"
                                class="grid gap-3 md:grid-cols-3">@csrf
                                <input name="reference_no" placeholder="Reference (optional)"
                                    class="border-gray-300 rounded-md text-sm md:col-span-1">
                                <select name="severity" class="border-gray-300 rounded-md text-sm">
                                    <option value="">Severity</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                                <select name="status" class="border-gray-300 rounded-md text-sm">
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <div class="md:col-span-3 flex justify-end"><button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add Finding</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="actions-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-4">
                        @forelse($audit->actions as $action)
                        <div class="p-4 border rounded bg-white shadow-sm">
                            <div class="flex justify-between">
                                <div class="font-medium text-gray-800">{{ $action->reference_no ?? 'Action
                                    #'.$action->id }} • {{ $action->title }}</div>
                                <div class="text-xs text-gray-500">Status: {{ ucfirst($action->status ?? 'n/a') }}
                                    @if($action->due_date) • Due: {{ \Carbon\Carbon::parse($action->due_date)->format('M
                                    d, Y') }} @endif</div>
                            </div>
                            @if($action->description)<div class="mt-2 text-xs text-gray-600">{{
                                Str::limit($action->description, 180) }}</div>@endif
                            @if($action->updates->count())
                            <div class="mt-3 space-y-2">
                                @foreach($action->updates->sortByDesc('created_at')->take(5) as $upd)
                                <div class="p-2 bg-gray-50 border rounded text-[11px]"><span class="font-medium">{{
                                        $upd->creator?->name ?? 'System' }}:</span> {{ Str::limit($upd->update_text,
                                    140) }} <span class="text-gray-500 ml-2">{{ $upd->created_at->format('M d H:i')
                                        }}</span></div>
                                @endforeach
                            </div>
                            @endif
                            <form method="POST" action="{{ route('audits.actions.updates.add', [$audit, $action]) }}"
                                class="mt-3 flex gap-2">@csrf
                                <input name="update_text" required placeholder="Add update..."
                                    class="flex-1 border-gray-300 rounded-md text-xs">
                                <button class="px-2 py-1 bg-indigo-600 text-white rounded text-xs">Add</button>
                            </form>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No actions yet.</p>
                        @endforelse
                        @if($audit->findings->count())
                        <div class="mt-6 p-4 border border-indigo-200 rounded-lg bg-indigo-50/60">
                            <h5 class="text-sm font-semibold text-gray-800 mb-2">Add Action</h5>
                            <form method="POST"
                                action="{{ route('audits.actions.add', [$audit, $audit->findings->first()]) }}"
                                class="grid md:grid-cols-4 gap-3">@csrf
                                <select name="audit_finding_id" required
                                    class="border-gray-300 rounded-md text-sm md:col-span-2">
                                    <option value="">Select Finding</option>@foreach($audit->findings as $f)<option
                                        value="{{ $f->id }}">{{ $f->reference_no ?? 'Finding '.$f->id }}</option>
                                    @endforeach
                                </select>
                                <input name="title" required placeholder="Action title"
                                    class="border-gray-300 rounded-md text-sm md:col-span-2">
                                <div class="md:col-span-4 flex justify-end"><button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add Action</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <div id="documents-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-4">
                        <div class="p-4 border border-indigo-200 rounded-lg bg-indigo-50/60">
                            <h5 class="text-sm font-semibold text-gray-800 mb-2">Upload Document</h5>
                            <form method="POST" action="{{ route('audits.documents.store', $audit) }}"
                                enctype="multipart/form-data" class="space-y-2 text-xs">@csrf
                                <input type="file" name="file" required
                                    class="border-gray-300 rounded-md text-sm w-full">
                                <input name="category" placeholder="Category (optional)"
                                    class="border-gray-300 rounded-md text-sm w-full">
                                <div class="flex justify-end"><button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Upload</button></div>
                            </form>
                        </div>
                        @forelse($audit->documents as $doc)
                        <div class="flex justify-between items-center p-3 border rounded bg-white shadow-sm">
                            <div class="text-sm">
                                <div class="font-medium text-gray-800">{{ $doc->original_name }}</div>
                                <div class="text-[11px] text-gray-500">{{ $doc->mime_type ?? 'n/a' }} • Uploaded {{
                                    $doc->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('audits.documents.download', [$audit, $doc]) }}"
                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded">Download</a>
                                <form method="POST" action="{{ route('audits.documents.delete', [$audit, $doc]) }}">
                                    @csrf @method('DELETE')<button onclick="return confirm('Delete document?')"
                                        class="px-2 py-1 text-xs bg-red-600 text-white rounded">Delete</button></form>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No documents uploaded.</p>
                        @endforelse
                    </div>
                </div>

                <div id="risks-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-4">
                        @forelse($audit->risks as $risk)
                        <div class="p-4 border rounded bg-white shadow-sm">
                            <div class="flex justify-between">
                                <div class="font-medium text-gray-800">{{ $risk->title }}</div>
                                <div class="text-xs text-gray-500">Likelihood: {{ ucfirst($risk->likelihood ?? 'n/a') }}
                                    • Level: {{ ucfirst($risk->risk_level ?? 'n/a') }}</div>
                            </div>
                            @if($risk->description)<div class="mt-2 text-xs text-gray-600">{{
                                Str::limit($risk->description, 180) }}</div>@endif
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No risks logged.</p>
                        @endforelse
                        <div class="mt-6 p-4 border border-indigo-200 rounded-lg bg-indigo-50/60">
                            <h5 class="text-sm font-semibold text-gray-800 mb-2">Add Risk</h5>
                            <form method="POST" action="{{ route('audits.risks.add', $audit) }}"
                                class="grid md:grid-cols-4 gap-3">@csrf
                                <input name="title" required placeholder="Title"
                                    class="border-gray-300 rounded-md text-sm md:col-span-2">
                                <select name="likelihood" class="border-gray-300 rounded-md text-sm">
                                    <option value="">Likelihood</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <select name="risk_level" class="border-gray-300 rounded-md text-sm">
                                    <option value="">Risk Level</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                                <textarea name="description" rows="2"
                                    class="border-gray-300 rounded-md text-sm md:col-span-4"
                                    placeholder="Description (optional)"></textarea>
                                <div class="md:col-span-4 flex justify-end"><button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add Risk</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Removed notifications, schedules, tags tab content -->

                <div id="metrics-tab" class="tab-content p-6" style="display:none;">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="text-lg font-semibold text-gray-800">Metrics Cache</h4>
                            <form method="POST" action="{{ route('audits.metrics.recalc', $audit) }}">@csrf <button
                                    class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Recalculate</button>
                            </form>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4">
                            @forelse($audit->metrics as $m)
                            <div class="p-3 border rounded bg-white shadow-sm text-xs">
                                <div class="font-medium text-gray-700 truncate">{{ Str::headline($m->metric_key) }}
                                </div>
                                <div class="text-indigo-600 font-bold text-sm">{{ $m->metric_value ?? $m->numeric_value
                                    ?? '—' }}</div>
                                <div class="text-[10px] text-gray-500 mt-1">Calc: {{
                                    optional($m->calculated_at)->diffForHumans() }}</div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">No metrics cached.</p>
                            @endforelse
                        </div>
                        <div class="mt-6 p-4 border rounded bg-gray-50">
                            <h5 class="text-sm font-semibold mb-2">Add Metric</h5>
                            <form method="POST" action="{{ route('audits.metrics.add', $audit) }}"
                                class="grid md:grid-cols-4 gap-3">@csrf
                                <input name="metric_key" required placeholder="Key"
                                    class="border-gray-300 rounded-md text-sm">
                                <input name="metric_value" placeholder="Decimal Value"
                                    class="border-gray-300 rounded-md text-sm">
                                <input name="numeric_value" placeholder="Integer Value"
                                    class="border-gray-300 rounded-md text-sm">
                                <input name="ttl_seconds" placeholder="TTL (s)"
                                    class="border-gray-300 rounded-md text-sm">
                                <div class="md:col-span-4 flex justify-end"><button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="operations-tab" class="tab-content p-6" style="display:none;">
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
                                        <input name="title" value="{{ $audit->title }}"
                                            class="w-full border-gray-300 rounded-md text-sm" placeholder="Title"
                                            required>
                                        <textarea name="description" rows="2"
                                            class="w-full border-gray-300 rounded-md text-sm"
                                            placeholder="Description">{{ $audit->description }}</textarea>
                                        <textarea name="scope_summary" rows="2"
                                            class="w-full border-gray-300 rounded-md text-sm"
                                            placeholder="Scope Summary">{{ $audit->scope_summary }}</textarea>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="date" name="planned_start_date"
                                                value="{{ $audit->planned_start_date }}"
                                                class="border-gray-300 rounded-md text-sm">
                                            <input type="date" name="planned_end_date"
                                                value="{{ $audit->planned_end_date }}"
                                                class="border-gray-300 rounded-md text-sm">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="date" name="actual_start_date"
                                                value="{{ $audit->actual_start_date }}"
                                                class="border-gray-300 rounded-md text-sm">
                                            <input type="date" name="actual_end_date"
                                                value="{{ $audit->actual_end_date }}"
                                                class="border-gray-300 rounded-md text-sm">
                                        </div>
                                        <input name="score" value="{{ $audit->score }}" placeholder="Score"
                                            class="w-full border-gray-300 rounded-md text-sm">
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
                                <form method="POST" action="{{ route('audits.tags.add', $audit) }}" class="flex gap-3">
                                    @csrf
                                    <input name="tag_name" placeholder="Existing tag name"
                                        class="flex-1 border-gray-300 rounded-md text-sm">
                                    <button class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Add Tag</button>
                                </form>
                            </div>
                            <!-- Schedules Management -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-md font-medium mb-3">Schedules</h4>
                                <div class="space-y-3 mb-4">
                                    @forelse($audit->schedules as $sch)
                                    <div
                                        class="p-2 border rounded flex justify-between items-center text-xs bg-gray-50">
                                        <div>
                                            <div class="font-medium text-gray-700">{{ ucfirst($sch->frequency) }} • {{
                                                $sch->scheduled_date?->format('M d, Y') }}</div>
                                            <div class="text-[10px] text-gray-500">Next: {{
                                                $sch->next_run_date?->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <form method="POST"
                                            action="{{ route('audits.schedules.delete', [$audit, $sch]) }}">@csrf
                                            @method('DELETE')<button class="px-2 py-0.5 bg-red-600 text-white rounded"
                                                onclick="return confirm('Delete schedule?')">✕</button></form>
                                    </div>
                                    @empty
                                    <p class="text-sm text-gray-500">No schedules.</p>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('audits.schedules.add', $audit) }}"
                                    class="grid md:grid-cols-4 gap-2">@csrf
                                    <select name="frequency" class="border-gray-300 rounded-md text-xs col-span-1">
                                        <option value="once">Once</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="semiannual">Semiannual</option>
                                        <option value="annual">Annual</option>
                                    </select>
                                    <input type="date" name="scheduled_date" required
                                        class="border-gray-300 rounded-md text-xs col-span-1">
                                    <input type="date" name="next_run_date"
                                        class="border-gray-300 rounded-md text-xs col-span-1">
                                    <button
                                        class="px-3 py-1 bg-indigo-600 text-white rounded text-xs col-span-1">Add</button>
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
    </script>
    @endpush
</x-app-layout>