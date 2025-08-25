<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Audits
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
            @can('create audits')
            <a href="{{ route('audits.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Audit
            </a>
            @endcan
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
                <a href="{{ route('product.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <!-- FILTER SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display:none">
            <div class="p-6">
                <form method="GET" action="{{ route('audits.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <x-input-filters name="id" label="Audit ID" type="number" />
                        <x-input-filters name="reference_no" label="Reference No" />
                        <x-input-filters name="title" label="Title" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="filter[status]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select Status --</option>
                                @foreach(['planned','in_progress','reporting','issued','closed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected(request('filter.status')===$s)>{{
                                    ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Risk
                                Overall</label>
                            <select name="filter[risk_overall]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select Risk --</option>
                                @foreach(['low','medium','high','critical'] as $r)
                                <option value="{{ $r }}" @selected(request('filter.risk_overall')===$r)>{{ ucfirst($r)
                                    }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Audit Type</label>
                            <select name="filter[audit_type_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select Type --</option>
                                @foreach(($auditTypes ?? []) as $type)
                                <option value="{{ $type->id }}" @selected(request('filter.audit_type_id')==$type->id)>{{
                                    $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lead
                                Auditor</label>
                            <select name="filter[lead_auditor_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select Lead --</option>
                                @foreach(($users ?? []) as $u)
                                <option value="{{ $u->id }}" @selected(request('filter.lead_auditor_id')==$u->id)>{{
                                    $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created By</label>
                            <select name="filter[created_by]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select User --</option>
                                @foreach(($users ?? []) as $u)
                                <option value="{{ $u->id }}" @selected(request('filter.created_by')==$u->id)>{{ $u->name
                                    }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag</label>
                            <select name="filter[audit_tag_id]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">-- Select Tag --</option>
                                @foreach(($tags ?? []) as $tag)
                                <option value="{{ $tag->id }}" @selected(request('filter.audit_tag_id')==$tag->id)>{{
                                    $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Created Date Range -->
                        <x-date-from />
                        <x-date-to />
                        <!-- Planned Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned Start
                                From</label>
                            <input type="date" name="filter[planned_start_from]"
                                value="{{ request('filter.planned_start_from') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned Start
                                To</label>
                            <input type="date" name="filter[planned_start_to]"
                                value="{{ request('filter.planned_start_to') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned End
                                From</label>
                            <input type="date" name="filter[planned_end_from]"
                                value="{{ request('filter.planned_end_from') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned End
                                To</label>
                            <input type="date" name="filter[planned_end_to]"
                                value="{{ request('filter.planned_end_to') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                        <!-- Score Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Score Min</label>
                            <input type="number" step="0.01" name="filter[score_min]"
                                value="{{ request('filter.score_min') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Score Max</label>
                            <input type="number" step="0.01" name="filter[score_max]"
                                value="{{ request('filter.score_max') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-4">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md">Apply
                            Filters</button>
                        <a href="{{ route('audits.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- STATISTICS DASHBOARD -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_audits'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
            </div>
            @php $statusMap = [ 'planned'=>'yellow', 'in_progress'=>'blue', 'reporting'=>'purple', 'issued'=>'orange',
            'closed'=>'green', 'cancelled'=>'gray']; @endphp
            @foreach($statusMap as $key=>$color)
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-{{ $color }}-600">{{ $statistics['status'][$key] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">{{ ucwords(str_replace('_',' ',$key)) }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
            @php $riskColors = ['low'=>'green','medium'=>'yellow','high'=>'orange','critical'=>'red']; @endphp
            @foreach($riskColors as $risk=>$col)
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-xl font-bold text-{{ $col }}-600">{{ $statistics['risk'][$risk] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Risk {{ ucfirst($risk) }}</div>
                </div>
            </div>
            @endforeach
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden  shadow-md transform hover:scale-110 transition duration-300  sm:rounded-lg p-4">
                <div class="text-center">
                    <div class="text-xl font-bold text-indigo-600">{{ $statistics['avg_score'] ?? '-' }}</div>
                    <div class="text-xs text-gray-600">Avg Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            @if(($audits ?? collect())->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="min-w-max w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-3 px-2 text-center">#</th>
                            <th class="py-3 px-2 text-left">Audit Details</th>
                            <th class="py-3 px-2 text-center">Type</th>
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-2 text-center">Risk</th>
                            <th class="py-3 px-2 text-center">Lead</th>
                            <th class="py-3 px-2 text-center">Planned</th>
                            <th class="py-3 px-2 text-center">Actual</th>
                            <th class="py-3 px-2 text-center">Score</th>
                            <th class="py-3 px-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-sm leading-normal">
                        @foreach($audits as $idx => $audit)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-2 text-center font-semibold">{{ $idx + 1 }}</td>
                            <td class="py-3 px-2">
                                <div class="flex flex-col">
                                    <div class="font-semibold text-blue-600">
                                        <a href="{{ route('audits.show',$audit) }}" class="hover:underline">{{
                                            $audit->reference_no }}</a>
                                    </div>
                                    <div class="text-gray-800 font-medium">{{ Str::limit($audit->title,40) }}</div>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-center">{{ $audit->type?->name ?? '-' }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">{{
                                    ucwords(str_replace('_',' ',$audit->status)) }}</span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold @class([
                                            'bg-green-100 text-green-800' => $audit->risk_overall==='low',
                                            'bg-yellow-100 text-yellow-800' => $audit->risk_overall==='medium',
                                            'bg-orange-100 text-orange-800' => $audit->risk_overall==='high',
                                            'bg-red-100 text-red-800' => $audit->risk_overall==='critical',
                                            'bg-gray-100 text-gray-800' => !$audit->risk_overall,
                                        ])">{{ $audit->risk_overall ? ucfirst($audit->risk_overall) : '-' }}</span>
                            </td>
                            <td class="py-3 px-2 text-center">{{ $audit->leadAuditor?->name ?? '-' }}</td>
                            <td class="py-3 px-2 text-center text-xs">
                                @if($audit->planned_start_date)
                                {{ $audit->planned_start_date?->format('d M') }} - {{
                                $audit->planned_end_date?->format('d M') }}
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center text-xs">
                                @if($audit->actual_start_date)
                                {{ $audit->actual_start_date?->format('d M') }} - {{ $audit->actual_end_date?->format('d
                                M') }}
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">{{ $audit->score ?? '-' }}</td>
                            <td class="py-3 px-2 text-center">
                                @can('view audits')
                                <a href="{{ route('audits.show',$audit) }}"
                                    class="inline-flex items-center px-3 py-1 text-white bg-blue-600 hover:bg-blue-700 rounded-md text-xs font-semibold">View</a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50">
                    {{ $audits->appends(request()->query())->links() }}
                </div>
            </div>
            @else
            <div class="p-8 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">No audits found</h3>
                <p class="text-gray-600 mb-4">@if(request()->hasAny(['filter'])) No audits match your current filters.
                    @else There are no audits yet. @endif</p>
                <div class="space-x-4">
                    @if(request()->hasAny(['filter']))
                    <a href="{{ route('audits.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 rounded-md text-white text-xs">Clear
                        Filters</a>
                    @endif
                    <a href="{{ route('audits.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 rounded-md text-white text-xs">Create
                        First Audit</a>
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
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
            function showFilters(){targetDiv.style.display='block';targetDiv.style.opacity='0';targetDiv.style.transform='translateY(-20px)';setTimeout(()=>{targetDiv.style.opacity='1';targetDiv.style.transform='translateY(0)';},10);} 
            function hideFilters(){targetDiv.style.opacity='0';targetDiv.style.transform='translateY(-20px)';setTimeout(()=>{targetDiv.style.display='none';},300);} 
            btn.addEventListener('click',e=>{e.stopPropagation(); if(targetDiv.style.display==='none'||!targetDiv.style.display){showFilters();} else {hideFilters();}});
            document.addEventListener('click',e=>{ if(targetDiv.style.display==='block' && !targetDiv.contains(e.target) && !btn.contains(e.target)){ hideFilters(); }});
            targetDiv.addEventListener('click',e=>e.stopPropagation());
        });
    </script>
    @endpush
</x-app-layout>