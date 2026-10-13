            {{-- Maker-checker workflow --}}
            @php
                $statusTone = ['draft' => 'bg-gray-100 text-gray-800', 'submitted' => 'bg-blue-100 text-blue-800', 'under_review' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                $userName = fn ($u) => $u?->name ?? '—';
            @endphp
            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg" id="workflow">
                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="flex flex-wrap items-center gap-2 text-lg font-bold">
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $statusTone[$accountOpeningRequest->status] ?? 'bg-gray-100 text-gray-800' }}">{{ \Illuminate\Support\Str::headline($accountOpeningRequest->status) }}</span>
                            @if ($accountOpeningRequest->isLocked())
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-800 px-3 py-1 text-sm font-semibold text-white" data-locked>
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                    Locked
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-start gap-2">
                        @if ($accountOpeningRequest->isEditable())
                            @can('submit account openings')
                                <form method="POST" action="{{ route('account-openings.submit', $accountOpeningRequest) }}">
                                    @csrf
                                    <button type="submit" @disabled(! $allComplete)
                                        class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50">
                                        {{ $accountOpeningRequest->status === 'rejected' ? 'Resubmit for Approval' : 'Submit for Approval' }}
                                    </button>
                                </form>
                            @endcan
                        @endif

                        @if ($canDecide)
                            <form method="POST" action="{{ route('account-openings.approve', $accountOpeningRequest) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-green-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-900">
                                    Approve &amp; Activate Account
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Who did what --}}
                <dl class="mb-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-4">
                    <div><dt class="text-gray-500">Maker (started by)</dt><dd class="font-semibold">{{ $userName($accountOpeningRequest->creator) }}</dd><dd class="text-xs text-gray-500">{{ $accountOpeningRequest->created_at?->format('d.m.Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Submitted by</dt><dd class="font-semibold">{{ $userName($accountOpeningRequest->submitter) }}</dd><dd class="text-xs text-gray-500">{{ $accountOpeningRequest->submitted_at?->format('d.m.Y H:i') ?? 'Not submitted yet' }}</dd></div>
                    <div><dt class="text-gray-500">Checker (approved by)</dt><dd class="font-semibold">{{ $userName($accountOpeningRequest->approver) }}</dd><dd class="text-xs text-gray-500">{{ $accountOpeningRequest->approved_at?->format('d.m.Y H:i') ?? 'Not approved' }}</dd></div>
                    @if ($accountOpeningRequest->status === 'rejected')
                        <div><dt class="text-gray-500">Rejected by</dt><dd class="font-semibold">{{ $userName($accountOpeningRequest->rejecter) }}</dd><dd class="text-xs text-gray-500">{{ $accountOpeningRequest->rejected_at?->format('d.m.Y H:i') }}</dd></div>
                    @elseif ($accountOpeningRequest->unlocked_at)
                        <div><dt class="text-gray-500">Last unlocked by</dt><dd class="font-semibold">{{ $userName($accountOpeningRequest->unlocker) }}</dd><dd class="text-xs text-gray-500">{{ $accountOpeningRequest->unlocked_at->format('d.m.Y H:i') }}</dd></div>
                    @endif
                </dl>

                @if ($accountOpeningRequest->status === 'rejected' && $accountOpeningRequest->rejection_reason)
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"><b>Rejected:</b> {{ $accountOpeningRequest->rejection_reason }}. Correct the form and resubmit it.</div>
                @endif

                @if ($accountOpeningRequest->isLocked())
                    <div class="mb-4 rounded-md border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800">This request is approved and locked for every user. Only a super admin can unlock it for changes.</div>
                @elseif (in_array($accountOpeningRequest->status, ['submitted', 'under_review'], true))
                    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Waiting for a checker.
                        @if ($isMaker && ! $workflow->selfApprovalAllowed())
                            You created or submitted this request, so a different user has to approve or reject it.
                        @endif
                    </div>
                @endif

                @if ($canDecide)
                    <form method="POST" action="{{ route('account-openings.reject', $accountOpeningRequest) }}" class="mb-4 rounded-md border border-gray-300 p-3" x-data="{ open: {{ $errors->has('reason') ? 'true' : 'false' }} }">
                        @csrf
                        <button type="button" @click="open = !open" class="text-sm font-semibold text-red-700 underline">Reject this request…</button>
                        <div x-show="open" x-cloak class="mt-2 space-y-2">
                            <label for="reject_reason" class="block text-sm font-semibold">Reason for rejecting (the maker sees this) <span class="text-red-600">*</span></label>
                            <textarea id="reject_reason" name="reason" rows="3" maxlength="2000" class="w-full rounded-md border-gray-400 text-sm" required>{{ old('reason') }}</textarea>
                            <button type="submit" class="rounded-md bg-red-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-800">Reject &amp; return to maker</button>
                        </div>
                    </form>
                @endif

                @if ($canUnlock)
                    <form method="POST" action="{{ route('account-openings.unlock', $accountOpeningRequest) }}" class="mb-4 rounded-md border border-amber-300 bg-amber-50 p-3" x-data="{ open: {{ $errors->has('reason') ? 'true' : 'false' }} }">
                        @csrf
                        <button type="button" @click="open = !open" class="text-sm font-semibold text-amber-800 underline">Unlock this request for changes (super admin)…</button>
                        <div x-show="open" x-cloak class="mt-2 space-y-2">
                            <label for="unlock_reason" class="block text-sm font-semibold">Why does this approved request need to change? <span class="text-red-600">*</span></label>
                            <textarea id="unlock_reason" name="reason" rows="3" maxlength="2000" class="w-full rounded-md border-gray-400 text-sm" required>{{ old('reason') }}</textarea>
                            <p class="text-xs text-gray-700">Only this request is unlocked. It goes back to Draft and must be submitted and approved again, which locks it once more.</p>
                            <button type="submit" class="rounded-md bg-amber-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-amber-800">Unlock request</button>
                        </div>
                    </form>
                @endif

            </div>
