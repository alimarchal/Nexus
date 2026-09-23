@php
    /**
     * AKSIC -- record view.
     *
     * Read-only presentation of one AKSIC case. Display only: no field on this
     * page is captured or derived beyond what the aksics / aksic_amortizations
     * tables already hold. Printing is handled by the dedicated print sheet
     * (aksics.print) so this page can stay screen-optimised.
     */
    $canModifyAksic = $aksic->amortizations->isEmpty() || auth()->user()?->hasRole('super-admin');

    $schedule = $aksic->amortizations;
    $rule = $aksic->aksicRule;

    $dash = '—';
    $val = fn ($value) => filled($value) ? $value : $dash;
    $money = fn ($value) => $value === null ? $dash : number_format((float) $value, 2);
    $pct = fn ($value) => $value === null ? $dash : number_format((float) $value, 2).'%';
    $date = fn ($value) => \App\Support\AksicDate::display($value, $dash);

    $totalInterest = (float) $schedule->sum('total_interest');
    $totalPayable = (float) $schedule->sum('total_installment');
    $totalPrincipal = (float) $schedule->sum('installment_per_month');

    // #3 / #10: instalment 1 is the markup-only broken period; the rest are the
    // regular instalments whose markup is checked against the scheme guideline.
    $brokenPeriodMarkup = (float) ($schedule->first()?->total_interest ?? 0);
    $regularMarkup = $totalInterest - $brokenPeriodMarkup;
    $guidelineMarkup = ($aksic->principal_amount !== null && $aksic->tenure && $aksic->total_rate !== null)
        ? (float) app(\App\Services\AksicAmortizationScheduleGenerator::class)
            ->guidelineMarkup((string) $aksic->principal_amount, (int) $aksic->tenure, (string) $aksic->total_rate)
        : null;
    $isNewSchedule = $schedule->isNotEmpty() && (float) $schedule->first()->installment_per_month === 0.0;

    $canApprove = $aksic->status !== 'Reject' && $canModifyAksic;
    $navQuery = $pendingOnly ? ['nav' => 'pending'] : [];

    $statusStyles = [
        'Approved' => 'bg-green-100 text-green-800 ring-green-600/20 dark:bg-green-900/40 dark:text-green-200',
        'Pending' => 'bg-amber-100 text-amber-800 ring-amber-600/20 dark:bg-amber-900/40 dark:text-amber-200',
        'Reject' => 'bg-red-100 text-red-800 ring-red-600/20 dark:bg-red-900/40 dark:text-red-200',
        'Rejected' => 'bg-red-100 text-red-800 ring-red-600/20 dark:bg-red-900/40 dark:text-red-200',
    ];
    $statusClass = $statusStyles[$aksic->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-gray-700 dark:text-gray-200';

    $btn = 'inline-flex items-center gap-1.5 rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    AKSIC Case {{ $aksic->application_no }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $val($aksic->name) }}
                    @if ($aksic->cnic) &middot; CNIC {{ $aksic->cnic }} @endif
                    @if ($aksic->branch) &middot; {{ $aksic->branch->code }} - {{ $aksic->branch->name }} @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('approve aksics')
                    @if ($canApprove)
                        <button type="button" x-data
                            x-on:click="$dispatch('open-approve-aksic-modal', {{ Illuminate\Support\Js::from([
                                'url' => route('aksic.approve', $aksic),
                                'categoryId' => (string) $aksic->business_category_id,
                                'subCategoryId' => (string) $aksic->business_sub_category_id,
                                'category' => $aksic->businessCategory?->name ?? '-',
                                'principal' => $aksic->principal_amount === null ? '-' : number_format((float) $aksic->principal_amount, 2),
                                'kiborRate' => $aksic->kibor_rate === null ? '-' : number_format((float) $aksic->kibor_rate, 2).'%',
                                'spreadRate' => $aksic->spread_rate === null ? '-' : number_format((float) $aksic->spread_rate, 2).'%',
                                'totalRate' => $aksic->total_rate === null ? '-' : number_format((float) $aksic->total_rate, 2).'%',
                                'returnTo' => 'show',
                                'nav' => $pendingOnly ? 'pending' : '',
                            ]) }})"
                            class="{{ $btn }} bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                            </svg>
                            {{ $schedule->isEmpty() ? 'Approve' : 'Regenerate Schedule' }}
                        </button>
                    @endif
                @endcan

                <a href="{{ route('aksic.print', $aksic) }}" target="_blank"
                    class="{{ $btn }} bg-green-800 hover:bg-green-900 focus:ring-green-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 9V4h12v5M6 18h12v4H6v-4Zm-2 0h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2Z" />
                    </svg>
                    Print Sheet
                </a>

                @can('edit aksics')
                    @if ($canModifyAksic)
                        <a href="{{ route('aksic.edit', $aksic) }}"
                            class="{{ $btn }} bg-blue-950 hover:bg-green-800 focus:ring-indigo-500">
                            Edit
                        </a>
                    @endif
                @endcan

                @can('delete aksics')
                    @if ($canModifyAksic)
                        <form method="POST" action="{{ route('aksic.destroy', $aksic) }}" class="inline-block"
                            onsubmit="return confirm('Delete this AKSIC record?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="{{ $btn }} bg-red-700 hover:bg-red-800 focus:ring-red-500">
                                Delete
                            </button>
                        </form>
                    @endif
                @endcan

                <a href="{{ route('aksic.index') }}"
                    class="{{ $btn }} bg-blue-950 hover:bg-green-800 focus:ring-indigo-500">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>
    </x-slot>

    @include('aksics._grid-style')

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <x-status-message />
            <x-validation-errors />

            {{-- Previous / Next through the case list (#12) -------------------- --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white px-4 py-3 shadow dark:bg-gray-800">
                <div class="flex items-center gap-2">
                    @if ($navigation['previous'])
                        <a href="{{ route('aksic.show', ['aksic' => $navigation['previous']] + $navQuery) }}"
                            class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                            title="Case {{ $navigation['previous']->application_no }}">
                            &larr; Previous
                        </a>
                    @else
                        <span class="inline-flex cursor-not-allowed items-center gap-1 rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-300 dark:border-gray-700 dark:text-gray-600">&larr; Previous</span>
                    @endif

                    @if ($navigation['next'])
                        <a href="{{ route('aksic.show', ['aksic' => $navigation['next']] + $navQuery) }}"
                            class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                            title="Case {{ $navigation['next']->application_no }}">
                            Next &rarr;
                        </a>
                    @else
                        <span class="inline-flex cursor-not-allowed items-center gap-1 rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-300 dark:border-gray-700 dark:text-gray-600">Next &rarr;</span>
                    @endif

                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                        Case {{ min($navigation['position'], max($navigation['total'], 1)) }} of {{ $navigation['total'] }}{{ $pendingOnly ? ' pending' : '' }}
                    </span>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('aksic.show', $aksic) }}"
                        class="rounded-md px-3 py-1.5 font-semibold {{ $pendingOnly ? 'text-gray-500 hover:text-gray-800 dark:text-gray-400' : 'bg-blue-950 text-white' }}">All cases</a>
                    <a href="{{ route('aksic.show', ['aksic' => $aksic, 'nav' => 'pending']) }}"
                        class="rounded-md px-3 py-1.5 font-semibold {{ $pendingOnly ? 'bg-blue-950 text-white' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">Pending only</a>
                </div>
            </div>

            @if (! $canModifyAksic)
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                    This case is locked because its repayment schedule has been generated. Only a super-admin can edit or delete it.
                </div>
            @endif

            {{-- Snapshot -------------------------------------------------- --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 ring-inset {{ $statusClass }}">
                            {{ $val($aksic->status) }}
                        </span>
                        @if ($aksic->bank_status)
                            <span class="text-xs text-gray-500 dark:text-gray-400">Bank status: {{ $aksic->bank_status }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Created {{ $aksic->created_at?->format('d.m.Y H:i') }}
                        @if ($aksic->creator) by {{ $aksic->creator->name }} @endif
                    </p>
                </div>

                <dl class="grid grid-cols-2 divide-gray-200 dark:divide-gray-700 md:grid-cols-5 md:divide-x">
                    <div class="px-6 py-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Principal</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $money($aksic->principal_amount) }}</dd>
                    </div>
                    <div class="px-6 py-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenure</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $aksic->tenure ? $aksic->tenure.' Months' : $dash }}</dd>
                    </div>
                    <div class="px-6 py-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Markup Rate</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $pct($aksic->total_rate) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400">KIBOR {{ $pct($aksic->kibor_rate) }} + Spread {{ $pct($aksic->spread_rate) }}</dd>
                    </div>
                    <div class="px-6 py-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Markup</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $money($aksic->total_interest) }}</dd>
                    </div>
                    <div class="px-6 py-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Payable</dt>
                        <dd class="mt-1 text-lg font-bold text-green-800 dark:text-green-300">
                            {{ $schedule->isEmpty() ? $dash : number_format($totalPayable, 2) }}
                        </dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400">{{ $schedule->count() }} installments</dd>
                    </div>
                </dl>
            </div>

            {{-- Applicant + Business ------------------------------------- --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Applicant</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500 dark:text-gray-400">Applicant Name</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $val($aksic->name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Father Name</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->father_name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">CNIC</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->cnic) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">CNIC Issue Date</dt><dd class="text-gray-900 dark:text-gray-100">{{ $date($aksic->cnic_issue_date) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Date of Birth</dt><dd class="text-gray-900 dark:text-gray-100">{{ $date($aksic->dob) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->phone) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Quota</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->quota) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Gender</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->gender) }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Permanent Address</dt><dd class="whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $val($aksic->permanent_address) }}</dd></div>
                    </dl>
                </div>

                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Business</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500 dark:text-gray-400">Business Name</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $val($aksic->business_name) }}</dd></div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Business Nature</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $val($aksic->business_type) }}
                                @if ($aksic->is_startup_business)
                                    <span class="ml-1 rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">Start-up</span>
                                @endif
                            </dd>
                        </div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Business Category</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->businessCategory?->name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Business Sub Category</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->businessSubCategory?->name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Tier</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->tier) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Applied Amount</dt><dd class="text-gray-900 dark:text-gray-100">{{ $money($aksic->amount) }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Business Address</dt><dd class="whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $val($aksic->business_address) }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- Location & branch + Financing ---------------------------- --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Location, Branch &amp; Fee</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500 dark:text-gray-400">District</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $val($aksic->district?->name ?? $aksic->district_name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Tehsil</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->tehsil_name) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Financing Branch</dt><dd class="text-gray-900 dark:text-gray-100">{{ $aksic->branch ? $aksic->branch->code.' - '.$aksic->branch->name : $dash }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Branch Chosen by Applicant</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->applicant_choosed_branch_code) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Challan Branch Code</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->challan_branch_code) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Fee Collecting Branch</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->fee_branch_code) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Challan Fee</dt><dd class="text-gray-900 dark:text-gray-100">{{ $money($aksic->challan_fee) }}</dd></div>
                        @if ($rule)
                            <div><dt class="text-gray-500 dark:text-gray-400">Scheme Rule</dt>
                                <dd class="text-gray-900 dark:text-gray-100">
                                    {{ $val($rule->district_name) }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ number_format((int) $rule->proposed_beneficiaries) }} beneficiaries, {{ $pct($rule->population_percentage) }} population)
                                    </span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Financing &amp; Security</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500 dark:text-gray-400">Account No</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $val($aksic->account_no) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Date of Disbursement</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $date($aksic->disbursement_date) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Principal Amount</dt><dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $money($aksic->principal_amount) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Tenure</dt><dd class="text-gray-900 dark:text-gray-100">{{ $aksic->tenure ? $aksic->tenure.' Months' : $dash }}</dd></div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Site Visit</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $aksic->site_visit_completed ? 'Completed' : 'Not completed' }}
                                @if ($aksic->site_visit_date)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $date($aksic->site_visit_date) }})</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Consent</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $val($aksic->consent_entry) }}
                                @if ($aksic->consent_date)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $date($aksic->consent_date) }})</span>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Liquid Security</dt><dd class="whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $val($aksic->liquid_security) }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Personal Guarantees</dt><dd class="whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $val($aksic->personal_guarantees) }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Mortgage</dt><dd class="whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $val($aksic->mortgage) }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Last Updated</dt><dd class="text-gray-900 dark:text-gray-100">{{ $aksic->updated_at?->format('d.m.Y H:i') ?? $dash }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Updated By</dt><dd class="text-gray-900 dark:text-gray-100">{{ $val($aksic->updater?->name) }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- Repayment schedule --------------------------------------- --}}
            <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h3 class="text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Repayment Schedule</h3>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            Date of Disbursement:
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $date($aksic->disbursement_date) }}</span>
                        </p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $schedule->count() }} installments
                        @if ($schedule->isNotEmpty())
                            &middot; first {{ $date($schedule->first()->due_date) }}
                            &middot; last {{ $date($schedule->last()->due_date) }}
                        @endif
                    </p>
                </div>

                @if ($schedule->isNotEmpty())
                    <dl class="grid grid-cols-1 gap-px border-b border-gray-200 bg-gray-200 text-sm dark:border-gray-700 dark:bg-gray-700 sm:grid-cols-4">
                        <div class="bg-white px-6 py-3 dark:bg-gray-800">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Broken-period markup (instalment 1)</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $isNewSchedule ? number_format($brokenPeriodMarkup, 2) : $dash }}</dd>
                        </div>
                        <div class="bg-white px-6 py-3 dark:bg-gray-800">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Regular markup ({{ $aksic->tenure }} instalments)</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $isNewSchedule ? number_format($regularMarkup, 2) : $dash }}</dd>
                        </div>
                        <div class="bg-white px-6 py-3 dark:bg-gray-800">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Scheme guideline markup</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-gray-100">
                                {{ $guidelineMarkup === null ? $dash : number_format($guidelineMarkup, 2) }}
                                @if ($isNewSchedule && $guidelineMarkup !== null)
                                    @if (abs($regularMarkup - $guidelineMarkup) < 0.01)
                                        <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-green-800 dark:bg-green-900/50 dark:text-green-200">Matches</span>
                                    @else
                                        <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-red-800 dark:bg-red-900/50 dark:text-red-200">Differs</span>
                                    @endif
                                @endif
                            </dd>
                        </div>
                        <div class="bg-white px-6 py-3 dark:bg-gray-800">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total markup</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-green-800 dark:text-green-300">{{ number_format($totalInterest, 2) }}</dd>
                        </div>
                    </dl>
                    @unless ($isNewSchedule)
                        <p class="border-b border-amber-200 bg-amber-50 px-6 py-2 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                            This schedule was generated with the old method (principal repaid from instalment 1). Use "Regenerate Schedule" to apply the new first-instalment rule.
                        </p>
                    @endunless
                @endif

                @if ($schedule->isEmpty())
                    <p class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No repayment schedule yet. The schedule is generated when the case is approved.
                    </p>
                @else
                    {{-- Bordered schedule, same style as the print sheet / claims tables --}}
                    <div class="p-4">
                    <div class="aksic-scroll">
                        <table class="aksic-grid aksic-schedule">
                            <thead>
                                <tr>
                                    <th class="ctr">#</th>
                                    <th>Due Date</th>
                                    <th class="num">Principal Outstanding</th>
                                    <th class="num">Principal Instalment</th>
                                    <th class="num">Markup per Year</th>
                                    <th class="num">Markup per Month</th>
                                    <th class="num">Markup</th>
                                    <th class="ctr">Days</th>
                                    <th class="num">Total Instalment</th>
                                    <th class="num">Outstanding Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedule as $row)
                                    <tr>
                                        <td class="ctr">
                                            {{ $row->installment_no }}
                                            @if ($loop->first && $isNewSchedule)
                                                <span style="display:block;font-size:9px;font-weight:700;text-transform:uppercase;color:#b45309" title="Broken period: markup only, principal unchanged">markup only</span>
                                            @endif
                                        </td>
                                        <td>{{ $date($row->due_date) }}</td>
                                        <td class="num">{{ number_format((float) $row->principal_amount_os, 2) }}</td>
                                        <td class="num">{{ number_format((float) $row->installment_per_month, 2) }}</td>
                                        <td class="num">{{ number_format((float) $row->product, 2) }}</td>
                                        <td class="num">{{ number_format((float) $row->interest_rate_per_month, 2) }}</td>
                                        <td class="num">{{ number_format((float) $row->total_interest, 2) }}</td>
                                        <td class="ctr">{{ $row->days }}</td>
                                        <td class="num"><b>{{ number_format((float) $row->total_installment, 2) }}</b></td>
                                        <td class="num">{{ number_format((float) $row->principal_balance_after_installment, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="num">Totals</td>
                                    <td class="num">{{ number_format($totalPrincipal, 2) }}</td>
                                    <td colspan="2"></td>
                                    <td class="num">{{ number_format($totalInterest, 2) }}</td>
                                    <td class="ctr">{{ $schedule->sum('days') }}</td>
                                    <td class="num">{{ number_format($totalPayable, 2) }}</td>
                                    <td class="num">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
        @include('aksics._approve-modal')
    @endpush
</x-app-layout>
