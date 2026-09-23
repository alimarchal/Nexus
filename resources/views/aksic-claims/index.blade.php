@php
    $filter = (array) request('filter', []);
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $badge = [
        'Lodged' => 'bg-amber-100 text-amber-800',
        'Settled' => 'bg-green-100 text-green-800',
        'Rejected' => 'bg-red-100 text-red-800',
    ];
    $summaryTotals = (object) [
        'claims' => $summary->sum('claims'),
        'loans' => $summary->sum('loans'),
        'principal' => $summary->sum('principal'),
        'markup' => $summary->sum('markup'),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="AKSIC Claims" :createRoute="route('aksic-claims.create')" createLabel="Lodge Claim"
            createPermission="lodge aksic claims" :showSearch="true" :showRefresh="true" backRoute="aksic.index" />
    </x-slot>

    {{-- Filters: hidden until the filter (sliders) button is clicked, same as the AKSIC list --}}
    <x-filter-section :action="route('aksic-claims.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    @include('aksic-claims._filters', ['prefix' => 'filter', 'values' => $filter])
                    <div>
                        <label class="{{ $lbl }}" for="filter_status">Status</label>
                        <select id="filter_status" name="filter[status]" class="{{ $control }}">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filter['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="filter_date_from">Claim date from</label>
                        <input id="filter_date_from" type="text" name="filter[date_from]" value="{{ $filter['date_from'] ?? '' }}"
                            placeholder="{{ \App\Support\AksicDate::PLACEHOLDER }}" class="{{ $control }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="filter_date_to">Claim date to</label>
                        <input id="filter_date_to" type="text" name="filter[date_to]" value="{{ $filter['date_to'] ?? '' }}"
                            placeholder="{{ \App\Support\AksicDate::PLACEHOLDER }}" class="{{ $control }}">
                    </div>

                </div>
        <input type="hidden" name="group_by" value="{{ $groupBy }}">
    </x-filter-section>

    @include('aksics._grid-style')

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <x-status-message />

            {{-- Summary by district / region / branch / gender ----------------- --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">
                        Claims summary &mdash; {{ $groups[$groupBy] }}-wise
                    </h3>
                    <div class="flex gap-1 text-xs">
                        @foreach ($groups as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery(['group_by' => $key]) }}"
                                class="rounded-md px-3 py-1.5 font-semibold {{ $groupBy === $key ? 'bg-blue-950 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="p-4">
                <table class="aksic-grid">
                    <thead>
                        <tr>
                            <th class="ctr">#</th>
                            <th>{{ $groups[$groupBy] }}</th>
                            <th class="num">Claims</th>
                            <th class="num">Loans</th>
                            <th class="num">Principal Outstanding</th>
                            <th class="num">Markup Claimed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summary as $row)
                            <tr>
                                <td class="ctr">{{ $loop->iteration }}</td>
                                <td><b>{{ $row->label }}</b></td>
                                <td class="num">{{ number_format($row->claims) }}</td>
                                <td class="num">{{ number_format($row->loans) }}</td>
                                <td class="num">{{ number_format($row->principal, 2) }}</td>
                                <td class="num"><b>{{ number_format($row->markup, 2) }}</b></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ctr">No claims match these filters.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($summary->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td class="num" title="A claim spanning several groups is counted in each">&mdash;</td>
                                <td class="num">{{ number_format($summaryTotals->loans) }}</td>
                                <td class="num">{{ number_format($summaryTotals->principal, 2) }}</td>
                                <td class="num">{{ number_format($summaryTotals->markup, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
                </div>
            </div>

            {{-- Claims list ------------------------------------------------------ --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="p-4">
                <table class="aksic-grid">
                    <thead>
                        <tr>
                            <th>Claim No</th>
                            <th>Claim Date</th>
                            <th>Period</th>
                            <th>Lodged For</th>
                            <th class="num">Loans</th>
                            <th class="num">Markup Claimed</th>
                            <th class="ctr">Status</th>
                            <th class="ctr print:hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claims as $claim)
                            <tr>
                                <td><b>{{ $claim->claim_no }}</b></td>
                                <td>{{ \App\Support\AksicDate::display($claim->claim_date) }}</td>
                                <td>{{ \App\Support\AksicDate::display($claim->period_from) }} &ndash; {{ \App\Support\AksicDate::display($claim->period_to) }}</td>
                                <td>{{ $claim->filterLabel() }}</td>
                                <td class="num">{{ number_format($claim->total_loans) }}</td>
                                <td class="num"><b>{{ number_format((float) $claim->total_markup, 2) }}</b></td>
                                <td class="ctr"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $badge[$claim->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $claim->status }}</span></td>
                                <td class="ctr print:hidden"><a href="{{ route('aksic-claims.show', $claim) }}" class="font-semibold text-blue-700 hover:underline">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="ctr">No claims lodged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="px-3 py-2">{{ $claims->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
