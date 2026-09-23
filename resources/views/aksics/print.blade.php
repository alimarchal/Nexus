@php
    /**
     * AKSIC -- printable loan case sheet + repayment schedule.
     *
     * Standalone document (no app chrome) so that the browser prints exactly
     * what is on screen. Mirrors the Account Opening Form print sheet.
     * Display only: every value below already exists on the aksics /
     * aksic_amortizations tables -- nothing new is captured here.
     */
    $rule = $aksic->aksicRule;
    $schedule = $aksic->amortizations;

    $val = fn ($value) => filled($value) ? e($value) : '&mdash;';
    $money = fn ($value) => $value === null ? '&mdash;' : number_format((float) $value, 2);
    $pct = fn ($value) => $value === null ? '&mdash;' : number_format((float) $value, 2).'%';
    $date = fn ($value) => \App\Support\AksicDate::display($value, '&mdash;');
    $tick = fn ($value) => $value ? '&#9632;' : '&#9633;';

    // Principal in words -- derived for the sanction sheet, not stored.
    $words = null;
    if ($aksic->principal_amount !== null && class_exists(\NumberFormatter::class)) {
        try {
            $words = \Illuminate\Support\Str::title(
                \Illuminate\Support\Number::spell((int) round((float) $aksic->principal_amount))
            ).' Rupees Only';
        } catch (\Throwable) {
            $words = null;
        }
    }

    $totalInterest = (float) $schedule->sum('total_interest');
    $totalPayable = (float) $schedule->sum('total_installment');
    $totalPrincipal = (float) $schedule->sum('installment_per_month');
    $isNewSchedule = $schedule->isNotEmpty() && (float) $schedule->first()->installment_per_month === 0.0;
    $brokenPeriodMarkup = (float) ($schedule->first()?->total_interest ?? 0);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aksic->application_no }} — AKSIC Loan Case Sheet</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Helvetica Neue", Arial, sans-serif; font-size: 10.5px; color: #111; margin: 0; padding: 24px; background: #f3f4f6; }
        .sheet { max-width: 900px; margin: 0 auto; background: #fff; padding: 28px 32px; }
        .toolbar { max-width: 900px; margin: 0 auto 12px; display: flex; justify-content: space-between; align-items: center; }
        .btn { display: inline-block; padding: 8px 18px; background: #14532d; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; border: 0; border-radius: 4px; cursor: pointer; }
        .btn.secondary { background: #1e3a5f; }
        header.form-head { text-align: center; border-bottom: 3px double #14532d; padding-bottom: 10px; margin-bottom: 14px; }
        header.form-head .bank { font-size: 19px; font-weight: 800; color: #14532d; letter-spacing: .02em; }
        header.form-head .doc { font-size: 13px; font-weight: 700; margin-top: 2px; }
        header.form-head .variant { font-size: 10px; color: #555; margin-top: 2px; }
        h2.section { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; background: #14532d; color: #fff; padding: 4px 8px; margin: 14px 0 6px; }
        table.grid { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.grid th, table.grid td { border: 1px solid #b8b8b8; padding: 4px 6px; vertical-align: top; text-align: left; }
        table.grid th { width: 22%; background: #f1f5f2; font-weight: 600; font-size: 9.5px; text-transform: uppercase; letter-spacing: .03em; color: #333; }
        table.list { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.list th { border: 1px solid #b8b8b8; background: #f1f5f2; padding: 4px 6px; font-size: 9.5px; text-transform: uppercase; text-align: left; }
        table.list td { border: 1px solid #b8b8b8; padding: 4px 6px; }
        table.list tfoot td { background: #f1f5f2; font-weight: 700; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .ctr { text-align: center; }
        .checks span { display: inline-block; margin-right: 14px; white-space: nowrap; }
        .muted { color: #777; }
        .badge { display: inline-block; padding: 1px 7px; border: 1px solid #14532d; border-radius: 9px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #14532d; }
        .sig-row { display: flex; gap: 18px; margin-top: 14px; }
        .sig-box { flex: 1; border: 1px solid #b8b8b8; height: 82px; padding: 4px 6px; font-size: 9px; position: relative; }
        .sig-box .cap { position: absolute; bottom: 4px; left: 6px; right: 6px; border-top: 1px solid #999; padding-top: 2px; color: #555; }
        footer.meta { margin-top: 16px; border-top: 1px solid #ccc; padding-top: 6px; font-size: 9px; color: #666; display: flex; justify-content: space-between; }
        @media print {
            body { background: #fff; padding: 0; font-size: 9.5px; }
            .sheet { max-width: none; padding: 0; }
            .toolbar { display: none; }
            h2.section, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.list th, table.grid th, table.list tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.list thead { display: table-header-group; }
            table.list tr { break-inside: avoid; page-break-inside: avoid; }
            .avoid-break { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a class="btn secondary" href="{{ route('aksic.show', $aksic) }}">&larr; Back</a>
    <button class="btn" onclick="window.print()">Print</button>
</div>

<div class="sheet">
    <header class="form-head">
        <div class="bank">The Bank of Azad Jammu &amp; Kashmir</div>
        <div class="doc">AKSIC Financing &mdash; Loan Case Sheet &amp; Repayment Schedule</div>
        <div class="variant">
            Application No. {{ $aksic->application_no }}
            &nbsp;&middot;&nbsp; <span class="badge">{{ $aksic->status ?? 'Pending' }}</span>
        </div>
    </header>

    {{-- Case particulars --}}
    <h2 class="section">Case Particulars</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Application No.</th><td>{!! $val($aksic->application_no) !!}</td>
            <th>Status</th><td>{!! $val($aksic->status) !!}</td>
        </tr>
        <tr>
            <th>Account No.</th><td>{!! $val($aksic->account_no) !!}</td>
            <th>Date of Disbursement</th><td>{!! $date($aksic->disbursement_date) !!}</td>
        </tr>
        <tr>
            <th>Bank Status</th><td>{!! $val($aksic->bank_status) !!}</td>
            <th>Tier</th><td>{!! $val($aksic->tier) !!}</td>
        </tr>
        <tr>
            <th>Case Created</th><td>{!! $val($aksic->created_at?->format('d.m.Y H:i')) !!}</td>
            <th>Last Updated</th><td>{!! $val($aksic->updated_at?->format('d.m.Y H:i')) !!}</td>
        </tr>
        <tr>
            <th>Entered By</th><td>{!! $val($aksic->creator?->name) !!}</td>
            <th>Updated By</th><td>{!! $val($aksic->updater?->name) !!}</td>
        </tr>
    </table>

    {{-- Applicant --}}
    <h2 class="section">Applicant Particulars</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Applicant Name</th><td>{!! $val($aksic->name) !!}</td>
            <th>Father Name</th><td>{!! $val($aksic->father_name) !!}</td>
        </tr>
        <tr>
            <th>CNIC</th><td>{!! $val($aksic->cnic) !!}</td>
            <th>CNIC Issue Date</th><td>{!! $date($aksic->cnic_issue_date) !!}</td>
        </tr>
        <tr>
            <th>Date of Birth</th><td>{!! $date($aksic->dob) !!}</td>
            <th>Contact Number</th><td>{!! $val($aksic->phone) !!}</td>
        </tr>
        <tr>
            <th>Quota</th><td>{!! $val($aksic->quota) !!}</td>
            <th>Gender</th><td>{!! $val($aksic->gender) !!}</td>
        </tr>
        <tr>
            <th>Permanent Address</th>
            <td colspan="3">{!! $val($aksic->permanent_address) !!}</td>
        </tr>
    </table>

    {{-- Business --}}
    <h2 class="section">Business Particulars</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Business Name</th><td>{!! $val($aksic->business_name) !!}</td>
            <th>Business Nature</th>
            <td class="checks">
                <span>{!! $tick($aksic->business_type === 'Existing') !!} Existing</span>
                <span>{!! $tick($aksic->business_type === 'New') !!} New / Start-up</span>
            </td>
        </tr>
        <tr>
            <th>Business Category</th><td>{!! $val($aksic->businessCategory?->name) !!}</td>
            <th>Business Sub Category</th><td>{!! $val($aksic->businessSubCategory?->name) !!}</td>
        </tr>
        <tr>
            <th>Business Address</th>
            <td colspan="3">{!! $val($aksic->business_address) !!}</td>
        </tr>
    </table>

    {{-- Location, branch & fee --}}
    <h2 class="section">Location, Branch &amp; Application Fee</h2>
    <table class="grid avoid-break">
        <tr>
            <th>District</th><td>{!! $val($aksic->district?->name ?? $aksic->district_name) !!}</td>
            <th>Tehsil</th><td>{!! $val($aksic->tehsil_name) !!}</td>
        </tr>
        <tr>
            <th>Financing Branch</th>
            <td>{!! $val($aksic->branch ? $aksic->branch->code.' - '.$aksic->branch->name : null) !!}</td>
            <th>Branch Chosen by Applicant</th>
            <td>{!! $val($aksic->applicant_choosed_branch_code) !!}</td>
        </tr>
        <tr>
            <th>Challan Branch Code</th><td>{!! $val($aksic->challan_branch_code) !!}</td>
            <th>Fee Collecting Branch</th><td>{!! $val($aksic->fee_branch_code) !!}</td>
        </tr>
        <tr>
            <th>Challan Fee</th><td class="num">{!! $money($aksic->challan_fee) !!}</td>
            <th>Applied Amount</th><td class="num">{!! $money($aksic->amount) !!}</td>
        </tr>
    </table>

    {{-- Financing terms --}}
    <h2 class="section">Financing Terms &amp; Pricing</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Principal Amount (PKR)</th><td class="num">{!! $money($aksic->principal_amount) !!}</td>
            <th>Tenure</th><td>{{ $aksic->tenure ? $aksic->tenure.' Months' : '—' }}</td>
        </tr>
        @if ($words)
            <tr>
                <th>Amount in Words</th>
                <td colspan="3">{{ $words }}</td>
            </tr>
        @endif
        <tr>
            <th>KIBOR Rate</th><td class="num">{!! $pct($aksic->kibor_rate) !!}</td>
            <th>Spread Rate</th><td class="num">{!! $pct($aksic->spread_rate) !!}</td>
        </tr>
        <tr>
            <th>Total Markup Rate</th><td class="num">{!! $pct($aksic->total_rate) !!}</td>
            <th>Total Markup Amount</th><td class="num">{!! $money($aksic->total_interest) !!}</td>
        </tr>
        <tr>
            <th>Disbursement Date</th><td>{!! $date($aksic->disbursement_date) !!}</td>
            <th>Total Amount Payable</th>
            <td class="num">{{ $schedule->isEmpty() ? '—' : number_format($totalPayable, 2) }}</td>
        </tr>
    </table>

    {{-- Security & due diligence --}}
    <h2 class="section">Security, Consent &amp; Due Diligence</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Site Visit Completed</th>
            <td class="checks">
                <span>{!! $tick($aksic->site_visit_completed) !!} Yes</span>
                <span>{!! $tick(! $aksic->site_visit_completed) !!} No</span>
            </td>
            <th>Site Visit Date</th><td>{!! $date($aksic->site_visit_date) !!}</td>
        </tr>
        <tr>
            <th>Consent Entry</th><td>{!! $val($aksic->consent_entry) !!}</td>
            <th>Consent Date</th><td>{!! $date($aksic->consent_date) !!}</td>
        </tr>
        <tr>
            <th>Liquid Security</th>
            <td colspan="3">{!! $val($aksic->liquid_security) !!}</td>
        </tr>
        <tr>
            <th>Personal Guarantees</th>
            <td colspan="3">{!! $val($aksic->personal_guarantees) !!}</td>
        </tr>
        <tr>
            <th>Mortgage</th>
            <td colspan="3">{!! $val($aksic->mortgage) !!}</td>
        </tr>
    </table>

    @if ($rule)
        {{-- District rule applied --}}
        <h2 class="section">AKSIC Scheme Rule Applied</h2>
        <table class="grid avoid-break">
            <tr>
                <th>Rule District</th><td>{!! $val($rule->district_name) !!}</td>
                <th>Population Share</th><td class="num">{!! $pct($rule->population_percentage) !!}</td>
            </tr>
            <tr>
                <th>Proposed Beneficiaries</th>
                <td class="num">{{ number_format((int) $rule->proposed_beneficiaries) }}</td>
                <th>Rule Status</th><td>{{ $rule->is_active ? 'Active' : 'Inactive' }}</td>
            </tr>
        </table>
    @endif

    {{-- Repayment schedule --}}
    <h2 class="section">Repayment (Amortization) Schedule</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Date of Disbursement</th><td>{!! $date($aksic->disbursement_date) !!}</td>
            <th>Principal / Tenure</th><td>{!! $money($aksic->principal_amount) !!} &middot; {{ $aksic->tenure }} months @ {!! $pct($aksic->total_rate) !!}</td>
        </tr>
        @if ($isNewSchedule)
            <tr>
                <th>Broken-period Markup</th><td class="num">{{ number_format($brokenPeriodMarkup, 2) }}</td>
                <th>Regular Markup ({{ $aksic->tenure }} inst.)</th><td class="num">{{ number_format($totalInterest - $brokenPeriodMarkup, 2) }}</td>
            </tr>
        @endif
    </table>
    <table class="list">
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
            @forelse ($schedule as $row)
                <tr>
                    <td class="ctr">{{ $row->installment_no }}@if ($loop->first && $isNewSchedule)<br><span class="muted" style="font-size:8px;">markup only</span>@endif</td>
                    <td>{!! $date($row->due_date) !!}</td>
                    <td class="num">{{ number_format((float) $row->principal_amount_os, 2) }}</td>
                    <td class="num">{{ number_format((float) $row->installment_per_month, 2) }}</td>
                    <td class="num">{{ number_format((float) $row->product, 2) }}</td>
                    <td class="num">{{ number_format((float) $row->interest_rate_per_month, 2) }}</td>
                    <td class="num">{{ number_format((float) $row->total_interest, 2) }}</td>
                    <td class="ctr">{{ $row->days }}</td>
                    <td class="num">{{ number_format((float) $row->total_installment, 2) }}</td>
                    <td class="num">{{ number_format((float) $row->principal_balance_after_installment, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted ctr">
                        No repayment schedule generated yet &mdash; the case is approved to produce the schedule.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($schedule->isNotEmpty())
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
                <tr>
                    <td colspan="6" class="num">Grand Total Payable (Principal + Markup)</td>
                    <td colspan="4" class="num">{{ number_format($totalPayable, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Signatures --}}
    <h2 class="section">Certification</h2>
    <p style="margin:0 0 8px; font-size:9.5px;">
        Certified that the particulars stated above have been verified from the record and the applicant's
        documents, and that the financing is recommended under the AKSIC scheme rules applicable to the
        district shown above.
    </p>
    <div class="sig-row avoid-break">
        <div class="sig-box"><div class="cap">Prepared / Entered By</div></div>
        <div class="sig-box"><div class="cap">Recommended By (Branch Manager)</div></div>
        <div class="sig-box"><div class="cap">Approved By (Signature &amp; Stamp)</div></div>
    </div>
    <div class="sig-row avoid-break">
        <div class="sig-box"><div class="cap">Signature of Applicant</div></div>
        <div class="sig-box"><div class="cap">Signature of Guarantor (1)</div></div>
        <div class="sig-box"><div class="cap">Signature of Guarantor (2)</div></div>
    </div>

    <footer class="meta">
        <span>
            {{ $aksic->application_no }} &middot; CNIC {{ $aksic->cnic }}
            @if ($aksic->branch) &middot; {{ $aksic->branch->code }} @endif
        </span>
        <span>Printed {{ now()->format('d.m.Y H:i') }} by {{ auth()->user()?->name }}</span>
    </footer>
</div>
</body>
</html>
