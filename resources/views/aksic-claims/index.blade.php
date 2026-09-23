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
            createPermission="lodge aksic claims" :showSearch="false" :showRefresh="true" backRoute="aksic.index" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <x-status-message />

            {{-- Filters ----------------------------------------------------- --}}
            <form method="GET" action="{{ route('aksic-claims.index') }}"
                class="overflow-hidden bg-white p-5 shadow-xl dark:bg-gray-800 sm:rounded-lg">
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
                    <div>
                        <label class="{{ $lbl }}" for="group_by">Summary by</label>
                        <select id="group_by" name="group_by" class="{{ $control }}">
                            @foreach ($groups as $key => $label)
                                <option value="{{ $key }}" @selected($groupBy === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <x-button class="bg-blue-950 hover:bg-green-800">Apply</x-button>
                    <a href="{{ route('aksic-claims.index') }}" class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-300">Reset</a>
                </div>
            </form>

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
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ $groups[$groupBy] }}</th>
                            <th class="px-4 py-2 text-right">Claims</th>
                            <th class="px-4 py-2 text-right">Loans</th>
                            <th class="px-4 py-2 text-right">Principal Outstanding</th>
                            <th class="px-4 py-2 text-right">Markup Claimed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 tabular-nums dark:divide-gray-700">
                        @forelse ($summary as $row)
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ $row->label }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row->claims) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row->loans) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row->principal, 2) }}</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ number_format($row->markup, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No claims match these filters.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($summary->isNotEmpty())
                        <tfoot class="bg-gray-100 font-bold tabular-nums dark:bg-gray-700">
                            <tr>
                                <td class="px-4 py-2">Total</td>
                                <td class="px-4 py-2 text-right" title="A claim spanning several groups is counted in each">&mdash;</td>
                                <td class="px-4 py-2 text-right">{{ number_format($summaryTotals->loans) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($summaryTotals->principal, 2) }}</td>
                                <td class="px-4 py-2 text-right text-green-800 dark:text-green-300">{{ number_format($summaryTotals->markup, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Claims list ------------------------------------------------------ --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-green-800 text-xs uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-3 py-2 text-left">Claim No</th>
                            <th class="px-3 py-2 text-left">Claim Date</th>
                            <th class="px-3 py-2 text-left">Period</th>
                            <th class="px-3 py-2 text-left">Lodged For</th>
                            <th class="px-3 py-2 text-right">Loans</th>
                            <th class="px-3 py-2 text-right">Markup Claimed</th>
                            <th class="px-3 py-2 text-center">Status</th>
                            <th class="px-3 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 tabular-nums dark:divide-gray-700">
                        @forelse ($claims as $claim)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-3 py-2 font-semibold">{{ $claim->claim_no }}</td>
                                <td class="px-3 py-2">{{ \App\Support\AksicDate::display($claim->claim_date) }}</td>
                                <td class="px-3 py-2">{{ \App\Support\AksicDate::display($claim->period_from) }} &ndash; {{ \App\Support\AksicDate::display($claim->period_to) }}</td>
                                <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">{{ $claim->filterLabel() }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($claim->total_loans) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format((float) $claim->total_markup, 2) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $badge[$claim->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $claim->status }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <a href="{{ route('aksic-claims.show', $claim) }}" class="font-semibold text-blue-700 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-3 py-8 text-center text-gray-500">No claims lodged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-3 py-2">{{ $claims->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
