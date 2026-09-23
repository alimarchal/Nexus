@php
    use App\Services\AksicBudgetService;

    $t = $position['totals'];
    $rows = $position['rows'];
    $m = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($used, $limit) => $limit > 0 ? round($used / $limit * 100, 1) : ($used > 0 ? 100 : 0);
    $barClass = fn ($p) => $p > 100 ? 'over' : ($p >= 85 ? 'warn' : '');
    $usedPct = $pct($t['used'], $t['allocated']);
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $enfBadge = ['block' => 'bg-red-100 text-red-800', 'warn' => 'bg-amber-100 text-amber-800', 'report' => 'bg-gray-100 text-gray-700'];
    $canManage = auth()->user()?->can('manage aksic budget');
    $btn = 'inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">AKSIC Markup Budget</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $budget->title }}
                    @if ($budget->reference_no) &middot; {{ $budget->reference_no }} @endif
                    @if ($budget->reference_date) &middot; {{ $budget->reference_date->format('d.m.Y') }} @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($canManage)
                    <a href="{{ route('aksic-budgets.edit', $budget) }}" class="{{ $btn }}">Edit settings</a>
                    <a href="{{ route('aksic-budgets.create') }}" class="{{ $btn }}">New version</a>
                @endif
                <button type="button" onclick="window.print()" class="{{ $btn }} bg-green-800">Print</button>
                <a href="{{ route('aksic.index') }}" class="{{ $btn }}">&larr; AKSIC</a>
            </div>
        </div>
    </x-slot>

    @include('aksics._grid-style')

    <div class="py-6 print:py-0" x-data="{ revise: null, redistribute: false }">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8 print:max-w-none print:px-0">
            <x-status-message />
            <x-validation-errors />

            <div class="hidden text-center print:block">
                <p style="font-size:17px;font-weight:800;color:#14532d">The Bank of Azad Jammu &amp; Kashmir</p>
                <p style="font-size:13px;font-weight:700">AKSIC PM Youth Loan Scheme &mdash; Markup Budget Position</p>
                <p style="font-size:10px;color:#555">{{ $budget->title }} &middot; Printed {{ now()->format('d.m.Y H:i') }}</p>
            </div>

            @unless ($budget->is_active)
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 print:hidden">
                    This budget version is <b>inactive</b>; approvals are checked against the active budget.
                    @if ($canManage)
                        <form method="POST" action="{{ route('aksic-budgets.activate', $budget) }}" class="mt-2 inline" onsubmit="return confirm('Make this the active budget?')">
                            @csrf
                            <button class="{{ $btn }} ml-2 bg-emerald-700">Activate this version</button>
                        </form>
                    @endif
                </div>
            @endunless

            {{-- Summary --------------------------------------------------------- --}}
            <table class="aksic-grid">
                <thead>
                    <tr>
                        <th class="num">Total budget</th>
                        <th class="num">Allocated to districts</th>
                        <th class="num">Unallocated</th>
                        <th class="num">Markup used (approved cases)</th>
                        <th class="num">Remaining</th>
                        <th class="ctr">Used</th>
                        <th class="ctr">Loans</th>
                        <th class="ctr">At approval</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="num"><b>{{ $m($t['budget']) }}</b></td>
                        <td class="num">{{ $m($t['allocated']) }}</td>
                        <td class="num {{ $t['unallocated'] < 0 ? 'neg' : '' }}">{{ $m($t['unallocated']) }}</td>
                        <td class="num">{{ $m($t['used']) }}</td>
                        <td class="num {{ $t['remaining'] < 0 ? 'neg' : '' }}"><b>{{ $m($t['remaining']) }}</b></td>
                        <td class="ctr" style="min-width:110px">{{ $usedPct }}%<div class="bar"><span class="{{ $barClass($usedPct) }}" style="width: {{ min(100, $usedPct) }}%"></span></div></td>
                        <td class="ctr">{{ number_format($t['loans']) }}</td>
                        <td class="ctr"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $enfBadge[$budget->enforcement] ?? '' }}">{{ \App\Models\AksicBudget::ENFORCEMENTS[$budget->enforcement] }}</span></td>
                    </tr>
                </tbody>
            </table>

            {{-- View switch --------------------------------------------------- --}}
            <div class="flex flex-wrap items-center justify-between gap-2 print:hidden">
                <div class="flex gap-1 text-xs">
                    @foreach (['district' => 'District-wise', 'gender' => 'Gender-wise', 'nature' => 'Existing / New business'] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => null]) }}"
                            class="rounded-md px-3 py-1.5 font-semibold {{ $view === $key ? 'bg-blue-950 text-white' : 'bg-white text-gray-700 shadow hover:bg-gray-100' }}">{{ $label }}</a>
                    @endforeach
                </div>
                @if ($canManage)
                    <button type="button" @click="redistribute = !redistribute" class="{{ $btn }} bg-gray-700">Redistribute by population %</button>
                @endif
            </div>

            @if ($canManage)
                <form x-show="redistribute" x-cloak method="POST" action="{{ route('aksic-budgets.redistribute', $budget) }}"
                    onsubmit="return confirm('Re-split the total budget across all districts by population %? Every change is recorded.')"
                    class="grid grid-cols-1 items-end gap-4 rounded-lg bg-white p-5 shadow md:grid-cols-4 print:hidden dark:bg-gray-800">
                    @csrf
                    <p class="text-xs text-gray-600 md:col-span-4 dark:text-gray-300">Sets every district allocation to <b>total &times; population %</b>. Stops if any district would fall below the markup it has already used.</p>
                    <div><label class="{{ $lbl }}">Letter no.</label><input name="reference_no" class="{{ $control }}"></div>
                    <div><label class="{{ $lbl }}">Letter date (D.M.Y)</label><input name="reference_date" placeholder="dd.mm.yyyy" class="{{ $control }}"></div>
                    <div><label class="{{ $lbl }}">Reason <span class="text-red-600">*</span></label><input name="reason" required class="{{ $control }}"></div>
                    <div><x-button class="bg-blue-950 hover:bg-green-800">Redistribute</x-button></div>
                </form>
            @endif

            {{-- Position table -------------------------------------------------- --}}
            <div class="overflow-x-auto">
                @if ($view === 'district')
                    <table class="aksic-grid">
                        <thead>
                            <tr>
                                <th class="ctr">#</th><th>District</th><th class="num">Population %</th>
                                <th class="num">Allocation</th><th class="num">Markup used</th><th class="num">Remaining</th>
                                <th class="ctr">Used</th><th class="ctr">Loans</th>
                                @if ($canManage) <th class="ctr print:hidden">Revise</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php $p = $row['total']['percent']; @endphp
                                <tr>
                                    <td class="ctr">{{ $loop->iteration }}</td>
                                    <td><b>{{ $row['district'] }}</b></td>
                                    <td class="num">{{ number_format($row['population_percentage'], 2) }}</td>
                                    <td class="num">{{ $m($row['total']['allocated']) }}</td>
                                    <td class="num">{{ $m($row['total']['used']) }}</td>
                                    <td class="num {{ $row['total']['remaining'] < 0 ? 'neg' : '' }}">{{ $m($row['total']['remaining']) }}</td>
                                    <td class="ctr" style="min-width:100px">{{ $p }}%<div class="bar"><span class="{{ $barClass($p) }}" style="width: {{ min(100, $p) }}%"></span></div></td>
                                    <td class="ctr">{{ $row['loans'] }}</td>
                                    @if ($canManage)
                                        <td class="ctr print:hidden">
                                            <button type="button" class="font-semibold text-emerald-700 hover:underline"
                                                @click="revise = { action: 'enhancement', id: {{ $row['allocation']->id }}, district: @js($row['district']), allocated: @js($m($row['total']['allocated'])), used: @js($m($row['total']['used'])) }">Enhance</button>
                                            &middot;
                                            <button type="button" class="font-semibold text-red-700 hover:underline"
                                                @click="revise = { action: 'reduction', id: {{ $row['allocation']->id }}, district: @js($row['district']), allocated: @js($m($row['total']['allocated'])), used: @js($m($row['total']['used'])) }">Reduce</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td><td>Total</td><td class="num">{{ number_format($rows->sum('population_percentage'), 2) }}</td>
                                <td class="num">{{ $m($t['allocated']) }}</td><td class="num">{{ $m($t['used']) }}</td>
                                <td class="num">{{ $m($t['remaining']) }}</td><td class="ctr">{{ $usedPct }}%</td><td class="ctr">{{ $t['loans'] }}</td>
                                @if ($canManage) <td class="print:hidden"></td> @endif
                            </tr>
                        </tfoot>
                    </table>
                @else
                    @php
                        $buckets = $view === 'gender' ? AksicBudgetService::GENDERS : AksicBudgetService::NATURES;
                        $key = $view === 'gender' ? 'genders' : 'natures';
                        $firstRule = optional($rows->first())['allocation']?->rule;
                        $headerPct = $view === 'gender'
                            ? ['male' => $firstRule?->male_percentage ?? 48, 'female' => $firstRule?->female_percentage ?? 48,
                               'special' => $firstRule?->special_person_percentage ?? 2, 'transgender' => $firstRule?->transgender_percentage ?? 2]
                            : ['existing' => $budget->existing_business_percentage, 'new' => $budget->new_business_percentage];
                    @endphp
                    <table class="aksic-grid">
                        <thead>
                            <tr class="group">
                                <th rowspan="2">District</th>
                                @foreach ($buckets as $k => $label)
                                    <th colspan="3">{{ $label }} ({{ rtrim(rtrim(number_format((float) $headerPct[$k], 2), '0'), '.') }}%)</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($buckets as $label)
                                    <th class="num">Allocation</th><th class="num">Used</th><th class="num">Remaining</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td><b>{{ $row['district'] }}</b></td>
                                    @foreach ($buckets as $k => $label)
                                        @php $b = $row[$key][$k]; @endphp
                                        <td class="num">{{ $m($b['allocated']) }}</td>
                                        <td class="num">{{ $m($b['used']) }}</td>
                                        <td class="num {{ $b['remaining'] < 0 ? 'neg' : '' }}">{{ $m($b['remaining']) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                @foreach ($buckets as $k => $label)
                                    <td class="num">{{ $m($t[$key][$k]['allocated']) }}</td>
                                    <td class="num">{{ $m($t[$key][$k]['used']) }}</td>
                                    <td class="num">{{ $m($t[$key][$k]['remaining']) }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            {{-- Revision history -------------------------------------------------- --}}
            <div class="bg-white p-5 shadow-xl dark:bg-gray-800 sm:rounded-lg print:shadow-none">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Revision history</h3>
                <table class="aksic-grid">
                    <thead>
                        <tr>
                            <th>Date</th><th>Action</th><th>District</th><th class="num">Previous</th><th class="num">Change</th>
                            <th class="num">New</th><th>Letter</th><th>Reason</th><th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($revisions as $rev)
                            <tr>
                                <td style="white-space:nowrap">{{ $rev->created_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $actions[$rev->action] ?? $rev->action }}</td>
                                <td>{{ $rev->district?->name ?? 'Whole budget' }}</td>
                                <td class="num">{{ $rev->previous_amount === null ? '—' : $m($rev->previous_amount) }}</td>
                                <td class="num {{ (float) $rev->change_amount < 0 ? 'neg' : '' }}">{{ $rev->change_amount === null ? '—' : ((float) $rev->change_amount > 0 ? '+' : '').$m($rev->change_amount) }}</td>
                                <td class="num">{{ $rev->new_amount === null ? '—' : $m($rev->new_amount) }}</td>
                                <td>{{ $rev->reference_no }}{{ $rev->reference_date ? ' ('.$rev->reference_date->format('d.m.Y').')' : '' }}</td>
                                <td>{{ $rev->reason }}</td>
                                <td>{{ $rev->creator?->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="ctr">No changes recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2 print:hidden">{{ $revisions->links() }}</div>
            </div>

            {{-- Versions ------------------------------------------------------------ --}}
            @if ($versions->count() > 1)
                <div class="bg-white p-5 shadow-xl dark:bg-gray-800 sm:rounded-lg print:hidden">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Budget versions</h3>
                    <table class="aksic-grid">
                        <thead><tr><th>Title</th><th class="num">Total</th><th>Created</th><th class="ctr">Status</th><th class="ctr">Open</th></tr></thead>
                        <tbody>
                            @foreach ($versions as $v)
                                <tr>
                                    <td>{{ $v->title }}</td><td class="num">{{ $m($v->total_amount) }}</td>
                                    <td>{{ $v->created_at?->format('d.m.Y') }}</td>
                                    <td class="ctr">{{ $v->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="ctr"><a class="text-blue-700 hover:underline" href="{{ route('aksic-budgets.show', $v) }}">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Enhance / reduce modal ------------------------------------------------------- --}}
        @if ($canManage)
            <div x-show="revise" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4 print:hidden" @keydown.escape.window="revise = null">
                <form method="POST" :action="revise ? '{{ url('product/aksic-budgets/'.$budget->id.'/allocations') }}/' + revise.id + '/revise' : '#'"
                    @click.outside="revise = null" class="w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-xl">
                    @csrf
                    <input type="hidden" name="action" :value="revise?.action">
                    <div class="border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="(revise?.action === 'enhancement' ? 'Enhance' : 'Reduce') + ' allocation — ' + (revise?.district ?? '')"></h3>
                        <p class="mt-1 text-xs text-gray-500">Current allocation <b x-text="revise?.allocated"></b> &middot; used <b x-text="revise?.used"></b></p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-2">
                        <div class="md:col-span-2"><label class="{{ $lbl }}">Amount (Rs) <span class="text-red-600">*</span></label><input name="amount" type="number" step="0.01" min="0.01" required class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Letter no.</label><input name="reference_no" class="{{ $control }}"></div>
                        <div><label class="{{ $lbl }}">Letter date (D.M.Y)</label><input name="reference_date" placeholder="dd.mm.yyyy" class="{{ $control }}"></div>
                        <div class="md:col-span-2"><label class="{{ $lbl }}">Reason <span class="text-red-600">*</span></label><input name="reason" required class="{{ $control }}"></div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
                            <input type="checkbox" name="adjust_total" value="1" checked class="rounded border-gray-300">
                            <span x-text="revise?.action === 'enhancement' ? 'Also increase the total budget by this amount (new sanction)' : 'Also decrease the total budget by this amount (surrender)'"></span>
                        </label>
                        <p class="text-xs text-gray-500 md:col-span-2">Untick to move money within the existing total (it then shows as allocated / unallocated).</p>
                    </div>
                    <div class="flex justify-end gap-3 bg-gray-100 px-6 py-3">
                        <button type="button" @click="revise = null" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700">Cancel</button>
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-700">Save</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
