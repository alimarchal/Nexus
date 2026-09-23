@php
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $values = [
        'district_id' => old('district_id', request('district_id')),
        'region_id' => old('region_id', request('region_id')),
        'branch_id' => old('branch_id', request('branch_id')),
        'gender' => old('gender', request('gender')),
    ];
    $periodFrom = old('period_from', request('period_from'));
    $periodTo = old('period_to', request('period_to'));
    $display = fn ($v) => $v ? \App\Support\AksicDate::display(\App\Support\AksicDate::toDatabase($v), '') : '';

    $breakdown = function (string $key) use ($preview, $districts, $regions, $branches) {
        $districtNames = $districts->pluck('name', 'id');
        $regionNames = $regions->pluck('name', 'id');
        $branchNames = $branches->mapWithKeys(fn ($b) => [$b->id => $b->code.' - '.$b->name]);

        return $preview->groupBy(fn ($line) => match ($key) {
            'district' => $districtNames[$line['district_id']] ?? 'Not recorded',
            'region' => $regionNames[$line['region_id']] ?? 'Not recorded',
            'branch' => $branchNames[$line['branch_id']] ?? 'Not recorded',
            'gender' => $line['gender'] ?? 'Not recorded',
        })->map(fn ($lines) => ['loans' => $lines->count(), 'markup' => $lines->sum('markup_amount')])->sortKeys();
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Lodge AKSIC Claim" :showSearch="false" :showRefresh="false" backRoute="aksic-claims.index" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <x-validation-errors />

            {{-- Step 1: period + scope -> preview ------------------------------ --}}
            <form method="GET" action="{{ route('aksic-claims.create') }}"
                class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400">1. Claim period &amp; scope</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Markup of approved loans' instalments falling due in this period is claimed. Leave a filter blank to include all.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3">
                    <div>
                        <label class="{{ $lbl }}" for="period_from">Period from <span class="text-red-600">*</span></label>
                        <input id="period_from" type="text" name="period_from" value="{{ $display($periodFrom) }}" required
                            placeholder="{{ \App\Support\AksicDate::PLACEHOLDER }}" class="{{ $control }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="period_to">Period to <span class="text-red-600">*</span></label>
                        <input id="period_to" type="text" name="period_to" value="{{ $display($periodTo) }}" required
                            placeholder="{{ \App\Support\AksicDate::PLACEHOLDER }}" class="{{ $control }}">
                    </div>
                    <div class="hidden md:block"></div>
                    @include('aksic-claims._filters', ['prefix' => '', 'values' => $values])
                </div>
                <div class="flex justify-end border-t border-gray-200 px-6 py-3 dark:border-gray-700">
                    <x-button class="bg-blue-950 hover:bg-green-800">Preview eligible loans</x-button>
                </div>
            </form>

            {{-- Step 2: preview + lodge ----------------------------------------- --}}
            @if ($preview !== null)
                <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400">2. Eligible loans</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $display($criteria['period_from']) }} &ndash; {{ $display($criteria['period_to']) }}
                                &middot; loans already in a live claim for an overlapping period are excluded.
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase text-gray-500">{{ number_format($preview->count()) }} loans &middot; markup to claim</p>
                            <p class="text-xl font-bold tabular-nums text-green-800 dark:text-green-300">{{ number_format($preview->sum('markup_amount'), 2) }}</p>
                        </div>
                    </div>

                    @if ($preview->isEmpty())
                        <p class="px-6 py-10 text-center text-sm text-gray-500">No approved loan has unclaimed instalments due in this period for this scope.</p>
                    @else
                        <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 lg:grid-cols-4">
                            @foreach ($groups as $key => $label)
                                <div class="rounded-md border border-gray-200 dark:border-gray-700">
                                    <p class="border-b border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold uppercase text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300">{{ $label }}-wise</p>
                                    <table class="w-full text-xs tabular-nums">
                                        @foreach ($breakdown($key) as $name => $row)
                                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-700">
                                                <td class="px-3 py-1.5">{{ $name }}</td>
                                                <td class="px-3 py-1.5 text-right text-gray-500">{{ $row['loans'] }}</td>
                                                <td class="px-3 py-1.5 text-right font-semibold">{{ number_format($row['markup'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endforeach
                        </div>

                        <div class="max-h-96 overflow-auto border-t border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Application No</th>
                                        <th class="px-3 py-2 text-left">Account No</th>
                                        <th class="px-3 py-2 text-left">Borrower</th>
                                        <th class="px-3 py-2 text-left">District</th>
                                        <th class="px-3 py-2 text-left">Branch</th>
                                        <th class="px-3 py-2 text-center">Gender</th>
                                        <th class="px-3 py-2 text-center">Instalments</th>
                                        <th class="px-3 py-2 text-right">Principal Outstanding</th>
                                        <th class="px-3 py-2 text-right">Markup</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 tabular-nums dark:divide-gray-700">
                                    @foreach ($preview as $line)
                                        <tr>
                                            <td class="px-3 py-1.5"><a class="text-blue-700 hover:underline" href="{{ route('aksic.show', $line['aksic']) }}" target="_blank">{{ $line['aksic']->application_no }}</a></td>
                                            <td class="px-3 py-1.5">{{ $line['aksic']->account_no ?? '—' }}</td>
                                            <td class="px-3 py-1.5">{{ $line['aksic']->name }}</td>
                                            <td class="px-3 py-1.5">{{ $line['aksic']->district?->name ?? '—' }}</td>
                                            <td class="px-3 py-1.5">{{ $line['aksic']->branch?->code ?? '—' }}</td>
                                            <td class="px-3 py-1.5 text-center">{{ $line['gender'] ?? '—' }}</td>
                                            <td class="px-3 py-1.5 text-center">{{ $line['installments_count'] }}</td>
                                            <td class="px-3 py-1.5 text-right">{{ number_format($line['principal_outstanding'], 2) }}</td>
                                            <td class="px-3 py-1.5 text-right font-semibold">{{ number_format($line['markup_amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <form method="POST" action="{{ route('aksic-claims.store') }}" x-data="{ submitting: false }"
                            @submit="if (submitting || !confirm('Lodge this claim for {{ $preview->count() }} loans, markup {{ number_format($preview->sum('markup_amount'), 2) }}?')) { $event.preventDefault(); return; } submitting = true;"
                            class="grid grid-cols-1 gap-4 border-t border-gray-200 p-6 dark:border-gray-700 md:grid-cols-3">
                            @csrf
                            @foreach (['period_from', 'period_to', 'district_id', 'region_id', 'branch_id', 'gender'] as $field)
                                <input type="hidden" name="{{ $field }}" value="{{ $criteria[$field] ?? '' }}">
                            @endforeach
                            <div>
                                <label class="{{ $lbl }}" for="claim_date">Claim date <span class="text-red-600">*</span></label>
                                <input id="claim_date" type="text" name="claim_date" required
                                    value="{{ old('claim_date', now()->format(\App\Support\AksicDate::DISPLAY)) }}"
                                    placeholder="{{ \App\Support\AksicDate::PLACEHOLDER }}" class="{{ $control }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="{{ $lbl }}" for="remarks">Remarks</label>
                                <input id="remarks" type="text" name="remarks" value="{{ old('remarks') }}" maxlength="2000" class="{{ $control }}">
                            </div>
                            <div class="flex justify-end md:col-span-3">
                                <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-5 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-700 disabled:opacity-60">
                                    <span x-show="!submitting">Lodge claim</span>
                                    <span x-show="submitting">Lodging…</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
