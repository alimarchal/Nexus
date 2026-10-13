            {{-- History: what changed, who, when --}}
            @php
                $eventLabels = ['created' => 'Started', 'step_saved' => 'Saved', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'unlocked' => 'Unlocked'];
                $eventTone = ['created' => 'bg-gray-100 text-gray-800', 'step_saved' => 'bg-indigo-100 text-indigo-800', 'submitted' => 'bg-blue-100 text-blue-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800', 'unlocked' => 'bg-amber-100 text-amber-900'];
            @endphp
            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg" id="history">
                <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">History &mdash; every change, who made it and when</h3>
                <ol class="space-y-4">
                    @forelse ($accountOpeningRequest->histories->reverse() as $entry)
                        <li class="rounded-lg border border-gray-200 p-3" data-history="{{ $entry->event }}">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $eventTone[$entry->event] ?? 'bg-gray-100 text-gray-800' }}">{{ $eventLabels[$entry->event] ?? \Illuminate\Support\Str::headline($entry->event) }}@if ($entry->step) &middot; {{ $steps[$entry->step] ?? $entry->step }}@endif</span>
                                <span class="font-semibold">{{ $entry->user?->name ?? 'System' }}</span>
                                <span class="text-xs text-gray-500">{{ $entry->created_at?->format('d.m.Y H:i') }}</span>
                            </div>
                            @if ($entry->note)
                                <p class="mt-1 text-sm text-gray-700">{{ $entry->note }}</p>
                            @endif
                            @if ($entry->changes)
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs font-semibold text-blue-800">{{ count($entry->changes) }} {{ \Illuminate\Support\Str::plural('change', count($entry->changes)) }}</summary>
                                    <table class="mt-2 w-full text-xs">
                                        <thead><tr class="bg-gray-100 text-left"><th class="px-2 py-1">Field</th><th class="px-2 py-1">Before</th><th class="px-2 py-1">After</th></tr></thead>
                                        <tbody>
                                            @foreach ($entry->changes as $field => $change)
                                                <tr class="border-b border-gray-100">
                                                    <td class="px-2 py-1 font-semibold">{{ $field }}</td>
                                                    <td class="px-2 py-1 text-red-800">{{ $change['old'] ?? '—' }}</td>
                                                    <td class="px-2 py-1 text-green-800">{{ $change['new'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </details>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No history yet.</li>
                    @endforelse
                </ol>
            </div>
