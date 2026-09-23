@php
    $d = fn ($v) => \App\Support\AksicDate::display($v);
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $badge = [
        'Lodged' => 'bg-amber-100 text-amber-800 ring-amber-600/20',
        'Settled' => 'bg-green-100 text-green-800 ring-green-600/20',
        'Rejected' => 'bg-red-100 text-red-800 ring-red-600/20',
    ];
    $btn = 'inline-flex items-center gap-1.5 rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Claim {{ $claim->claim_no }}</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $claim->filterLabel() }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="{{ $btn }} bg-green-800 hover:bg-green-900">Print</button>
                @can('delete aksic claims')
                    @if ($claim->status !== 'Settled')
                        <form method="POST" action="{{ route('aksic-claims.destroy', $claim) }}"
                            onsubmit="return confirm('Delete claim {{ $claim->claim_no }}? Its loans become claimable again.')">
                            @csrf
                            @method('DELETE')
                            <button class="{{ $btn }} bg-red-700 hover:bg-red-800">Delete</button>
                        </form>
                    @endif
                @endcan
                <a href="{{ route('aksic-claims.index') }}" class="{{ $btn }} bg-blue-950 hover:bg-green-800">&larr;</a>
            </div>
        </div>
    </x-slot>

    @include('aksics._grid-style')

    <div class="py-6 print:py-0">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8 print:max-w-none print:px-0">
            <x-status-message />
            <x-validation-errors />

            <div class="hidden text-center print:block">
                <p class="text-lg font-extrabold text-green-900">The Bank of Azad Jammu &amp; Kashmir</p>
                <p class="text-sm font-bold">AKSIC Claim {{ $claim->claim_no }}</p>
            </div>

            {{-- Header ---------------------------------------------------------- --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg print:shadow-none">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase ring-1 ring-inset {{ $badge[$claim->status] ?? '' }}">{{ $claim->status }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Lodged {{ $claim->created_at?->format('d.m.Y H:i') }} @if ($claim->creator) by {{ $claim->creator->name }} @endif
                        @if ($claim->status_date) &middot; {{ $claim->status }} on {{ $d($claim->status_date) }} @endif
                    </p>
                </div>
                <dl class="grid grid-cols-2 gap-4 px-6 py-4 text-sm md:grid-cols-6">
                    <div><dt class="text-xs uppercase text-gray-500">Claim Date</dt><dd class="font-semibold">{{ $d($claim->claim_date) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Period</dt><dd class="font-semibold">{{ $d($claim->period_from) }} &ndash; {{ $d($claim->period_to) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Loans</dt><dd class="font-semibold">{{ number_format($claim->total_loans) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Principal Outstanding</dt><dd class="font-semibold tabular-nums">{{ number_format((float) $claim->total_principal_outstanding, 2) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Markup Claimed</dt><dd class="text-lg font-bold tabular-nums text-green-800 dark:text-green-300">{{ number_format((float) $claim->total_markup, 2) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Remarks</dt><dd>{{ $claim->remarks ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Settle / reject ---------------------------------------------------- --}}
            @can('settle aksic claims')
                @if ($claim->status === 'Lodged')
                    <form method="POST" action="{{ route('aksic-claims.status', $claim) }}" x-data="{ status: 'Settled' }"
                        @submit="if (!confirm('Mark claim {{ $claim->claim_no }} as ' + status + '?')) $event.preventDefault()"
                        class="grid grid-cols-1 items-end gap-4 bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg md:grid-cols-4 print:hidden">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Outcome</label>
                            <select name="status" x-model="status" class="{{ $control }}">
                                <option value="Settled">Settled</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date (D.M.Y)</label>
                            <input type="text" name="status_date" required value="{{ now()->format(\App\Support\AksicDate::DISPLAY) }}" class="{{ $control }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks</label>
                            <input type="text" name="remarks" value="{{ $claim->remarks }}" class="{{ $control }}">
                        </div>
                        <div><x-button class="bg-blue-950 hover:bg-green-800">Update claim</x-button></div>
                    </form>
                @endif
            @endcan

            {{-- District / Region / Branch / Gender breakdown -------------------- --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 print:grid-cols-2">
                @foreach ($groups as $key => $label)
                    <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg print:shadow-none print:border">
                        <h3 class="border-b border-gray-200 px-5 py-3 text-sm font-bold uppercase tracking-wide text-green-800 dark:border-gray-700 dark:text-green-400">{{ $label }}-wise</h3>
                        <table class="aksic-grid">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 text-left">{{ $label }}</th>
                                    <th class="px-4 py-2 text-right">Loans</th>
                                    <th class="px-4 py-2 text-right">Principal O/S</th>
                                    <th class="px-4 py-2 text-right">Markup</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($breakdowns[$key] as $row)
                                    <tr>
                                        <td class="px-4 py-1.5">{{ $row->label }}</td>
                                        <td class="px-4 py-1.5 text-right">{{ $row->loans }}</td>
                                        <td class="px-4 py-1.5 text-right">{{ number_format($row->principal, 2) }}</td>
                                        <td class="px-4 py-1.5 text-right font-semibold">{{ number_format($row->markup, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            {{-- Loans --------------------------------------------------------------- --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg print:shadow-none">
                <h3 class="border-b border-gray-200 px-6 py-3 text-sm font-bold uppercase tracking-wide text-green-800 dark:border-gray-700 dark:text-green-400">Loans in this claim</h3>
                <table class="aksic-grid">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Application No</th>
                            <th class="px-3 py-2 text-left">Account No</th>
                            <th class="px-3 py-2 text-left">Borrower</th>
                            <th class="px-3 py-2 text-left">District</th>
                            <th class="px-3 py-2 text-left">Region</th>
                            <th class="px-3 py-2 text-left">Branch</th>
                            <th class="px-3 py-2 text-center">Gender</th>
                            <th class="px-3 py-2 text-center">Inst.</th>
                            <th class="px-3 py-2 text-right">Principal O/S</th>
                            <th class="px-3 py-2 text-right">Markup</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($claim->items as $item)
                            <tr>
                                <td class="px-3 py-1.5 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-3 py-1.5">
                                    @if ($item->aksic)
                                        <a href="{{ route('aksic.show', $item->aksic) }}" class="text-blue-700 hover:underline print:text-black">{{ $item->aksic->application_no }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-1.5">{{ $item->aksic?->account_no ?? '—' }}</td>
                                <td class="px-3 py-1.5">{{ $item->aksic?->name ?? '—' }}</td>
                                <td class="px-3 py-1.5">{{ $item->district?->name ?? '—' }}</td>
                                <td class="px-3 py-1.5">{{ $item->region?->name ?? '—' }}</td>
                                <td class="px-3 py-1.5">{{ $item->branch?->code ?? '—' }}</td>
                                <td class="px-3 py-1.5 text-center">{{ $item->gender ?? '—' }}</td>
                                <td class="px-3 py-1.5 text-center">{{ $item->installments_count }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format((float) $item->principal_outstanding, 2) }}</td>
                                <td class="px-3 py-1.5 text-right font-semibold">{{ number_format((float) $item->markup_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold dark:bg-gray-700">
                        <tr>
                            <td colspan="9" class="px-3 py-2 text-right">Total</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float) $claim->total_principal_outstanding, 2) }}</td>
                            <td class="px-3 py-2 text-right text-green-800 dark:text-green-300">{{ number_format((float) $claim->total_markup, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
