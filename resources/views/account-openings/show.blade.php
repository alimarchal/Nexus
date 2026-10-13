@php
    $customer = $accountOpeningRequest->customer;
    $account = $accountOpeningRequest->account;
    $dueDiligence = $account?->dueDiligences->first();
    $steps = \App\Http\Controllers\AccountOpeningController::STEPS;
    $allComplete = collect($progress)->every(fn (array $row): bool => $row['complete']);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('account-openings.index') }}">Account opening</a><span aria-hidden="true">›</span>
                    <span>{{ $accountOpeningRequest->request_number }}</span>
                </nav>
                <h1 class="ak-title">{{ $customer?->displayName() ?: 'New customer' }} <span class="ak-muted" style="font-size:16px; font-weight:600">{{ $accountOpeningRequest->request_number }}</span></h1>
                <p class="ak-sub">
                    {{ $accountOpeningRequest->aof_version }} &middot; <strong data-form-edition>{{ $accountOpeningRequest->editionLabel() }} form</strong> &middot; {{ $accountOpeningRequest->branch?->code }} {{ $accountOpeningRequest->branch?->name }}
                </p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('account-openings.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back to list</a>
                <a href="{{ route('account-openings.print', $accountOpeningRequest) }}" target="_blank" class="ak-btn ak-btn-outline">Print form</a>
                @can('edit account openings')
                    @if ($accountOpeningRequest->isEditable())
                        <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, $nextStep]) }}" class="ak-btn ak-btn-primary">
                            {{ $allComplete ? 'Review the form' : 'Continue: '.$steps[$nextStep] }}
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')
    <style>
        .aof-grid { display: grid; gap: 20px; grid-template-columns: 1fr; }
        @media (min-width: 1024px) { .aof-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } }
        .aof-side { display: flex; flex-direction: column; gap: 16px; }
        @media (min-width: 1024px) { .aof-side { position: sticky; top: 16px; align-self: start; } }
        .aof-steps { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; }
        .aof-steps li { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .aof-steps li:last-child { border-bottom: 0; }
        .aof-dot { flex: none; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; border: 2px solid var(--ak-border); color: var(--ak-muted); background: #fff; }
        .aof-dot.is-done { background: var(--ak-green); border-color: var(--ak-green); color: #fff; }
        .aof-dot.is-current { border-color: var(--ak-navy); color: var(--ak-navy); box-shadow: 0 0 0 3px var(--ak-navy-tint); }
        .aof-dot.is-locked { background: var(--ak-slate-bg); }
        .aof-steps a, .aof-steps .aof-off { font-weight: 600; color: var(--ak-text); text-decoration: none; }
        .aof-steps a:hover { text-decoration: underline; }
        .aof-steps .aof-off { color: var(--ak-muted); cursor: not-allowed; }
        .aof-steps small { display: block; font-weight: 400; color: var(--ak-muted); font-size: 12px; }
        @media print { .aof-grid { display: block; } .aof-side { position: static; } }
    </style>

    <div class="ak-page">
        <x-status-message />

        @php
            $stepsDone = collect($progress)->filter(fn (array $row): bool => $row['complete'])->count();
            $lockedSteps = \App\Support\AccountOpeningSteps::locked($progress);
            $strictSteps = \App\Support\AccountOpeningSteps::strict();
        @endphp

        {{-- KPI cards --}}
        <section class="ak-kpis" aria-label="Request summary">
            <div class="ak-kpi">
                <span class="ak-kpi-icon {{ $accountOpeningRequest->status === 'approved' ? 'ak-tone-green' : ($accountOpeningRequest->status === 'rejected' ? 'ak-tone-amber' : 'ak-tone-navy') }}" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Status</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ \Illuminate\Support\Str::headline($accountOpeningRequest->status) }}{{ $accountOpeningRequest->isLocked() ? ' · locked' : '' }}</span>
                    <span class="ak-kpi-hint">{{ $accountOpeningRequest->submitted_at ? 'submitted '.$accountOpeningRequest->submitted_at->format('d.m.Y') : 'not submitted yet' }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-navy" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Steps complete</span>
                    <span class="ak-kpi-value">{{ $stepsDone }} / {{ count($progress) }}</span>
                    <span class="ak-kpi-hint">{{ $allComplete ? 'Ready to submit' : 'Next: '.$steps[$nextStep] }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-slate" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.93 17.93 0 0 1 12 21.75c-2.68 0-5.22-.58-7.5-1.63Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Customer</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ $customer?->displayName() ?: 'Not captured yet' }}</span>
                    <span class="ak-kpi-hint">CIF {{ $customer?->cif_number ?? '—' }}</span>
                </span>
            </div>
            <div class="ak-kpi">
                <span class="ak-kpi-icon ak-tone-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg></span>
                <span class="ak-kpi-body">
                    <span class="ak-kpi-label">Account</span>
                    <span class="ak-kpi-value ak-kpi-value-sm">{{ $account?->account_number ?? 'Not created yet' }}</span>
                    <span class="ak-kpi-hint">{{ $account?->product?->name ?? 'Created in step 4' }}</span>
                </span>
            </div>
        </section>

        <div class="aof-grid">
            <div class="space-y-4">
                {{-- Step-wise progress --}}
                <section class="uf-card" aria-label="Steps" id="steps">
                    <header class="uf-card-head">
                        <h2 class="uf-card-title">Step-wise progress</h2>
                        <span class="ak-sub" style="margin:0">{{ $strictSteps ? 'Each step opens after the one before it is complete.' : 'Steps can be filled in any order.' }}</span>
                    </header>
                    <div style="padding: 6px 20px 12px">
                        <ol class="aof-steps">
                            @foreach ($steps as $key => $label)
                                @php
                                    $done = $progress[$key]['complete'];
                                    $isLockedStep = isset($lockedSteps[$key]);
                                    $isNext = $key === $nextStep && ! $allComplete;
                                    $canOpen = $accountOpeningRequest->isEditable() && ! $isLockedStep;
                                @endphp
                                <li data-step="{{ $key }}" data-state="{{ $done ? 'complete' : ($isLockedStep ? 'locked' : 'open') }}">
                                    <span class="aof-dot {{ $done ? 'is-done' : ($isNext ? 'is-current' : ($isLockedStep ? 'is-locked' : '')) }}" aria-hidden="true">{{ $done ? '✓' : ($isLockedStep ? '🔒' : $loop->iteration) }}</span>
                                    <div>
                                        @if ($canOpen)
                                            <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, $key]) }}">{{ $loop->iteration }}. {{ $label }}</a>
                                        @else
                                            <span class="aof-off" @if ($isLockedStep) title="Complete “{{ $steps[$lockedSteps[$key]] }}” first" @endif>{{ $loop->iteration }}. {{ $label }}</span>
                                        @endif
                                        <small>
                                            @if ($done) Complete
                                            @elseif ($isLockedStep) Locked until “{{ $steps[$lockedSteps[$key]] }}” is complete
                                            @elseif ($isNext) Next step to fill in
                                            @else Not started @endif
                                        </small>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

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

            {{-- Customer --}}
            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Customer Information (CIF)</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                    <div><dt class="text-gray-500">CIF Number</dt><dd class="font-semibold">{{ $customer->cif_number }}</dd></div>
                    <div><dt class="text-gray-500">Name</dt><dd class="font-semibold">{{ $customer->displayName() ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Category</dt><dd>{{ $customer->customerCategory?->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Customer Type</dt><dd>{{ \Illuminate\Support\Str::headline($customer->customer_type) }}</dd></div>
                    <div><dt class="text-gray-500">Branch</dt><dd>{{ $accountOpeningRequest->branch?->code }} - {{ $accountOpeningRequest->branch?->name }}</dd></div>
                    <div><dt class="text-gray-500">CIF Date</dt><dd>{{ $customer->cif_date?->format('d-m-Y') }}</dd></div>
                    <div><dt class="text-gray-500">Zakat Exempt</dt><dd>{{ $customer->is_zakat_exempt ? 'Yes - '.($customer->zakatExemptionReason?->name ?? '') : 'No' }}</dd></div>
                    <div><dt class="text-gray-500">PEP</dt><dd>{{ $customer->is_pep ? 'Yes' : 'No' }}</dd></div>
                </dl>

                @if ($customer->individual)
                    <h4 class="mb-2 mt-6 text-sm font-bold uppercase text-gray-600">Individual Details</h4>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                        <div><dt class="text-gray-500">Father / Husband</dt><dd>{{ $customer->individual->parent_or_spouse_name ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Mother's Maiden Name</dt><dd>{{ $customer->individual->mother_maiden_name ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Date of Birth</dt><dd>{{ $customer->individual->date_of_birth?->format('d-m-Y') }}</dd></div>
                        <div><dt class="text-gray-500">Profession</dt><dd>{{ $customer->individual->profession?->name ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Nationalities</dt><dd>{{ $customer->nationalities->map(fn ($n) => $n->country?->name)->filter()->implode(', ') ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Minor</dt><dd>{{ $customer->individual->is_minor ? 'Yes - guardian: '.$customer->individual->guardian_name : 'No' }}</dd></div>
                    </dl>
                @endif

                @if ($customer->organization)
                    <h4 class="mb-2 mt-6 text-sm font-bold uppercase text-gray-600">Business Details</h4>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                        <div><dt class="text-gray-500">Business Name</dt><dd>{{ $customer->organization->business_name }}</dd></div>
                        <div><dt class="text-gray-500">Nature of Business</dt><dd>{{ $customer->organization->businessNature?->name ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Registration No.</dt><dd>{{ $customer->organization->business_registration_number ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">NTN</dt><dd>{{ $customer->organization->ntn ?? '-' }}</dd></div>
                    </dl>
                @endif

                <h4 class="mb-2 mt-6 text-sm font-bold uppercase text-gray-600">Identification &amp; Contact</h4>
                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <ul class="space-y-1">
                        @forelse ($customer->identifications as $identification)
                            <li>
                                {{ $identification->documentType?->name }}: <span class="font-semibold">{{ $identification->document_number }}</span>
                                @if ($identification->expiry_date)
                                    <span class="text-xs text-gray-500">(expires {{ $identification->expiry_date->format('d-m-Y') }})</span>
                                @endif
                            </li>
                        @empty
                            <li class="text-gray-400">No identification captured.</li>
                        @endforelse
                    </ul>
                    <ul class="space-y-1">
                        @forelse ($customer->contacts as $contact)
                            <li>{{ \Illuminate\Support\Str::headline($contact->contact_type) }}: <span class="font-semibold">{{ $contact->country_code }} {{ $contact->value }}</span></li>
                        @empty
                            <li class="text-gray-400">No contact details captured.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Account --}}
            @if ($account)
                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Account</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                        <div><dt class="text-gray-500">Account Number</dt><dd class="font-semibold">{{ $account->account_number }}</dd></div>
                        <div><dt class="text-gray-500">IBAN</dt><dd>{{ $account->iban }}</dd></div>
                        <div><dt class="text-gray-500">Title of Account</dt><dd>{{ $account->title_of_account }}</dd></div>
                        <div><dt class="text-gray-500">Product</dt><dd>{{ $account->product?->name }}</dd></div>
                        <div><dt class="text-gray-500">Currency</dt><dd>{{ $account->currency?->code }}</dd></div>
                        <div><dt class="text-gray-500">Operating Instruction</dt><dd>{{ $account->operatingInstruction?->name }}</dd></div>
                        <div><dt class="text-gray-500">Initial Deposit</dt><dd>{{ $account->initial_deposit !== null ? number_format((float) $account->initial_deposit, 2) : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd>{{ \Illuminate\Support\Str::headline($account->status) }}</dd></div>
                    </dl>

                    <h4 class="mb-2 mt-6 text-sm font-bold uppercase text-gray-600">Applicants</h4>
                    <table class="w-full text-sm">
                        <thead class="bg-green-800 text-xs uppercase text-white">
                            <tr>
                                <th class="px-2 py-1 text-left">#</th>
                                <th class="px-2 py-1 text-left">Name</th>
                                <th class="px-2 py-1 text-left">Role</th>
                                <th class="px-2 py-1 text-center">Signatory</th>
                                <th class="px-2 py-1 text-center">Specimen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($account->holders as $holder)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="px-2 py-1">{{ $holder->applicant_number }}</td>
                                    <td class="px-2 py-1">{{ $holder->customer?->displayName() ?? $holder->customer?->cif_number }}</td>
                                    <td class="px-2 py-1">{{ \Illuminate\Support\Str::headline($holder->holder_role) }}</td>
                                    <td class="px-2 py-1 text-center">{{ $holder->is_signatory ? 'Yes' : 'No' }}</td>
                                    <td class="px-2 py-1 text-center">{{ $holder->specimenSignatures->where('is_active', true)->count() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-2 py-3 text-center text-gray-400">No applicants recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- CDD --}}
            @if ($dueDiligence)
                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Customer Due Diligence</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                        <div><dt class="text-gray-500">CDD Type</dt><dd>{{ \Illuminate\Support\Str::headline($dueDiligence->cdd_type) }}</dd></div>
                        <div><dt class="text-gray-500">Customer Source</dt><dd>{{ $dueDiligence->customer_source ? \Illuminate\Support\Str::headline($dueDiligence->customer_source) : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Physical Verification</dt><dd>{{ $dueDiligence->physical_verification_conducted ? 'Conducted' : 'Not conducted' }}</dd></div>
                        <div><dt class="text-gray-500">Proscribed List</dt><dd>{{ $dueDiligence->proscribed_list_cleared ? 'Cleared' : 'Not cleared' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Source of Income</dt><dd>{{ $dueDiligence->incomeSources->pluck('name')->implode(', ') ?: '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Purpose of Account</dt><dd>{{ $dueDiligence->accountPurposes->pluck('name')->implode(', ') ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Expected Credit / month</dt><dd>{{ $dueDiligence->expected_monthly_credit_amount !== null ? number_format((float) $dueDiligence->expected_monthly_credit_amount, 2) : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Expected Debit / month</dt><dd>{{ $dueDiligence->expected_monthly_debit_amount !== null ? number_format((float) $dueDiligence->expected_monthly_debit_amount, 2) : '-' }}</dd></div>
                        <div><dt class="text-gray-500">Monthly Income</dt><dd>{{ $dueDiligence->monthly_income !== null ? number_format((float) $dueDiligence->monthly_income, 2) : '-' }}</dd></div>
                        <div><dt class="text-gray-500">UBOs recorded</dt><dd>{{ $account->ultimateBeneficialOwners->count() }}</dd></div>
                    </dl>
                </div>
            @endif

            {{-- Documents --}}
            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Documentation</h3>
                <table class="w-full text-sm">
                    <thead class="bg-green-800 text-xs uppercase text-white">
                        <tr>
                            <th class="px-2 py-1 text-left">Document</th>
                            <th class="px-2 py-1 text-center">Status</th>
                            <th class="px-2 py-1 text-center">Attested</th>
                            <th class="px-2 py-1 text-center">File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accountOpeningRequest->documents as $document)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-2 py-1">{{ $document->documentType?->name }}</td>
                                <td class="px-2 py-1 text-center">{{ ucfirst($document->status) }}</td>
                                <td class="px-2 py-1 text-center">{{ $document->is_attested ? 'Yes' : 'No' }}</td>
                                <td class="px-2 py-1 text-center">
                                    @if ($document->file_path)
                                        <a class="text-blue-600 underline"
                                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document->file_path) }}" target="_blank">
                                            View
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-3 text-center text-gray-400">No documents recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            </div>

            <aside class="aof-side" aria-label="History">
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
            </aside>
        </div>
    </div>
</x-app-layout>
