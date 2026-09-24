@php
    /**
     * File management -- record view, laid out like the AKSIC case page:
     * header with one primary action, a status snapshot, detail cards, the
     * scanned pages, transfers (with approve / reject) and the history.
     */
    $file = $fileManagementSystem;
    $user = auth()->user();
    $dash = '—';
    $val = fn ($value) => filled($value) ? $value : $dash;
    $pendingTransfer = $file->transfers->firstWhere('status', 'pending');
    $canArchive = $user->can('archive file management systems') && ! $file->is_archived && $file->isHeldBy($user);
    $office = $file->fileable_type === 'division'
        ? ($file->fileable?->short_name ?: $file->fileable_name)
        : trim((($file->fileable?->code ?? null) ? $file->fileable->code.' - ' : '').($file->fileable_name ?? ''));
    $unitName = function (?string $type, $id) {
        $model = $type ? \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($type) : null;
        $unit = $model ? $model::find($id) : null;

        return \Illuminate\Support\Str::headline((string) $type).': '.($unit ? trim(($unit->code ?? '').' '.($unit->name ?? '')) : '#'.$id);
    };
    $size = function ($bytes): string {
        $bytes = (int) $bytes;

        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format(max(1, $bytes / 1024)).' KB';
    };
    $eventStyles = [
        'created' => 'ak-status-blue', 'updated' => 'ak-status-slate', 'transferred' => 'ak-status-amber',
        'archived' => 'ak-status-slate', 'page_uploaded' => 'ak-status-green', 'page_removed' => 'ak-status-red',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('file-management-systems.index') }}">File management</a><span aria-hidden="true">›</span>
                    <span>{{ $file->digital_id }}</span>
                </nav>
                <h1 class="ak-title">{{ $file->title ?: ($file->file_no ?: $file->digital_id) }}</h1>
                <p class="ak-sub">
                    {{ $file->digital_id }}
                    @if ($file->file_no) &middot; File no {{ $file->file_no }} @endif
                    &middot; {{ $file->fileable_label }}: {{ $val($office) }}
                </p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ $listUrl }}" class="ak-btn ak-btn-outline" title="Back to the file list"><span aria-hidden="true">←</span> Back</a>
                @can('delete file management systems')
                    <button type="button" x-data class="ak-btn ak-btn-danger-outline"
                        x-on:click="$dispatch('open-delete-fms-modal', { url: '{{ route('file-management-systems.destroy', $file) }}' })">Delete</button>
                @endcan
                @can('edit file management systems')
                    <a href="{{ route('file-management-systems.edit', $file) }}" class="ak-btn ak-btn-outline">Edit</a>
                @endcan
                @can('transfer file management systems')
                    @unless ($file->is_archived)
                        <a href="{{ route('file-management-systems.transfer', $file) }}" class="ak-btn {{ $canArchive ? 'ak-btn-outline' : 'ak-btn-primary' }}">Transfer</a>
                    @endunless
                @endcan
                @if ($canArchive)
                    <a href="{{ route('file-management-systems.archive-form', $file) }}" class="ak-btn ak-btn-primary">Archive to box</a>
                @endif
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')
    <style>
        .fm-card { background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: var(--ak-shadow); }
        .fm-card-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding: 14px 20px; border-bottom: 1px solid var(--ak-line); }
        .fm-card-head h2 { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #166534; }
        .fm-card-head p { font-size: 12px; color: var(--ak-muted); }
        .fm-card-body { padding: 18px 20px; }
        .fm-dl { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 14px 20px; font-size: 14px; }
        @media (min-width: 640px) { .fm-dl { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .fm-dl dt { font-size: 12px; color: var(--ak-muted); }
        .fm-dl dd { margin-top: 2px; color: var(--ak-text); font-weight: 500; overflow-wrap: anywhere; }
        .fm-dl .wide { grid-column: 1 / -1; }
        .fm-snap { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 768px) { .fm-snap { grid-template-columns: repeat(5, minmax(0, 1fr)); } .fm-snap > div + div { border-left: 1px solid var(--ak-line); } }
        .fm-snap > div { padding: 14px 20px; }
        .fm-snap dt { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--ak-muted); }
        .fm-snap dd { margin-top: 4px; font-size: 16px; font-weight: 700; color: var(--ak-text); overflow-wrap: anywhere; }
        .fm-snap dd small { display: block; font-size: 12px; font-weight: 400; color: var(--ak-muted); }
        .fm-grid-2 { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1024px) { .fm-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .fm-pages { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 640px) { .fm-pages { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .fm-pages { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        .fm-page { display: flex; flex-direction: column; border: 1px solid var(--ak-border); border-radius: 10px; overflow: hidden; text-decoration: none; color: var(--ak-text); background: #fff; transition: box-shadow .15s, border-color .15s; }
        .fm-page:hover { border-color: var(--ak-navy); box-shadow: var(--ak-shadow-hover); }
        .fm-thumb { position: relative; height: 140px; display: flex; align-items: center; justify-content: center; background: var(--ak-soft); border-bottom: 1px solid var(--ak-line); }
        .fm-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fm-thumb svg { width: 44px; height: 44px; color: #94a3b8; }
        .fm-thumb .fm-no { position: absolute; top: 6px; left: 6px; padding: 1px 7px; border-radius: 999px; background: rgba(15,23,42,.75); color: #fff; font-size: 11px; font-weight: 700; }
        .fm-thumb .fm-type { position: absolute; top: 6px; right: 6px; padding: 1px 6px; border-radius: 4px; background: #fff; border: 1px solid var(--ak-border); font-size: 10px; font-weight: 800; color: #334155; }
        .fm-page-meta { padding: 8px 10px; font-size: 12px; }
        .fm-page-meta b { display: block; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .fm-page-meta span { color: var(--ak-muted); }
        .fm-timeline { list-style: none; margin: 0; padding: 0; }
        .fm-timeline li { position: relative; padding: 0 0 16px 24px; }
        .fm-timeline li::before { content: ''; position: absolute; left: 6px; top: 6px; bottom: -6px; width: 2px; background: var(--ak-line); }
        .fm-timeline li:last-child::before { display: none; }
        .fm-timeline li::after { content: ''; position: absolute; left: 1px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 2px solid var(--ak-navy); }
        .fm-timeline .fm-when { font-size: 12px; color: var(--ak-muted); }
        .fm-timeline .fm-what { font-size: 14px; color: var(--ak-text); margin-top: 2px; }
        .ak-status-slate { background: #e2e8f0; color: #334155; }
        .ak-status-blue { background: #dbeafe; color: #1e3a8a; }
        .ak-status-red { background: #fee2e2; color: #991b1b; }
        .fm-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .fm-table th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--ak-muted); padding: 8px 10px; border-bottom: 1px solid var(--ak-border); background: var(--ak-soft); }
        .fm-table td { padding: 10px; border-bottom: 1px solid var(--ak-line); vertical-align: top; color: var(--ak-text); }
        .fm-empty { font-size: 13px; color: var(--ak-muted); }
    </style>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <x-status-message />
            @if ($errors->any())
                <div class="ak-alert ak-alert-error" role="alert">{{ $errors->first() }}</div>
            @endif

            @if ($pendingTransfer)
                <div class="ak-alert ak-alert-warn" role="status">
                    <b>Transfer pending:</b> {{ $unitName($pendingTransfer->source_fileable_type, $pendingTransfer->source_fileable_id) }}
                    &rarr; {{ $unitName($pendingTransfer->destination_fileable_type, $pendingTransfer->destination_fileable_id) }}
                    @if (in_array($pendingTransfer->id, $approvableTransferIds, true))
                        &middot; <a href="#fm-transfers" class="font-semibold underline">Awaiting your decision →</a>
                    @else
                        &middot; awaiting a decision by the receiving office.
                    @endif
                </div>
            @endif

            {{-- Snapshot --}}
            <div class="fm-card">
                <div class="fm-card-head">
                    <div class="flex items-center gap-2">
                        @if ($pendingTransfer)
                            <span class="ak-status ak-status-amber"><i aria-hidden="true"></i>Transfer pending</span>
                        @elseif ($file->is_archived)
                            <span class="ak-status ak-status-slate"><i aria-hidden="true"></i>Archived</span>
                        @else
                            <span class="ak-status ak-status-blue"><i aria-hidden="true"></i>In circulation</span>
                        @endif
                    </div>
                    <p>Added {{ $file->created_at?->format('d.m.Y H:i') }} @if ($file->creator) by {{ $file->creator->name }} @endif</p>
                </div>
                <dl class="fm-snap">
                    <div><dt>Document date</dt><dd>{{ $file->document_date?->format('d.m.Y') ?? $dash }}</dd></div>
                    <div><dt>Category</dt><dd>{{ $val($file->fileCategory?->category_name) }}</dd></div>
                    <div><dt>Scanned pages</dt><dd>{{ $file->media->count() }}<small>{{ $file->media->count() ? $size($file->media->sum('size')).' in total' : 'none uploaded' }}</small></dd></div>
                    <div><dt>Custodian</dt><dd>{{ $val($file->currentCustodian?->name) }}</dd></div>
                    <div><dt>Location</dt>
                        <dd>
                            @if ($file->is_archived && $file->box)
                                {{ $file->box->box_number }}<small>{{ $val($file->box->location) }} · position {{ $file->position_in_box }}</small>
                            @else
                                {{ $val($office) }}<small>{{ $file->fileable_label }} office</small>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Details --}}
            <div class="fm-grid-2">
                <section class="fm-card" aria-labelledby="fm-details">
                    <div class="fm-card-head"><h2 id="fm-details">Record</h2></div>
                    <dl class="fm-dl fm-card-body">
                        <div><dt>Digital ID</dt><dd class="ak-mono">{{ $file->digital_id }}</dd></div>
                        <div><dt>File no (own reference)</dt><dd>{{ $val($file->file_no) }}</dd></div>
                        <div class="wide"><dt>Title</dt><dd>{{ $val($file->title) }}</dd></div>
                        <div><dt>Category</dt><dd>{{ $val($file->fileCategory?->category_name) }}</dd></div>
                        <div><dt>Document date</dt><dd>{{ $file->document_date?->format('d.m.Y') ?? $dash }}</dd></div>
                        <div><dt>Created by</dt><dd>{{ $file->creator?->name ?? 'System' }}</dd></div>
                        <div><dt>Last updated</dt><dd>{{ $file->updated_at?->format('d.m.Y H:i') }} @if ($file->updater) by {{ $file->updater->name }} @endif</dd></div>
                    </dl>
                </section>

                <section class="fm-card" aria-labelledby="fm-custody">
                    <div class="fm-card-head"><h2 id="fm-custody">Custody &amp; archive</h2></div>
                    <dl class="fm-dl fm-card-body">
                        <div><dt>Record owner</dt><dd>{{ $file->fileable_label }}: {{ $val($office) }}</dd></div>
                        <div><dt>Current custodian</dt><dd>{{ $file->currentCustodian?->name ?? 'Unassigned' }}</dd></div>
                        @if ($file->is_archived)
                            <div><dt>Box</dt><dd>{{ $file->box?->box_number ?? 'Unknown' }}</dd></div>
                            <div><dt>Position in box</dt><dd>{{ $val($file->position_in_box) }}</dd></div>
                            <div><dt>Shelf / location</dt><dd>{{ $val($file->box?->location) }}</dd></div>
                            <div><dt>Archived at</dt><dd>{{ $file->archived_at?->format('d.m.Y H:i') ?? $dash }}</dd></div>
                        @else
                            <div class="wide"><dt>Archive</dt>
                                <dd>
                                    Not archived yet.
                                    @if ($canArchive)
                                        <a href="{{ route('file-management-systems.archive-form', $file) }}" class="font-semibold text-blue-800 underline">Archive to a box →</a>
                                    @else
                                        <span class="ak-muted">Only the owning office can archive it.</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div><dt>Transfers</dt><dd>{{ $file->transfers->count() }} recorded{{ $pendingTransfer ? ', 1 pending' : '' }}</dd></div>
                    </dl>
                </section>
            </div>

            {{-- Scanned pages --}}
            <section class="fm-card" aria-labelledby="fm-pages">
                <div class="fm-card-head">
                    <h2 id="fm-pages">Scanned pages ({{ $file->media->count() }})</h2>
                    @can('edit file management systems')
                        <a href="{{ route('file-management-systems.edit', $file) }}" class="ak-btn ak-btn-outline">Add or remove pages</a>
                    @endcan
                </div>
                <div class="fm-card-body">
                    @if ($file->media->isNotEmpty())
                        <div class="fm-pages">
                            @foreach ($file->media as $page)
                                @php
                                    $name = $page->getCustomProperty('original_filename', $page->file_name);
                                    $isImage = str_starts_with((string) $page->mime_type, 'image/');
                                    $ext = strtoupper(pathinfo($name, PATHINFO_EXTENSION) ?: 'FILE');
                                @endphp
                                <a href="{{ $page->getUrl() }}" target="_blank" rel="noopener" class="fm-page" title="Open {{ $name }}">
                                    <span class="fm-thumb">
                                        <span class="fm-no">Page {{ $loop->iteration }}</span>
                                        <span class="fm-type">{{ $ext }}</span>
                                        @if ($isImage)
                                            <img src="{{ $page->getUrl() }}" alt="Page {{ $loop->iteration }}: {{ $name }}" loading="lazy">
                                        @else
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        @endif
                                    </span>
                                    <span class="fm-page-meta"><b>{{ $name }}</b><span>{{ $size($page->size) }} · {{ $page->created_at?->format('d.m.Y') }}</span></span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="fm-empty">No pages uploaded yet.</p>
                    @endif
                </div>
            </section>

            {{-- Transfers --}}
            <section class="fm-card" id="fm-transfers" aria-labelledby="fm-transfers-title">
                <div class="fm-card-head">
                    <h2 id="fm-transfers-title">Transfers</h2>
                    @can('transfer file management systems')
                        @unless ($file->is_archived || $pendingTransfer)
                            <a href="{{ route('file-management-systems.transfer', $file) }}" class="ak-btn ak-btn-outline">New transfer</a>
                        @endunless
                    @endcan
                </div>
                <div class="fm-card-body">
                    @if ($file->transfers->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="fm-table">
                                <thead><tr><th>Status</th><th>From → To</th><th>Reason</th><th>People</th><th>Decision</th></tr></thead>
                                <tbody>
                                    @foreach ($file->transfers as $transfer)
                                        @php
                                            $statusClass = ['pending' => 'ak-status-amber', 'approved' => 'ak-status-green', 'rejected' => 'ak-status-red'][$transfer->status] ?? 'ak-status-slate';
                                        @endphp
                                        <tr>
                                            <td><span class="ak-status {{ $statusClass }}"><i aria-hidden="true"></i>{{ \Illuminate\Support\Str::headline($transfer->status) }}</span>
                                                <div class="ak-muted">{{ $transfer->created_at?->format('d.m.Y H:i') }}</div></td>
                                            <td>{{ $unitName($transfer->source_fileable_type, $transfer->source_fileable_id) }}<br>&rarr; {{ $unitName($transfer->destination_fileable_type, $transfer->destination_fileable_id) }}</td>
                                            <td>{{ $val($transfer->reason) }}</td>
                                            <td class="ak-muted">Requested: {{ $transfer->requester?->name ?? 'System' }}<br>Custodian: {{ $transfer->recipient?->name ?? 'Unassigned' }}<br>Decided: {{ $transfer->decider?->name ?? $dash }}</td>
                                            <td>
                                                @if ($transfer->status === 'pending' && in_array($transfer->id, $approvableTransferIds, true))
                                                    <div x-data="{ reject: false }" class="flex flex-wrap items-start gap-2">
                                                        <form method="POST" action="{{ route('file-management-systems.transfers.decide', [$file, $transfer]) }}" x-show="! reject">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="decision" value="approved">
                                                            <button class="ak-btn ak-btn-success">Approve</button>
                                                        </form>
                                                        <button type="button" class="ak-btn ak-btn-danger-outline" x-show="! reject" @click="reject = true">Reject</button>
                                                        <form method="POST" action="{{ route('file-management-systems.transfers.decide', [$file, $transfer]) }}" x-show="reject" x-cloak class="flex flex-wrap gap-2">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="decision" value="rejected">
                                                            <input name="decision_note" required placeholder="Reason for rejection" aria-label="Reason for rejection"
                                                                class="w-56 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            <button class="ak-btn ak-btn-danger-outline">Confirm reject</button>
                                                            <button type="button" class="ak-btn ak-btn-ghost" @click="reject = false">Cancel</button>
                                                        </form>
                                                    </div>
                                                @else
                                                    {{ $val($transfer->decision_note) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="fm-empty">No transfer requests have been recorded.</p>
                    @endif
                </div>
            </section>

            {{-- History --}}
            <section class="fm-card" aria-labelledby="fm-history">
                <div class="fm-card-head"><h2 id="fm-history">History</h2><p>{{ $activityHistory->count() }} {{ \Illuminate\Support\Str::plural('event', $activityHistory->count()) }}, newest first</p></div>
                <div class="fm-card-body">
                    @if ($activityHistory->isNotEmpty())
                        <ol class="fm-timeline">
                            @foreach ($activityHistory as $activity)
                                <li>
                                    <div class="fm-when">
                                        {{ $activity->created_at?->format('d.m.Y h:i A') }} &middot; {{ $activity->causer?->name ?? 'System' }}
                                        <span class="ak-status {{ $eventStyles[$activity->event] ?? 'ak-status-slate' }}" style="margin-left:6px">{{ \Illuminate\Support\Str::headline($activity->event ?? 'updated') }}</span>
                                    </div>
                                    <div class="fm-what">
                                        @if ($activity->event === 'transferred')
                                            {{ $unitName(data_get($activity->properties, 'from.type'), data_get($activity->properties, 'from.id')) }}
                                            &rarr; {{ $unitName(data_get($activity->properties, 'to.type'), data_get($activity->properties, 'to.id')) }}
                                        @elseif (in_array($activity->event, ['page_uploaded', 'page_removed'], true))
                                            {{ data_get($activity->properties, 'original_filename', $activity->description) }}
                                        @elseif ($activity->event === 'archived')
                                            Archived to {{ data_get($activity->properties, 'box_number') }}, position {{ data_get($activity->properties, 'position_in_box') }}
                                        @else
                                            {{ $activity->description }}
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="fm-empty">No history is available for this record.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>

    @can('delete file management systems')
        <x-alpine-confirmation-modal eventName="open-delete-fms-modal" title="Delete file {{ $file->digital_id }}"
            confirmButtonText="Delete" confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
            <p class="text-sm text-gray-600">
                The record and all {{ $file->media->count() }} uploaded {{ \Illuminate\Support\Str::plural('page', $file->media->count()) }} will be removed. This cannot be undone.
            </p>
        </x-alpine-confirmation-modal>
    @endcan
</x-app-layout>
