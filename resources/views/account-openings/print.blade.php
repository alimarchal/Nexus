@php
    $request = $accountOpeningRequest;
    $customer = $request->customer;
    $individual = $customer?->individual;
    $organization = $customer?->organization;
    $account = $request->account;
    $dueDiligence = $account?->dueDiligences->first();
    $isEntity = $request->aof_form_type === \App\Models\AccountOpeningRequest::FORM_ENTITY;

    $address = fn (string $type) => $customer?->addresses->firstWhere('address_type', $type);
    $contact = fn (string $type, int $sequence = 1) => $customer?->contacts
        ->first(fn ($row) => $row->contact_type === $type && (int) $row->sequence === $sequence);
    $tick = fn ($value) => $value ? '&#9632;' : '&#9633;';
    $val = fn ($value) => filled($value) ? e($value) : '&mdash;';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $request->request_number }} — Account Opening Form</title>
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
        .checks span { display: inline-block; margin-right: 14px; white-space: nowrap; }
        .muted { color: #777; }
        .sig-row { display: flex; gap: 18px; margin-top: 14px; }
        .sig-box { flex: 1; border: 1px solid #b8b8b8; height: 82px; padding: 4px 6px; font-size: 9px; position: relative; }
        .sig-box .cap { position: absolute; bottom: 4px; left: 6px; right: 6px; border-top: 1px solid #999; padding-top: 2px; color: #555; }
        .sig-box img { max-height: 44px; max-width: 100%; }
        footer.meta { margin-top: 16px; border-top: 1px solid #ccc; padding-top: 6px; font-size: 9px; color: #666; display: flex; justify-content: space-between; }
        @media print {
            body { background: #fff; padding: 0; font-size: 9.5px; }
            .sheet { max-width: none; padding: 0; }
            .toolbar { display: none; }
            h2.section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.list th, table.grid th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .avoid-break { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a class="btn secondary" href="{{ route('account-openings.show', $request) }}">&larr; Back</a>
    <button class="btn" onclick="window.print()">Print</button>
</div>

<div class="sheet">
    <header class="form-head">
        <div class="bank">The Bank of Azad Jammu &amp; Kashmir</div>
        <div class="doc">Account Opening Form</div>
        <div class="variant">
            {{ $isEntity
                ? 'Government, Partnership, Public/Private Limited Companies, NGOs/NPOs/Charities, Clubs, Societies and Associations'
                : 'Individual / Joint & Sole Proprietor Account' }}
            &nbsp;&middot;&nbsp; {{ $request->aof_version }}
        </div>
    </header>

    {{-- #01 / #02 Particulars of Account + Bank Use Only --}}
    <h2 class="section">Particulars of Account &mdash; For Bank Use Only</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Title of Account</th><td>{!! $val($account?->title_of_account) !!}</td>
            <th>Branch Code</th><td>{!! $val($request->branch?->code) !!} &mdash; {!! $val($request->branch?->name) !!}</td>
        </tr>
        <tr>
            <th>Account Number</th><td>{!! $val($account?->account_number) !!}</td>
            <th>Date</th><td>{!! $val($request->request_date?->format('d-m-Y')) !!}</td>
        </tr>
        <tr>
            <th>IBAN</th><td>{!! $val($account?->iban) !!}</td>
            <th>Profit Center</th><td>{!! $val($account?->profit_center) !!}</td>
        </tr>
        <tr>
            <th>Client / Relationship ID</th>
            <td colspan="3">
                @forelse ($account?->holders ?? collect() as $holder)
                    ({{ $holder->applicant_number }}) {{ $holder->customer?->cif_number }}@if (! $loop->last), @endif
                @empty
                    {!! $val($customer?->cif_number) !!}
                @endforelse
            </td>
        </tr>
        <tr>
            <th>Request No.</th><td>{{ $request->request_number }}</td>
            <th>Status</th><td>{{ \Illuminate\Support\Str::headline($request->status) }}</td>
        </tr>
    </table>

    {{-- #04 / #05 Customer Category --}}
    <h2 class="section">Customer Information Form (CIF)</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Client / Relationship ID</th><td>{!! $val($customer?->cif_number) !!}</td>
            <th>CIF Date</th><td>{!! $val($customer?->cif_date?->format('d-m-Y')) !!}</td>
        </tr>
        <tr>
            <th>Customer Category</th><td>{!! $val($customer?->customerCategory?->name) !!}</td>
            <th>Category Code &amp; Desc.</th><td>{!! $val($customer?->category_code_description) !!}</td>
        </tr>
        <tr>
            <th>Economic Sector Code</th><td>{!! $val($customer?->economicSector?->code) !!}</td>
            <th>Others (specify)</th><td>{!! $val($customer?->customer_category_other) !!}</td>
        </tr>
        @if ($customer?->specialCategories->isNotEmpty())
            <tr>
                <th>Special Category of Account</th>
                <td colspan="3">
                    @foreach ($customer->specialCategories as $special)
                        {{ $special->name }}@if ($special->pivot->extra_value) ({{ $special->pivot->extra_value }})@endif
                        @if (! $loop->last) &middot; @endif
                    @endforeach
                </td>
            </tr>
        @endif
    </table>

    {{-- #06 Customer Details For Individuals --}}
    @if ($individual)
        <h2 class="section">Customer Details For Individuals</h2>
        <table class="grid avoid-break">
            <tr>
                <th>Name</th><td>{!! $val(trim(($individual->title ? $individual->title.' ' : '').$individual->full_name)) !!}</td>
                <th>S/o, D/o, W/o</th><td>{!! $val($individual->parentageRelationship?->name) !!} {!! $val($individual->parent_or_spouse_name) !!}</td>
            </tr>
            <tr>
                <th>Mother's Maiden Name</th><td>{!! $val($individual->mother_maiden_name) !!}</td>
                <th>Gender</th><td>{!! $val($individual->gender?->name) !!}</td>
            </tr>
            <tr>
                <th>Marital Status</th><td>{!! $val($individual->maritalStatus?->name) !!}</td>
                <th>Education</th><td>{!! $val($individual->educationLevel?->name) !!}</td>
            </tr>
            <tr>
                <th>Date of Birth</th><td>{!! $val($individual->date_of_birth?->format('d-m-Y')) !!}</td>
                <th>Country / City of Birth</th><td>{!! $val($individual->birthCountry?->name) !!} / {!! $val($individual->birth_city) !!}</td>
            </tr>
            <tr>
                <th>Country of Residence</th><td>{!! $val($individual->residenceCountry?->name) !!}</td>
                <th>Nationalities</th><td>{!! $val($customer->nationalities->map(fn ($n) => $n->country?->name)->filter()->implode(', ')) !!}</td>
            </tr>
            <tr>
                <th>Profession</th><td>{!! $val($individual->profession?->name) !!}</td>
                <th>Designation</th><td>{!! $val($individual->designation) !!}</td>
            </tr>
            <tr>
                <th>N.T.N Number</th><td>{!! $val($individual->ntn) !!}</td>
                <th>Employer / Institution</th><td>{!! $val($individual->employer_institution_name) !!}</td>
            </tr>
            @if ($individual->is_minor)
                <tr>
                    <th>Guardian</th><td>{!! $val($individual->guardian_name) !!}</td>
                    <th>Relationship with Minor</th><td>{!! $val($individual->guardianRelationship?->name) !!}</td>
                </tr>
            @endif
            @if ($individual->requires_witness)
                <tr>
                    <th>Witness</th><td>{!! $val($individual->witness_name) !!} ({!! $val($individual->witness_cnic) !!})</td>
                    <th>Relation with Customer</th><td>{!! $val($individual->witness_relation) !!}</td>
                </tr>
            @endif
            <tr>
                <th>Mobile Number Portability</th>
                <td colspan="3" class="checks">
                    <span>{!! $tick($individual->has_availed_mnp) !!} Availed MNP</span>
                    <span>New provider: {!! $val($individual->mnp_new_provider) !!}</span>
                </td>
            </tr>
        </table>
    @endif

    {{-- #06 Customer Details For Business --}}
    @if ($organization)
        <h2 class="section">Customer Details For Business</h2>
        <table class="grid avoid-break">
            <tr>
                <th>Company / Business Name</th><td>{!! $val($organization->business_name) !!}</td>
                <th>Nature of Business</th><td>{!! $val($organization->businessNature?->name) !!}</td>
            </tr>
            <tr>
                <th>Business Registration No.</th><td>{!! $val($organization->business_registration_number) !!}</td>
                <th>Issued / Expiry Date</th><td>{!! $val($organization->issued_date?->format('d-m-Y')) !!} / {!! $val($organization->expiry_date?->format('d-m-Y')) !!}</td>
            </tr>
            <tr>
                <th>Commencement Date</th><td>{!! $val($organization->business_commencement_date?->format('d-m-Y')) !!}</td>
                <th>Incorporation Date</th><td>{!! $val($organization->business_incorporation_date?->format('d-m-Y')) !!}</td>
            </tr>
            <tr>
                <th>Years in Business</th><td>{!! $val($organization->years_in_business) !!}</td>
                <th>Country of Incorporation</th><td>{!! $val($organization->incorporationCountry?->name) !!}</td>
            </tr>
            <tr>
                <th>NTN Number</th><td>{!! $val($organization->ntn) !!}</td>
                <th>Sales Tax Registration No.</th><td>{!! $val($organization->sales_tax_registration_number) !!}</td>
            </tr>
            <tr>
                <th>Chamber / Trade Body No.</th><td>{!! $val($organization->chamber_membership_number) !!}</td>
                <th>Number of Employees</th><td>{!! $val($organization->number_of_employees) !!}</td>
            </tr>
            <tr>
                <th>Tax Exemption</th>
                <td colspan="3" class="checks">
                    <span>{!! $tick($organization->tax_exempt_on_cash_withdrawal) !!} On Cash Withdrawal</span>
                    <span>{!! $tick($organization->tax_exempt_on_profit) !!} On Profit</span>
                    <span>{!! $tick($organization->is_dnfbp) !!} Is a DNFBP</span>
                </td>
            </tr>
            <tr>
                <th>Parent Company / Group</th><td>{!! $val($organization->parent_company_name) !!}</td>
                <th>Main Geographic Area</th><td>{!! $val($organization->main_geographic_area) !!}</td>
            </tr>
        </table>
    @endif

    {{-- #07 Identification (the entity AOF has no such block of its own) --}}
    @if ($customer?->identifications->isNotEmpty() || $customer?->needsIndividualProfile())
    <h2 class="section">Identification</h2>
    <table class="list avoid-break">
        <thead>
            <tr><th>Document</th><th>Number</th><th>Issued</th><th>Expiry</th><th>Place of Issuance</th><th>Attested</th><th>Verisys</th></tr>
        </thead>
        <tbody>
            @forelse ($customer?->identifications ?? collect() as $identification)
                <tr>
                    <td>{{ $identification->documentType?->name }}</td>
                    <td>{{ $identification->document_number }}</td>
                    <td>{{ $identification->issue_date?->format('d-m-Y') ?? '—' }}</td>
                    <td>{{ $identification->expiry_date?->format('d-m-Y') ?? '—' }}</td>
                    <td>{{ $identification->place_of_issuance ?? '—' }}</td>
                    <td>{!! $tick($identification->is_attested) !!}</td>
                    <td>{!! $tick($identification->verisys_verified) !!}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No identification recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    {{-- #08 / #09 Contact Details --}}
    <h2 class="section">Contact Details</h2>
    <table class="list avoid-break">
        <thead><tr><th>Address</th><th>House / Office</th><th>Street / Area</th><th>Tehsil / District</th><th>City</th><th>Country</th><th>Postal Code</th></tr></thead>
        <tbody>
            @foreach ([
                'permanent_residential' => 'Permanent Residential',
                'registered_business' => 'Registered Business',
                'current_residential' => 'Current Residential',
                'current_business' => 'Current Business',
                'mailing' => 'Statement Mailing',
            ] as $type => $label)
                @php $row = $address($type); @endphp
                @if ($row)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $row->house_office_no ?? '—' }}</td>
                        <td>{{ $row->street_area ?? '—' }}</td>
                        <td>{{ $row->tehsil_district ?? '—' }}</td>
                        <td>{{ $row->city ?? '—' }}</td>
                        <td>{{ $row->country?->name ?? '—' }}</td>
                        <td>{{ $row->postal_code ?? '—' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <table class="grid avoid-break">
        <tr>
            <th>Mobile No.</th><td>{!! $val(trim(($contact('mobile')?->country_code ?? '').' '.($contact('mobile')?->value ?? ''))) !!}</td>
            <th>Telephone Res. / Office</th>
            <td>{!! $val($contact('telephone_residence_office')?->value) !!}
                @if ($contact('telephone_residence_office', 2))
                    &nbsp;/&nbsp; {!! $val($contact('telephone_residence_office', 2)?->value) !!}
                @endif
            </td>
        </tr>
        <tr>
            <th>E-Mail ID</th><td>{!! $val($contact('email_personal_office')?->value) !!}</td>
            <th>Fax</th><td>{!! $val($contact('fax_personal_office')?->value) !!}</td>
        </tr>
        <tr>
            <th>Zakat Exemption</th>
            <td colspan="3" class="checks">
                <span>{!! $tick($customer?->is_zakat_exempt) !!} Exempt</span>
                <span>Code: {!! $val($customer?->zakatExemptionReason?->name) !!}</span>
                <span>CZ-50 submitted: {!! $val($customer?->cz50_submitted_on?->format('d-m-Y')) !!}</span>
            </td>
        </tr>
    </table>

    {{-- #12 / #13 / #14 FATCA, CRS, Tax Residency --}}
    <h2 class="section">FATCA / CRS &amp; Tax Residency</h2>
    @php $fatca = $customer?->fatcaDetail; @endphp
    <table class="grid avoid-break">
        @unless ($isEntity)
            <tr>
                <th>US Person?</th><td>{!! $tick($fatca?->q1_is_us_person) !!} Yes</td>
                <th>Country of birth US?</th><td>{!! $tick($fatca?->q2_country_of_birth_us) !!} Yes</td>
            </tr>
            <tr>
                <th>US Address / Telephone?</th><td>{!! $tick($fatca?->q3_has_us_address_or_phone) !!} Yes</td>
                <th>US mandate / links?</th><td>{!! $tick($fatca?->q4_has_us_mandate_or_links) !!} Yes</td>
            </tr>
            <tr>
                <th>Forms</th>
                <td colspan="3" class="checks">
                    <span>{!! $tick($fatca?->form_w9_signed) !!} W9</span>
                    <span>{!! $tick($fatca?->form_w8_ben_signed) !!} W-8 BEN</span>
                    <span>{!! $tick($fatca?->us_nationality_revoked) !!} Revocation of U.S. nationality claimed</span>
                </td>
            </tr>
        @else
            <tr>
                <th>Incorporated in the U.S.?</th><td>{!! $tick($fatca?->entity_incorporated_in_us) !!} Yes</td>
                <th>Country of Incorporation</th><td>{!! $val($fatca?->incorporationCountry?->name) !!}</td>
            </tr>
            <tr>
                <th>NFE Type</th><td>{!! $val($fatca?->nfe_type ? ucfirst($fatca->nfe_type).' NFE' : null) !!}</td>
                <th>W8-BEN-E</th><td>{!! $tick($fatca?->form_w8_ben_e_signed) !!} Signed</td>
            </tr>
            <tr>
                <th>CRS Entity Classification</th>
                <td colspan="3">
                    @if ($fatca?->crsClassification)
                        ({{ $fatca->crsClassification->form_option }}) {{ $fatca->crsClassification->name }}
                    @else
                        &mdash;
                    @endif
                </td>
            </tr>
            <tr>
                <th>GIIN</th><td>{!! $val($fatca?->giin) !!}</td>
                <th>Listed Stock Exchange</th><td>{!! $val($fatca?->listed_stock_exchange) !!}</td>
            </tr>
        @endunless
        <tr>
            <th>Politically Exposed Person</th>
            <td colspan="3" class="checks">
                <span>{!! $tick($customer?->is_pep) !!} PEP</span>
                <span>{!! $tick($customer?->pep_form_attached) !!} PEP declaration form attached</span>
            </td>
        </tr>
    </table>

    <table class="list avoid-break">
        <thead><tr><th>Country / Jurisdiction of Tax Residence</th><th>TIN / NTN</th><th>Reason (A/B/C)</th><th>Explanation (Reason B)</th></tr></thead>
        <tbody>
            @forelse ($customer?->taxResidencies ?? collect() as $residency)
                <tr>
                    <td>{{ $residency->country?->name }}</td>
                    <td>{{ $residency->tin ?? '—' }}</td>
                    <td>{{ $residency->no_tin_reason ?? '—' }}</td>
                    <td>{{ $residency->reason_b_explanation ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No tax residency declared.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($customer?->controllingPersons->isNotEmpty())
        <table class="list avoid-break">
            <thead><tr><th>#</th><th>Controlling Person / Shareholder</th><th>Designation</th><th>Share / Voting %</th><th>Type</th></tr></thead>
            <tbody>
                @foreach ($customer->controllingPersons as $person)
                    <tr>
                        <td>{{ $person->sequence }}</td>
                        <td>{{ $person->person_name }}</td>
                        <td>{{ $person->designation ?? '—' }}</td>
                        <td>{{ $person->shareholding_percentage ?? '—' }}</td>
                        <td>{{ $person->controllingPersonType?->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- #16 to #25 Account --}}
    <h2 class="section">Type of Account, Operating Instructions &amp; Services</h2>
    @php $chequeBook = $account?->chequeBooks->first(); $debitCard = $account?->debitCards->first(); @endphp
    <table class="grid avoid-break">
        <tr>
            <th>Product</th><td>{!! $val($account?->product?->name) !!}</td>
            <th>Currency</th><td>{!! $val($account?->currency?->code) !!} ({{ $account?->is_foreign_currency ? 'FCY' : 'Rupee' }})</td>
        </tr>
        <tr>
            <th>Operating Instruction</th><td>{!! $val($account?->operatingInstruction?->name) !!}</td>
            <th>Initial Deposit</th><td>{!! $val($account?->initial_deposit !== null ? number_format((float) $account->initial_deposit, 2) : null) !!}</td>
        </tr>
        <tr>
            <th>Special / Standing Instructions</th><td colspan="3">{!! $val($account?->special_instructions) !!}</td>
        </tr>
        <tr>
            <th>Services</th>
            <td colspan="3" class="checks">
                <span>{!! $tick($account?->sms_alerts_subscribed) !!} SMS Alerts</span>
                <span>{!! $tick($account?->digital_channels_opted) !!} Digital Channels / App</span>
                <span>{!! $tick($account?->digital_channels_biometric_verified) !!} Biometric verified</span>
                <span>{!! $tick($account?->zakat_applicable) !!} Zakat applicable</span>
            </td>
        </tr>
        <tr>
            <th>Statement</th><td>{!! $val($account?->statementDeliveryMode?->name) !!}</td>
            <th>Frequency</th><td>{!! $val($account?->statementFrequency?->name) !!}</td>
        </tr>
        <tr>
            <th>Cheque Book</th>
            <td>{!! $tick($chequeBook?->is_required) !!} Required
                @if ($chequeBook?->is_required)
                    &mdash; {{ $chequeBook->quantity_required }} book(s) of {{ $chequeBook->leaves_count }} leaves
                @endif
            </td>
            <th>Debit Card</th>
            <td>
                @if ($debitCard)
                    {{ $debitCard->cardType?->name }} &mdash; {{ $debitCard->name_on_card }}
                @else
                    &#9633; Not requested
                @endif
            </td>
        </tr>
    </table>

    {{-- #30 Applicants --}}
    <h2 class="section">Applicants &amp; Specimen Signatures</h2>
    <table class="list avoid-break">
        <thead><tr><th>#</th><th>Client / Relationship ID</th><th>Name</th><th>Role</th><th>Designation</th><th>Signatory</th></tr></thead>
        <tbody>
            @forelse ($account?->holders ?? collect() as $holder)
                <tr>
                    <td>{{ $holder->applicant_number }}</td>
                    <td>{{ $holder->customer?->cif_number }}</td>
                    <td>{{ $holder->customer?->displayName() }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($holder->holder_role) }}</td>
                    <td>{{ $holder->designation ?? '—' }}</td>
                    <td>{!! $tick($holder->is_signatory) !!}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No applicants recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sig-row avoid-break">
        @foreach (($account?->holders ?? collect())->take(3) as $holder)
            @php $specimen = $holder->specimenSignatures->firstWhere('is_active', true); @endphp
            <div class="sig-box">
                @if ($specimen?->signature_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($specimen->signature_image_path) }}" alt="">
                @endif
                <div class="cap">Applicant ({{ $holder->applicant_number }}) — {{ $holder->customer?->displayName() }}</div>
            </div>
        @endforeach
        @if (($account?->holders ?? collect())->isEmpty())
            <div class="sig-box"><div class="cap">Applicant's Signature / Thumb Impression</div></div>
            <div class="sig-box"><div class="cap">Applicant's Signature / Thumb Impression</div></div>
        @endif
    </div>

    {{-- #29 Next of kin --}}
    @if ($customer?->nextOfKin->isNotEmpty())
        <h2 class="section">Contact Persons (Next of Kin)</h2>
        <table class="list avoid-break">
            <thead><tr><th>Name</th><th>Relationship</th><th>ID Number</th><th>Address</th><th>City</th><th>Telephone</th><th>Email</th></tr></thead>
            <tbody>
                @foreach ($customer->nextOfKin as $kin)
                    <tr>
                        <td>{{ $kin->name }}</td>
                        <td>{{ $kin->relationship?->name ?? '—' }}</td>
                        <td>{{ $kin->identification_number ?? '—' }}</td>
                        <td>{{ $kin->address ?? '—' }}</td>
                        <td>{{ $kin->city ?? '—' }}</td>
                        <td>{{ $kin->telephone ?? '—' }}</td>
                        <td>{{ $kin->email ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- #31 to #33 CDD --}}
    @if ($dueDiligence)
        <h2 class="section">Customer Due Diligence &mdash; For Bank Use Only</h2>
        <table class="grid avoid-break">
            <tr>
                <th>Type of Customer</th>
                <td>{!! $val($dueDiligence->customer_source ? \Illuminate\Support\Str::headline($dueDiligence->customer_source) : null) !!}
                    {{ $dueDiligence->referred_by ? '— '.$dueDiligence->referred_by : '' }}</td>
                <th>Verification</th>
                <td class="checks">
                    <span>{!! $tick($dueDiligence->physical_verification_conducted) !!} Physical</span>
                    <span>{!! $tick($dueDiligence->proscribed_list_cleared) !!} Proscribed list cleared</span>
                </td>
            </tr>
            <tr>
                <th>Source of Income</th><td colspan="3">{!! $val($dueDiligence->incomeSources->pluck('name')->implode(', ')) !!}</td>
            </tr>
            <tr>
                <th>Source of Wealth</th><td colspan="3">{!! $val($dueDiligence->wealthSources->pluck('name')->implode(', ')) !!}</td>
            </tr>
            <tr>
                <th>Employer</th><td>{!! $val($dueDiligence->employer_name) !!} {{ $dueDiligence->employer_designation ? '('.$dueDiligence->employer_designation.')' : '' }}</td>
                <th>Monthly Income</th><td>{!! $val($dueDiligence->monthly_income !== null ? number_format((float) $dueDiligence->monthly_income, 2) : null) !!}</td>
            </tr>
            <tr>
                <th>Usual Mode of Credit</th><td>{!! $val($dueDiligence->creditModes->pluck('name')->implode(', ')) !!}</td>
                <th>Usual Mode of Debit</th><td>{!! $val($dueDiligence->debitModes->pluck('name')->implode(', ')) !!}</td>
            </tr>
            <tr>
                <th>Purpose of Account</th><td>{!! $val($dueDiligence->accountPurposes->pluck('name')->implode(', ')) !!}</td>
                <th>Expected Counter Parties</th><td>{!! $val($dueDiligence->counterPartyTypes->pluck('name')->implode(', ')) !!}</td>
            </tr>
            <tr>
                <th>Expected Credit / month</th>
                <td>{!! $val($dueDiligence->expected_monthly_credit_amount !== null ? number_format((float) $dueDiligence->expected_monthly_credit_amount, 2) : null) !!}
                    ({{ $dueDiligence->expected_monthly_credit_count ?? '—' }} txns)</td>
                <th>Expected Debit / month</th>
                <td>{!! $val($dueDiligence->expected_monthly_debit_amount !== null ? number_format((float) $dueDiligence->expected_monthly_debit_amount, 2) : null) !!}
                    ({{ $dueDiligence->expected_monthly_debit_count ?? '—' }} txns)</td>
            </tr>
            <tr>
                <th>Expected IFTT / month</th><td>{!! $val($dueDiligence->expected_iftt_amount) !!}</td>
                <th>Expected OFTT / month</th><td>{!! $val($dueDiligence->expected_oftt_amount) !!}</td>
            </tr>
        </table>

        @if ($account?->ultimateBeneficialOwners->isNotEmpty())
            <table class="list avoid-break">
                <thead><tr><th>Ultimate Beneficial Owner</th><th>Relationship with Customer</th><th>ID Document</th><th>ID Number</th></tr></thead>
                <tbody>
                    @foreach ($account->ultimateBeneficialOwners as $ubo)
                        <tr>
                            <td>{{ $ubo->name }}</td>
                            <td>{{ $ubo->relationship?->name ?? '—' }}</td>
                            <td>{{ $ubo->documentType?->name ?? '—' }}</td>
                            <td>{{ $ubo->identification_number ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    {{-- #27 Documentation --}}
    <h2 class="section">Minimum Documentation to be Obtained</h2>
    <table class="list avoid-break">
        <thead><tr><th>Document</th><th>Status</th><th>Attested</th><th>Remarks</th></tr></thead>
        <tbody>
            @forelse ($request->documents as $document)
                <tr>
                    <td>{{ $document->documentType?->name }}</td>
                    <td>{{ ucfirst($document->status) }}</td>
                    <td>{!! $tick($document->is_attested) !!}</td>
                    <td>{{ $document->remarks ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No documents recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- #26 / #28 / #34 Declarations and recommendation --}}
    <h2 class="section">Declarations &amp; Recommendation</h2>
    <table class="grid avoid-break">
        <tr>
            <th>Customer Declarations</th>
            <td colspan="3" class="checks">
                <span>{!! $tick($request->terms_and_conditions_accepted) !!} Terms &amp; Conditions accepted</span>
                <span>{!! $tick($request->indemnity_undertaking_accepted) !!} Indemnity &amp; Undertaking signed</span>
                <span>{!! $tick($request->aof_copy_received_by_customer) !!} Copy of AOF received</span>
                <span>{!! $tick($request->shariah_compliant_investment_authorized) !!} Shariah compliant investment authorized</span>
            </td>
        </tr>
        <tr>
            <th>Sales Staff / RM (1)</th><td>{!! $val($request->sales_staff_name_1) !!} {{ $request->sales_staff_employee_no_1 ? '('.$request->sales_staff_employee_no_1.')' : '' }}</td>
            <th>Sales Staff / RM (2)</th><td>{!! $val($request->sales_staff_name_2) !!} {{ $request->sales_staff_employee_no_2 ? '('.$request->sales_staff_employee_no_2.')' : '' }}</td>
        </tr>
        <tr>
            <th>Authorized by</th><td>{!! $val($request->authorized_by_name) !!} {{ $request->authorized_on ? '— '.$request->authorized_on->format('d-m-Y') : '' }}</td>
            <th>{{ $isEntity ? 'Branch Manager' : 'Branch Supervisor' }} / Data Entry Checker</th><td>{!! $val($request->checker_name) !!}</td>
        </tr>
    </table>

    <div class="sig-row avoid-break">
        <div class="sig-box"><div class="cap">Signature of Sales Staff / Relationship Manager</div></div>
        <div class="sig-box"><div class="cap">Authorized by (Signature &amp; Stamp)</div></div>
        <div class="sig-box"><div class="cap">{{ $isEntity ? 'Branch Manager' : 'Branch Supervisor' }} / Data Entry Checker</div></div>
    </div>

    <footer class="meta">
        <span>{{ $request->request_number }} &middot; CIF {{ $customer?->cif_number }} &middot; {{ $request->branch?->code }}</span>
        <span>Printed {{ now()->format('d-m-Y H:i') }} by {{ auth()->user()?->name }}</span>
    </footer>
</div>
</body>
</html>
