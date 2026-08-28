<hr class="my-6 border-gray-200 dark:border-gray-700">
<div class="flex items-center justify-between gap-4">
    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Transfer History</h3>
    @can('transfer file management systems')
        <a href="{{ route('file-management-systems.transfer', $fileManagementSystem) }}" class="rounded-md bg-blue-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-900">Transfer</a>
    @endcan
</div>

@if ($fileManagementSystem->transfers->isNotEmpty())
    <div class="mt-3 overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900"><tr>
                <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Transfer</th><th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Reason</th><th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">People</th><th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Decision</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @foreach ($fileManagementSystem->transfers as $transfer)
                    <tr>
                        <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200">{{ \Illuminate\Support\Str::headline($transfer->status) }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::headline($transfer->source_fileable_type) }} #{{ $transfer->source_fileable_id }} to {{ \Illuminate\Support\Str::headline($transfer->destination_fileable_type) }} #{{ $transfer->destination_fileable_id }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $transfer->reason }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Requested: {{ $transfer->requester?->name ?? 'System' }}<br>Custodian: {{ $transfer->recipient?->name ?? 'Unassigned' }}<br>Approver: {{ $transfer->decider?->name ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            @if ($transfer->status === 'pending' && in_array($transfer->id, $approvableTransferIds, true))
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('file-management-systems.transfers.decide', [$fileManagementSystem, $transfer]) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="approved"><button class="rounded-md bg-green-700 px-3 py-1 text-xs font-semibold text-white hover:bg-green-800">Approve</button></form>
                                    <form method="POST" action="{{ route('file-management-systems.transfers.decide', [$fileManagementSystem, $transfer]) }}" class="flex gap-2">@csrf @method('PATCH')<input type="hidden" name="decision" value="rejected"><input name="decision_note" required placeholder="Rejection note" class="w-40 rounded-md border-gray-300 text-xs shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><button class="rounded-md bg-red-700 px-3 py-1 text-xs font-semibold text-white hover:bg-red-800">Reject</button></form>
                                </div>
                            @else
                                {{ $transfer->decision_note ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No transfer requests have been recorded.</p>
@endif