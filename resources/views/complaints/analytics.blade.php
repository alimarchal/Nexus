<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Complaint Analytics &
                Insights</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-status-message />

            <!-- FILTERS -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200">
                <div class="p-5">
                    <form id="analytics-filters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Date From</label>
                            <input type="date" name="date_from"
                                value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded" />
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded" />
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Status</label>
                            <select name="filter[status]" class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                @foreach(['Open','In Progress','Pending','Resolved','Closed','Reopened'] as $s)
                                <option value="{{ $s }}" {{ request('filter.status')===$s?'selected':''}}>{{ $s }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Priority</label>
                            <select name="filter[priority]" class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                @foreach(['Low','Medium','High','Critical'] as $p)
                                <option value="{{ $p }}" {{ request('filter.priority')===$p?'selected':''}}>{{ $p }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Category</label>
                            <input type="text" name="filter[category]" value="{{ request('filter.category') }}"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded" placeholder="e.g. Harassment" />
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Escalated</label>
                            <select name="filter[escalated]" class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.escalated')==='1' ?'selected':''}}>Yes</option>
                                <option value="0" {{ request('filter.escalated')==='0' ?'selected':''}}>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Harassment Only</label>
                            <select name="filter[harassment_only]"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.harassment_only')==='1' ?'selected':''}}>Yes
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Has Witnesses</label>
                            <select name="filter[has_witnesses]"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.has_witnesses')==='1' ?'selected':''}}>Yes</option>
                                <option value="0" {{ request('filter.has_witnesses')==='0' ?'selected':''}}>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Confidential</label>
                            <select name="filter[harassment_confidential]"
                                class="w-full border-gray-300 dark:bg-gray-900 rounded">
                                <option value="">All</option>
                                <option value="1" {{ request('filter.harassment_confidential')==='1' ?'selected':''}}>
                                    Yes</option>
                                <option value="0" {{ request('filter.harassment_confidential')==='0' ?'selected':''}}>No
                                </option>
                            </select>
                        </div>
                        <div class="lg:col-span-4 flex space-x-3 pt-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-semibold">Apply</button>
                            <button type="button" id="reset-filters"
                                class="px-4 py-2 bg-gray-500 text-white rounded text-xs font-semibold">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DASHBOARD CARDS -->
            <div id="metrics-cards" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"></div>

            <!-- TREND & PERFORMANCE -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow border p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Monthly Trend</h3>
                        <span class="text-xs text-gray-500" id="trend-date-range"></span>
                    </div>
                    <div class="h-64"><canvas id="monthlyTrendChart"></canvas></div>
                </div>
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border p-5">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Resolution Performance</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-300">Avg Resolution Time: <span
                                id="avg-resolution" class="font-semibold">-</span></div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">SLA Compliance: <span id="sla-compliance"
                                class="font-semibold">-</span></div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border p-5">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Status Distribution</h3>
                        <ul id="status-distribution" class="space-y-1 text-xs"></ul>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border p-5">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Priority Distribution</h3>
                    <ul id="priority-distribution" class="space-y-1 text-xs"></ul>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border p-5">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Source Distribution</h3>
                    <ul id="source-distribution" class="space-y-1 text-xs"></ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const initialPayload = @json($initialPayload);
        const initialMonthlyTrend = @json($monthlyTrend);

        const cardConfig = [
            { key:'total', label:'Total', color:'bg-blue-50 text-blue-700 border-blue-200', filter:null, tooltip:'All complaints in selected period' },
            { key:'open', label:'Open', color:'bg-yellow-50 text-yellow-700 border-yellow-200', filter:{'filter[status]':'Open'}, tooltip:'Open / In Progress / Pending aggregated', aggregate:true },
            { key:'overdue', label:'Overdue', color:'bg-red-50 text-red-700 border-red-200', filter:{}, tooltip:'Past expected resolution and not closed' },
            { key:'sla_breached', label:'SLA Breach', color:'bg-purple-50 text-purple-700 border-purple-200', filter:{'filter[sla_breached]':'1'}, tooltip:'Marked as SLA breached' },
            { key:'unassigned', label:'Unassigned', color:'bg-gray-50 text-gray-700 border-gray-200', filter:{'filter[assigned_to]':'unassigned'}, tooltip:'No current assignee' },
            { key:'escalated', label:'Escalated', color:'bg-orange-50 text-orange-700 border-orange-200', filter:{'filter[escalated]':'1'}, tooltip:'Has escalation record(s)' },
            { key:'harassment', label:'Harassment', color:'bg-pink-50 text-pink-700 border-pink-200', filter:{'filter[harassment_only]':'1'}, tooltip:'Category Harassment' },
            { key:'harassment_confidential', label:'Confidential', color:'bg-pink-100 text-pink-800 border-pink-300', filter:{'filter[harassment_confidential]':'1'}, tooltip:'Confidential harassment cases' },
            { key:'with_witnesses', label:'With Witnesses', color:'bg-indigo-50 text-indigo-700 border-indigo-200', filter:{'filter[has_witnesses]':'1'}, tooltip:'At least one witness added' },
            { key:'resolved', label:'Resolved', color:'bg-green-50 text-green-700 border-green-200', filter:{'filter[status]':'Resolved'}, tooltip:'Resolved / Closed aggregated', aggregate:true },
        ];

        let monthlyChart = null;

        function renderCards(metrics){
            const container = document.getElementById('metrics-cards');
            container.innerHTML = '';
            cardConfig.slice(0,6).forEach(cfg => { // first row core cards
                const val = metrics[cfg.key] ?? 0;
                const div = document.createElement('div');
                div.className = `cursor-pointer select-none border rounded-lg p-3 flex flex-col justify-between shadow-sm hover:shadow transition ${cfg.color}`;
                div.title = cfg.tooltip;
                div.innerHTML = `<div class='text-xs uppercase font-semibold tracking-wide'>${cfg.label}</div><div class='mt-1 text-2xl font-bold'>${val}</div>`;
                div.addEventListener('click', ()=>applyCardFilter(cfg));
                container.appendChild(div);
            });
        }

        function applyCardFilter(cfg){
            if(!cfg.filter) return; // total has no direct filter
            const form = document.getElementById('analytics-filters');
            Object.entries(cfg.filter).forEach(([k,v])=>{
                // Attempt to locate matching input by name
                const input = form.querySelector(`[name='${k}']`);
                if(input){ input.value = v; }
            });
            fetchAndUpdate();
        }

        function renderDistributions(data, elementId, labelKey='status'){
            const ul = document.getElementById(elementId);
            ul.innerHTML='';
            data.forEach(item=>{
                const li=document.createElement('li');
                li.className='flex justify-between';
                li.innerHTML=`<span class='font-medium'>${item[labelKey] ?? item['source']}</span><span class='text-gray-600'>${item.count}</span>`;
                ul.appendChild(li);
            });
        }

        function renderMonthlyTrend(trend){
            const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
            const labels = trend.map(t=> new Date(t.year, t.month-1).toLocaleDateString('en-US',{month:'short', year:'2-digit'}));
            const counts = trend.map(t=> t.count);
            if(monthlyChart){ monthlyChart.destroy(); }
            monthlyChart = new Chart(ctx, {
                type:'line',
                data:{ labels, datasets:[{ label:'Complaints', data:counts, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.15)', tension:.35, fill:true }]},
                options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }}}}
            });
        }

        function applyInitial(){
            renderCards(initialPayload);
            document.getElementById('avg-resolution').textContent = initialPayload.avg_resolution_time_minutes ? (initialPayload.avg_resolution_time_minutes/60).toFixed(1)+ ' h' : 'N/A';
            document.getElementById('sla-compliance').textContent = (initialPayload.sla_compliance||0).toFixed(1)+'%';
            renderMonthlyTrend(initialMonthlyTrend);
            renderDistributions(@json($statusDistribution), 'status-distribution', 'status');
            renderDistributions(@json($priorityDistribution), 'priority-distribution', 'priority');
            renderDistributions(@json($sourceDistribution), 'source-distribution', 'source');
        }

        async function fetchAndUpdate(){
            const form = document.getElementById('analytics-filters');
            const params = new URLSearchParams(new FormData(form));
            const res = await fetch(`{{ route('complaints.analytics-data') }}?${params.toString()}`);
            if(!res.ok) return;
            const json = await res.json();
            renderCards(json.metrics);
            document.getElementById('avg-resolution').textContent = json.metrics.avgResolutionTime ? (json.metrics.avgResolutionTime/60).toFixed(1)+' h' : (json.metrics.avg_resolution_time_minutes ? (json.metrics.avg_resolution_time_minutes/60).toFixed(1)+' h' : 'N/A');
            document.getElementById('sla-compliance').textContent = (json.metrics.sla_compliance||0).toFixed(1)+'%';
            renderDistributions(json.statusDistribution, 'status-distribution', 'status');
            renderDistributions(json.priorityDistribution, 'priority-distribution', 'priority');
            renderDistributions(json.sourceDistribution, 'source-distribution', 'source');
            renderMonthlyTrend(json.monthlyTrend);
        }

        document.getElementById('analytics-filters').addEventListener('submit', function(e){ e.preventDefault(); fetchAndUpdate(); });
        document.getElementById('reset-filters').addEventListener('click', function(){
            const form = document.getElementById('analytics-filters');
            form.reset();
            fetchAndUpdate();
        });

        applyInitial();
    </script>
    @endpush
</x-app-layout>