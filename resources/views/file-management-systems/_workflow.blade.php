{{-- Maker-checker card of a file record. Needs $file, $workflow. --}}
@php
    $user = auth()->user();
    $tone = ['draft' => 'ak-status-slate', 'submitted' => 'ak-status-blue', 'approved' => 'ak-status-green', 'rejected' => 'ak-status-red'];
    $who = fn ($u) => $u?->name ?? '—';
    $canSubmit = $workflow->canSubmit($file, $user);
    $canDecide = $workflow->canDecide($file, $user);
    $canUnlock = $workflow->canUnlock($file, $user);
    $isMaker = $workflow->isMaker($file, $user);
@endphp

@if ($workflow->makerCheckerEnabled())
    <section class="fm-card" id="fm-workflow" aria-labelledby="fm-workflow-title">
        <div class="fm-card-head">
            <h2 id="fm-workflow-title">Approval (maker-checker)</h2>
            <div class="flex flex-wrap items-center gap-2">
                <span class="ak-status {{ $tone[$file->status] ?? 'ak-status-slate' }}" data-approval-status><i aria-hidden="true"></i>{{ \Illuminate\Support\Str::headline($file->status) }}</span>
                @if ($file->isLocked())
                    <span class="ak-status ak-status-slate" data-locked><i aria-hidden="true"></i>Locked</span>
                @endif
            </div>
        </div>
        <div class="fm-card-body space-y-3">
            <dl class="fm-snap">
                <div><dt>Maker (created by)</dt><dd>{{ $who($file->creator) }}<small>{{ $file->created_at?->format('d.m.Y H:i') }}</small></dd></div>
                <div><dt>Submitted by</dt><dd>{{ $who($file->submitter) }}<small>{{ $file->submitted_at?->format('d.m.Y H:i') ?? 'Not submitted yet' }}</small></dd></div>
                <div><dt>Checker (approved by)</dt><dd>{{ $who($file->approver) }}<small>{{ $file->approved_at?->format('d.m.Y H:i') ?? 'Not approved' }}</small></dd></div>
                @if ($file->status === 'rejected')
                    <div><dt>Rejected by</dt><dd>{{ $who($file->rejecter) }}<small>{{ $file->rejected_at?->format('d.m.Y H:i') }}</small></dd></div>
                @elseif ($file->unlocked_at)
                    <div><dt>Last unlocked by</dt><dd>{{ $who($file->unlocker) }}<small>{{ $file->unlocked_at->format('d.m.Y H:i') }}</small></dd></div>
                @endif
            </dl>

            @if ($file->status === 'rejected' && $file->rejection_reason)
                <div class="ak-alert ak-alert-error" role="status"><b>Rejected:</b> {{ $file->rejection_reason }}. Correct the record and resubmit it.</div>
            @endif

            @if ($file->isLocked())
                <div class="ak-alert ak-alert-warn" role="status">This record is approved and locked for every user. Only a super admin can unlock it for changes.</div>
            @elseif ($file->status === 'submitted')
                <div class="ak-alert ak-alert-warn" role="status">
                    Waiting for a checker.
                    @if ($isMaker && ! $workflow->selfApprovalAllowed())
                        You created or submitted this record, so a different user has to approve or reject it.
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap items-start gap-2">
                @if ($canSubmit)
                    <form method="POST" action="{{ route('file-management-systems.submit', $file) }}">
                        @csrf
                        <button type="submit" class="ak-btn ak-btn-primary">{{ $file->status === 'rejected' ? 'Resubmit for approval' : 'Submit for approval' }}</button>
                    </form>
                @endif
                @if ($canDecide)
                    <form method="POST" action="{{ route('file-management-systems.approve', $file) }}">
                        @csrf
                        <button type="submit" class="ak-btn ak-btn-primary">Approve</button>
                    </form>
                @endif
            </div>

            @if ($canDecide)
                <form method="POST" action="{{ route('file-management-systems.reject', $file) }}" class="rounded-md border border-gray-300 p-3" x-data="{ open: {{ $errors->has('reason') ? 'true' : 'false' }} }">
                    @csrf
                    <button type="button" @click="open = !open" class="text-sm font-semibold text-red-700 underline">Reject this record…</button>
                    <div x-show="open" x-cloak class="mt-2 space-y-2">
                        <label for="reject_reason" class="block text-sm font-semibold">Reason for rejecting (the maker sees this) <span class="text-red-600">*</span></label>
                        <textarea id="reject_reason" name="reason" rows="3" maxlength="2000" class="w-full rounded-md border-gray-400 text-sm" required>{{ old('reason') }}</textarea>
                        <button type="submit" class="ak-btn ak-btn-danger-outline">Reject &amp; return to maker</button>
                    </div>
                </form>
            @endif

            @if ($canUnlock)
                <form method="POST" action="{{ route('file-management-systems.unlock', $file) }}" class="rounded-md border border-amber-300 bg-amber-50 p-3" x-data="{ open: {{ $errors->has('reason') ? 'true' : 'false' }} }">
                    @csrf
                    <button type="button" @click="open = !open" class="text-sm font-semibold text-amber-800 underline">Unlock this record for changes (super admin)…</button>
                    <div x-show="open" x-cloak class="mt-2 space-y-2">
                        <label for="unlock_reason" class="block text-sm font-semibold">Why does this approved record need to change? <span class="text-red-600">*</span></label>
                        <textarea id="unlock_reason" name="reason" rows="3" maxlength="2000" class="w-full rounded-md border-gray-400 text-sm" required>{{ old('reason') }}</textarea>
                        <p class="text-xs text-gray-700">Only this record is unlocked. It goes back to Draft and must be submitted and approved again, which locks it once more. The reason and who unlocked it are kept in the history.</p>
                        <button type="submit" class="ak-btn ak-btn-outline">Unlock record</button>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endif
