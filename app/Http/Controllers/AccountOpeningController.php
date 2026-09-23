<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountOpeningRequest;
use App\Http\Requests\UpdateAccountOpeningStepRequest;
use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\AccountPurpose;
use App\Models\Branch;
use App\Models\BusinessNature;
use App\Models\CardType;
use App\Models\ControllingPersonType;
use App\Models\CounterPartyType;
use App\Models\Country;
use App\Models\CrsEntityClassification;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerDueDiligence;
use App\Models\DocumentRequirement;
use App\Models\EconomicSector;
use App\Models\EducationLevel;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\IncomeSource;
use App\Models\MaritalStatus;
use App\Models\OperatingInstruction;
use App\Models\Profession;
use App\Models\Relationship;
use App\Models\SpecialCategory;
use App\Models\StatementDeliveryMode;
use App\Models\StatementFrequency;
use App\Models\TransactionMode;
use App\Models\User;
use App\Models\WealthSource;
use App\Models\ZakatExemptionReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * BAJK Account Opening Form (AOF) module.
 *
 * One wizard serves both printed forms:
 *   - BAJK-AOF-Individual/Joint/Sole Proprietor (19-Jan-2026), 15 pages
 *   - BAJK-AOF-Government/Partnership/Company/NGO (28-Jan-2026), 17 pages
 *
 * The form type is stored on the request and drives which reference options and
 * which wizard fields are shown, so there is a single schema and a single UI.
 */
class AccountOpeningController extends Controller implements HasMiddleware
{
    /**
     * Wizard steps in the order the printed form is filled in.
     *
     * @var array<string, string>
     */
    public const STEPS = [
        'cif' => 'Customer Information (CIF)',
        'identification' => 'Identification, Address & Contact',
        'compliance' => 'FATCA / CRS & Tax Residency',
        'account' => 'Account, Products & Services',
        'holders' => 'Applicants & Signatures',
        'cdd' => 'Due Diligence & Documents',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view account openings', only: ['index', 'show', 'print']),
            new Middleware('role_or_permission:create account openings', only: ['create', 'store']),
            new Middleware('role_or_permission:edit account openings', only: ['editStep', 'updateStep', 'submit']),
            new Middleware('role_or_permission:approve account openings', only: ['approve']),
            new Middleware('role_or_permission:delete account openings', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $accountOpeningRequests = QueryBuilder::for(AccountOpeningRequest::class)
            ->visibleTo(auth()->user())
            ->allowedFilters([
                AllowedFilter::partial('request_number'),
                AllowedFilter::exact('aof_form_type'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('branch_id'),
                AllowedFilter::scope('request_date_from', 'requestDateFrom'),
                AllowedFilter::scope('request_date_to', 'requestDateTo'),
                AllowedFilter::callback('customer', function ($query, $value): void {
                    $query->where(function ($inner) use ($value): void {
                        $inner->whereHas('customer', fn ($q) => $q->where('cif_number', 'like', "%{$value}%"))
                            ->orWhereHas('customer.individual', fn ($q) => $q->where('full_name', 'like', "%{$value}%"))
                            ->orWhereHas('customer.organization', fn ($q) => $q->where('business_name', 'like', "%{$value}%"));
                    });
                }),
            ])
            ->with(['branch', 'customer.individual', 'customer.organization', 'customer.customerCategory', 'account.product'])
            ->defaultSort('-request_date')
            ->paginate(request('per_page', 10))
            ->appends(request()->query());

        return view('account-openings.index', [
            'accountOpeningRequests' => $accountOpeningRequests,
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('account-openings.create', [
            'branches' => Branch::orderBy('name')->get(),
            'defaultBranchId' => auth()->user()->branch_id,
            'categories' => CustomerCategory::active()->ordered()->get(),
            'economicSectors' => EconomicSector::active()->ordered()->get(),
        ]);
    }

    /**
     * Start a new AOF: creates the request container and its primary CIF record.
     */
    public function store(StoreAccountOpeningRequest $request): RedirectResponse
    {
        $category = CustomerCategory::findOrFail($request->customer_category_id);
        $branch = Branch::findOrFail($request->branch_id);

        $accountOpeningRequest = DB::transaction(function () use ($request, $category, $branch): AccountOpeningRequest {
            $customer = Customer::create([
                'cif_number' => generateUniqueIdWithPrefix($branch->code, 'customers', 'cif_number'),
                'branch_id' => $branch->id,
                'cif_date' => $request->request_date,
                'customer_type' => $category->resolvedCustomerType(),
                'aof_form_type' => $request->aof_form_type,
                'customer_category_id' => $category->id,
                'customer_category_other' => $request->customer_category_other,
                'category_code_description' => $request->category_code_description,
                'economic_sector_id' => $request->economic_sector_id,
                'status' => 'draft',
            ]);

            return AccountOpeningRequest::create([
                'request_number' => generateUniqueIdWithPrefix($branch->code, 'account_opening_requests', 'request_number'),
                'aof_form_type' => $request->aof_form_type,
                'aof_version' => $request->aof_form_type === AccountOpeningRequest::FORM_ENTITY
                    ? 'BAJK-AOF-Govt-Public-Private-28-Jan-2026'
                    : 'BAJK-AOF-Individual-19-Jan-2026',
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'request_date' => $request->request_date,
                'status' => 'draft',
            ]);
        });

        return redirect()
            ->route('account-openings.steps.edit', [$accountOpeningRequest, 'cif'])
            ->with('success', "Account opening request {$accountOpeningRequest->request_number} started. CIF: {$accountOpeningRequest->customer->cif_number}");
    }

    public function show(AccountOpeningRequest $accountOpeningRequest): View
    {
        $this->authorizeVisibility($accountOpeningRequest);

        $accountOpeningRequest->load([
            'branch',
            'customer.customerCategory', 'customer.individual', 'customer.organization',
            'customer.nationalities.country', 'customer.addresses.country', 'customer.contacts',
            'customer.identifications.documentType', 'customer.nextOfKin.relationship',
            'customer.taxResidencies.country', 'customer.fatcaDetail.crsClassification',
            'customer.controllingPersons.controllingPersonType', 'customer.specialCategories',
            'account.product', 'account.currency', 'account.operatingInstruction',
            'account.holders.customer.individual', 'account.holders.specimenSignatures',
            'account.chequeBooks', 'account.debitCards.cardType',
            'account.dueDiligences.incomeSources', 'account.dueDiligences.wealthSources',
            'account.dueDiligences.transactionModes', 'account.dueDiligences.accountPurposes',
            'account.dueDiligences.counterPartyTypes', 'account.ultimateBeneficialOwners.relationship',
            'documents.documentType',
        ]);

        return view('account-openings.show', [
            'accountOpeningRequest' => $accountOpeningRequest,
            'progress' => $this->progress($accountOpeningRequest),
        ]);
    }

    /**
     * A print-ready copy of the completed form, laid out in the same order as
     * the printed AOF so a branch can file it alongside the paper original.
     */
    public function print(AccountOpeningRequest $accountOpeningRequest): View
    {
        $this->authorizeVisibility($accountOpeningRequest);

        $accountOpeningRequest->load([
            'branch',
            'customer.customerCategory', 'customer.economicSector',
            'customer.individual.gender', 'customer.individual.maritalStatus',
            'customer.individual.educationLevel', 'customer.individual.profession',
            'customer.individual.parentageRelationship', 'customer.individual.birthCountry',
            'customer.individual.residenceCountry',
            'customer.organization.businessNature', 'customer.organization.incorporationCountry',
            'customer.nationalities.country', 'customer.addresses.country', 'customer.contacts',
            'customer.identifications.documentType', 'customer.nextOfKin.relationship',
            'customer.nextOfKin.country', 'customer.taxResidencies.country',
            'customer.fatcaDetail.crsClassification', 'customer.fatcaDetail.incorporationCountry',
            'customer.controllingPersons.controllingPersonType', 'customer.specialCategories',
            'customer.zakatExemptionReason',
            'account.product', 'account.currency', 'account.operatingInstruction',
            'account.statementDeliveryMode', 'account.statementFrequency',
            'account.holders.customer.individual', 'account.holders.customer.organization',
            'account.holders.relationship', 'account.holders.specimenSignatures',
            'account.chequeBooks', 'account.debitCards.cardType',
            'account.dueDiligences.incomeSources', 'account.dueDiligences.wealthSources',
            'account.dueDiligences.transactionModes', 'account.dueDiligences.accountPurposes',
            'account.dueDiligences.counterPartyTypes', 'account.dueDiligences.homeRemittanceCountry',
            'account.dueDiligences.fundProviderRelationship',
            'account.ultimateBeneficialOwners.relationship', 'account.ultimateBeneficialOwners.documentType',
            'documents.documentType',
        ]);

        return view('account-openings.print', [
            'accountOpeningRequest' => $accountOpeningRequest,
        ]);
    }

    public function editStep(AccountOpeningRequest $accountOpeningRequest, string $step): View
    {
        $this->authorizeVisibility($accountOpeningRequest);
        abort_unless(array_key_exists($step, self::STEPS), 404);

        $accountOpeningRequest->load([
            'customer.individual', 'customer.organization', 'customer.nationalities',
            'customer.addresses', 'customer.contacts', 'customer.identifications',
            'customer.nextOfKin', 'customer.taxResidencies', 'customer.fatcaDetail',
            'customer.controllingPersons', 'customer.specialCategories',
            'account.holders', 'account.chequeBooks', 'account.debitCards',
            'account.dueDiligences', 'account.ultimateBeneficialOwners', 'documents',
        ]);

        return view("account-openings.steps.{$step}", [
            'accountOpeningRequest' => $accountOpeningRequest,
            'customer' => $accountOpeningRequest->customer,
            'account' => $accountOpeningRequest->account,
            'step' => $step,
            'progress' => $this->progress($accountOpeningRequest),
            ...$this->referenceOptions($accountOpeningRequest),
        ]);
    }

    public function updateStep(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest, string $step): RedirectResponse
    {
        $this->authorizeVisibility($accountOpeningRequest);
        abort_unless(array_key_exists($step, self::STEPS), 404);

        DB::transaction(function () use ($request, $accountOpeningRequest, $step): void {
            match ($step) {
                'cif' => $this->saveCif($request, $accountOpeningRequest),
                'identification' => $this->saveIdentification($request, $accountOpeningRequest),
                'compliance' => $this->saveCompliance($request, $accountOpeningRequest),
                'account' => $this->saveAccount($request, $accountOpeningRequest),
                'holders' => $this->saveHolders($request, $accountOpeningRequest),
                'cdd' => $this->saveDueDiligence($request, $accountOpeningRequest),
            };
        });

        $next = $this->nextStep($step);

        if ($request->boolean('save_and_exit') || $next === null) {
            return redirect()
                ->route('account-openings.show', $accountOpeningRequest)
                ->with('success', self::STEPS[$step].' saved successfully.');
        }

        return redirect()
            ->route('account-openings.steps.edit', [$accountOpeningRequest, $next])
            ->with('success', self::STEPS[$step].' saved successfully.');
    }

    public function submit(AccountOpeningRequest $accountOpeningRequest): RedirectResponse
    {
        $this->authorizeVisibility($accountOpeningRequest);

        $missing = collect($this->progress($accountOpeningRequest))
            ->reject(fn (array $s): bool => $s['complete'])
            ->keys();

        if ($missing->isNotEmpty()) {
            return back()->with('error', 'Complete every step before submitting: '.$missing->map(fn ($k) => self::STEPS[$k])->implode(', '));
        }

        $accountOpeningRequest->update(['status' => 'submitted']);
        $accountOpeningRequest->customer->update(['status' => 'pending_verification']);

        return redirect()
            ->route('account-openings.show', $accountOpeningRequest)
            ->with('success', 'Account opening request submitted for approval.');
    }

    public function approve(AccountOpeningRequest $accountOpeningRequest): RedirectResponse
    {
        $this->authorizeVisibility($accountOpeningRequest);

        abort_unless($accountOpeningRequest->status === 'submitted', 422);

        DB::transaction(function () use ($accountOpeningRequest): void {
            /** @var User $user */
            $user = auth()->user();

            $accountOpeningRequest->update([
                'status' => 'approved',
                'authorized_by_name' => $user->name,
                'authorized_on' => now()->toDateString(),
            ]);

            $accountOpeningRequest->customer->update([
                'status' => 'active',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $accountOpeningRequest->account?->update([
                'status' => 'active',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Account opening request approved. The account is now active.');
    }

    public function destroy(AccountOpeningRequest $accountOpeningRequest): RedirectResponse
    {
        $this->authorizeVisibility($accountOpeningRequest);

        $number = $accountOpeningRequest->request_number;
        $accountOpeningRequest->delete();

        return redirect()
            ->route('account-openings.index')
            ->with('success', "Account opening request {$number} deleted.");
    }

    /* ------------------------------------------------------------------ */
    /* Wizard plumbing */
    /* ------------------------------------------------------------------ */

    private function authorizeVisibility(AccountOpeningRequest $accountOpeningRequest): void
    {
        abort_unless(
            AccountOpeningRequest::visibleTo(auth()->user())->whereKey($accountOpeningRequest->id)->exists(),
            404
        );
    }

    /**
     * Derive wizard progress from the data itself, so no extra state column is
     * needed and the form can be completed in any order.
     *
     * @return array<string, array{label: string, complete: bool}>
     */
    private function progress(AccountOpeningRequest $accountOpeningRequest): array
    {
        $customer = $accountOpeningRequest->customer;
        $account = $accountOpeningRequest->account;

        $completed = [
            'cif' => (bool) ($customer?->individual || $customer?->organization),
            // The entity AOF has no Identification block of its own (identity
            // documents there belong to the authorized signatories), so that
            // step is complete on address and contact alone.
            'identification' => $customer?->needsIndividualProfile()
                ? ((bool) $customer->identifications()->exists() && (bool) $customer->addresses()->exists())
                : (bool) $customer?->addresses()->exists(),
            'compliance' => (bool) $customer?->fatcaDetail,
            'account' => (bool) $account,
            'holders' => (bool) $account?->holders()->exists(),
            'cdd' => (bool) $account?->dueDiligences()->exists(),
        ];

        return collect(self::STEPS)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'complete' => $completed[$key],
            ])
            ->all();
    }

    private function nextStep(string $step): ?string
    {
        $keys = array_keys(self::STEPS);
        $index = array_search($step, $keys, true);

        return $keys[$index + 1] ?? null;
    }

    /**
     * Reference lists for the wizard, already narrowed to the form type in use
     * so an Individual AOF never shows Entity-only options and vice versa.
     *
     * @return array<string, mixed>
     */
    private function referenceOptions(AccountOpeningRequest $accountOpeningRequest): array
    {
        $formType = $accountOpeningRequest->aof_form_type === AccountOpeningRequest::FORM_ENTITY ? 'entity' : 'individual';

        return [
            'formType' => $formType,
            'countries' => Country::active()->ordered()->get(),
            'currencies' => Currency::active()->ordered()->get(),
            'genders' => Gender::active()->ordered()->get(),
            'maritalStatuses' => MaritalStatus::active()->ordered()->get(),
            'educationLevels' => EducationLevel::active()->ordered()->get(),
            'professions' => Profession::active()->ordered()->get(),
            'businessNatures' => BusinessNature::active()->ordered()->get(),
            'specialCategories' => SpecialCategory::active()->ordered()->get(),
            'relationships' => Relationship::active()->ordered()->get(),
            'identificationDocumentTypes' => IdentificationDocumentType::active()->ordered()->get(),
            'zakatExemptionReasons' => ZakatExemptionReason::active()->ordered()->get(),
            'crsClassifications' => CrsEntityClassification::active()->ordered()->get(),
            'controllingPersonTypes' => ControllingPersonType::active()->ordered()->get(),
            'accountProducts' => AccountProduct::active()->availableFor($formType)->ordered()->get(),
            'operatingInstructions' => OperatingInstruction::active()->ordered()->get(),
            'statementDeliveryModes' => StatementDeliveryMode::active()->availableFor($formType)->ordered()->get(),
            'statementFrequencies' => StatementFrequency::active()->availableFor($formType)->ordered()->get(),
            'cardTypes' => CardType::active()->availableFor($formType)->ordered()->get(),
            'incomeSources' => IncomeSource::active()->forForm($formType)->ordered()->get(),
            'wealthSources' => WealthSource::active()->ordered()->get(),
            'transactionModes' => TransactionMode::active()->ordered()->get(),
            'accountPurposes' => AccountPurpose::active()->forForm($formType)->ordered()->get(),
            'counterPartyTypes' => CounterPartyType::active()->ordered()->get(),
            'documentRequirements' => DocumentRequirement::with('documentType')
                ->where('customer_category_id', $accountOpeningRequest->customer?->customer_category_id)
                ->orderBy('sort_order')
                ->get(),
            'otherCustomers' => Customer::visibleTo(auth()->user())
                ->whereKeyNot($accountOpeningRequest->customer_id)
                ->with(['individual', 'organization'])
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Step 1 -- Customer Information Form (AOF sections #03 to #06) */
    /* ------------------------------------------------------------------ */

    private function saveCif(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $customer = $accountOpeningRequest->customer;

        $customer->update($request->safe()->only([
            'customer_category_other',
            'category_code_description',
            'economic_sector_id',
        ]));

        if ($customer->needsIndividualProfile()) {
            $customer->individual()->updateOrCreate([], $request->safe()->only([
                'title', 'full_name', 'full_name_ur', 'parentage_relationship_id', 'parent_or_spouse_name',
                'mother_maiden_name', 'gender_id', 'marital_status_id', 'marital_status_other',
                'education_level_id', 'education_other', 'date_of_birth', 'birth_country_id', 'birth_city',
                'residence_country_id', 'profession_id', 'profession_other', 'designation', 'ntn',
                'guardian_name', 'guardian_relationship_id',
                'employer_institution_name', 'employer_institution_address',
                'mnp_new_provider', 'witness_name', 'witness_relation', 'witness_cnic',
            ]) + array_filter([
                'is_minor' => $request->boolean('is_minor'),
                'has_availed_mnp' => $request->boolean('has_availed_mnp'),
                'requires_witness' => $request->boolean('requires_witness'),
                'thumb_impression_taken' => $request->boolean('thumb_impression_taken'),
                // Photo Account / shaky signature images (AOF Important Notes c & e).
                'photograph_path' => $request->file('photograph')?->store("account-openings/{$customer->id}/identity", 'public'),
                'witness_signature_path' => $request->file('witness_signature')?->store("account-openings/{$customer->id}/identity", 'public'),
            ], fn ($value): bool => $value !== null));
        }

        if ($customer->needsOrganizationProfile()) {
            $organisation = $request->safe()->only([
                'business_name', 'business_name_ur', 'other_business_of_proprietor',
                'business_nature_id', 'business_nature_other',
                'business_registration_number', 'issued_date', 'expiry_date',
                'business_commencement_date', 'business_incorporation_date', 'years_in_business',
                'incorporation_country_id', 'sales_tax_registration_number',
                'chamber_membership_number', 'parent_company_name', 'group_companies',
                'main_geographic_area', 'outside_pakistan_country', 'outside_pakistan_province',
                'number_of_employees',
            ]);

            // The business NTN is a separate input so it never clashes with the
            // individual NTN on a Sole Proprietor form, where both blocks show.
            $organisation['ntn'] = $request->input('business_ntn');
            $organisation['tax_exempt_on_cash_withdrawal'] = $request->boolean('tax_exempt_on_cash_withdrawal');
            $organisation['tax_exempt_on_profit'] = $request->boolean('tax_exempt_on_profit');
            $organisation['is_dnfbp'] = $request->boolean('is_dnfbp');

            $customer->organization()->updateOrCreate([], $organisation);
        }

        $this->syncNationalities($customer, $request->input('nationalities', []));
        $this->syncSpecialCategories($customer, $request->input('special_categories', []), $request->input('special_category_values', []));
    }

    /**
     * @param  array<int, string>  $countryIds
     */
    private function syncNationalities(Customer $customer, array $countryIds): void
    {
        $customer->nationalities()->delete();

        foreach (array_values(array_filter($countryIds)) as $index => $countryId) {
            $customer->nationalities()->create([
                'country_id' => $countryId,
                'is_primary' => $index === 0,
                'sequence' => $index + 1,
            ]);
        }
    }

    /**
     * @param  array<int, string>  $categoryIds
     * @param  array<string, string|null>  $values
     */
    private function syncSpecialCategories(Customer $customer, array $categoryIds, array $values): void
    {
        $payload = [];

        foreach (array_filter($categoryIds) as $categoryId) {
            $payload[$categoryId] = ['extra_value' => $values[$categoryId] ?? null];
        }

        $customer->specialCategories()->sync($payload);
    }

    /* ------------------------------------------------------------------ */
    /* Step 2 -- Identification, addresses, contacts, zakat, next of kin */
    /* ------------------------------------------------------------------ */

    private function saveIdentification(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $customer = $accountOpeningRequest->customer;

        $customer->update([
            'is_zakat_exempt' => $request->boolean('is_zakat_exempt'),
            'zakat_exemption_reason_id' => $request->input('zakat_exemption_reason_id'),
            'zakat_exemption_other' => $request->input('zakat_exemption_other'),
            'cz50_submitted_on' => $request->input('cz50_submitted_on'),
        ]);

        $customer->identifications()->delete();
        foreach ($request->input('identifications', []) as $index => $row) {
            if (blank($row['document_number'] ?? null)) {
                continue;
            }

            $customer->identifications()->create([
                'identification_document_type_id' => $row['identification_document_type_id'],
                'document_number' => $row['document_number'],
                'issue_date' => $row['issue_date'] ?? null,
                'expiry_date' => $row['expiry_date'] ?? null,
                'place_of_issuance' => $row['place_of_issuance'] ?? null,
                'is_primary' => $index === 0,
                'is_expired_accepted' => (bool) ($row['is_expired_accepted'] ?? false),
                'nadra_token_number' => $row['nadra_token_number'] ?? null,
                'verisys_verified' => (bool) ($row['verisys_verified'] ?? false),
                'is_attested' => (bool) ($row['is_attested'] ?? false),
            ]);
        }

        foreach ($request->input('addresses', []) as $type => $row) {
            if (blank($row['city'] ?? null) && blank($row['street_area'] ?? null)) {
                continue;
            }

            $customer->addresses()->updateOrCreate(
                ['address_type' => $type],
                [
                    'house_office_no' => $row['house_office_no'] ?? null,
                    'street_area' => $row['street_area'] ?? null,
                    'tehsil_district' => $row['tehsil_district'] ?? null,
                    'nearest_landmark' => $row['nearest_landmark'] ?? null,
                    'city' => $row['city'] ?? null,
                    'country_id' => $row['country_id'] ?? null,
                    'postal_code' => $row['postal_code'] ?? null,
                    'is_primary' => in_array($type, ['permanent_residential', 'registered_business'], true),
                ]
            );
        }

        // The printed form numbers its telephone boxes (1) and (2), so each contact
        // is stored against its own sequence rather than one row per type.
        foreach ($request->input('contacts', []) as $type => $rows) {
            foreach ($rows as $sequence => $row) {
                $sequence = (int) $sequence;

                if (blank($row['value'] ?? null)) {
                    $customer->contacts()
                        ->where('contact_type', $type)
                        ->where('sequence', $sequence)
                        ->delete();

                    continue;
                }

                $customer->contacts()->updateOrCreate(
                    ['contact_type' => $type, 'sequence' => $sequence],
                    [
                        'country_code' => $row['country_code'] ?? null,
                        'value' => $row['value'],
                        'is_primary' => $sequence === 1,
                        'is_registered_for_mobile_banking' => $type === 'mobile' && $sequence === 1,
                        'is_registered_for_estatement' => $type === 'email_personal_office' && $sequence === 1,
                        'is_contact_center_registered' => $type === 'mobile' && $sequence === 1,
                    ]
                );
            }
        }

        $customer->nextOfKin()->delete();
        foreach ($request->input('next_of_kin', []) as $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }

            $customer->nextOfKin()->create([
                'name' => $row['name'],
                'relationship_id' => $row['relationship_id'] ?? null,
                'identification_document_type_id' => $row['identification_document_type_id'] ?? null,
                'identification_number' => $row['identification_number'] ?? null,
                'address' => $row['address'] ?? null,
                'tehsil_district' => $row['tehsil_district'] ?? null,
                'nearest_landmark' => $row['nearest_landmark'] ?? null,
                'city' => $row['city'] ?? null,
                'country_id' => $row['country_id'] ?? null,
                'postal_code' => $row['postal_code'] ?? null,
                'telephone' => $row['telephone'] ?? null,
                'email' => $row['email'] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Step 3 -- FATCA / CRS, tax residency, controlling persons, PEP */
    /* ------------------------------------------------------------------ */

    private function saveCompliance(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $customer = $accountOpeningRequest->customer;

        $customer->update([
            'is_pep' => $request->boolean('is_pep'),
            'pep_form_attached' => $request->boolean('pep_form_attached'),
        ]);

        $customer->fatcaDetail()->updateOrCreate([], [
            'q1_is_us_person' => $request->boolean('q1_is_us_person'),
            'q2_country_of_birth_us' => $request->boolean('q2_country_of_birth_us'),
            'q3_has_us_address_or_phone' => $request->boolean('q3_has_us_address_or_phone'),
            'q4_has_us_mandate_or_links' => $request->boolean('q4_has_us_mandate_or_links'),
            'form_w9_signed' => $request->boolean('form_w9_signed'),
            'form_w8_ben_signed' => $request->boolean('form_w8_ben_signed'),
            'us_nationality_revoked' => $request->boolean('us_nationality_revoked'),
            'entity_incorporated_in_us' => $request->boolean('entity_incorporated_in_us'),
            'incorporation_country_id' => $request->input('incorporation_country_id'),
            'nfe_type' => $request->input('nfe_type'),
            'form_w8_ben_e_signed' => $request->boolean('form_w8_ben_e_signed'),
            'crs_entity_classification_id' => $request->input('crs_entity_classification_id'),
            'giin' => $request->input('giin'),
            'listed_stock_exchange' => $request->input('listed_stock_exchange'),
            'is_tax_resident_other_country' => $request->boolean('is_tax_resident_other_country'),
            'self_certification_date' => $request->input('self_certification_date'),
        ]);

        $customer->taxResidencies()->delete();
        foreach ($request->input('tax_residencies', []) as $index => $row) {
            if (blank($row['country_id'] ?? null)) {
                continue;
            }

            $customer->taxResidencies()->create([
                'country_id' => $row['country_id'],
                'tin' => $row['tin'] ?? null,
                'no_tin_reason' => $row['no_tin_reason'] ?? null,
                'reason_b_explanation' => $row['reason_b_explanation'] ?? null,
                'sequence' => $index + 1,
            ]);
        }

        $customer->controllingPersons()->delete();
        foreach ($request->input('controlling_persons', []) as $index => $row) {
            if (blank($row['person_name'] ?? null)) {
                continue;
            }

            $customer->controllingPersons()->create([
                'declaration_basis' => $row['declaration_basis'] ?? 'fatca_10_percent',
                'sequence' => $index + 1,
                'person_name' => $row['person_name'],
                'designation' => $row['designation'] ?? null,
                'shareholding_percentage' => $row['shareholding_percentage'] ?? null,
                'controlling_person_type_id' => $row['controlling_person_type_id'] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Step 4 -- Account, products and services (AOF #16 to #25) */
    /* ------------------------------------------------------------------ */

    private function saveAccount(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $product = AccountProduct::findOrFail($request->input('account_product_id'));
        $currency = Currency::findOrFail($request->input('currency_id'));
        $branch = $accountOpeningRequest->branch;

        $account = $accountOpeningRequest->account;

        $payload = [
            'title_of_account' => $request->input('title_of_account'),
            'branch_id' => $accountOpeningRequest->branch_id,
            'profit_center' => $request->input('profit_center'),
            'opening_date' => $request->input('opening_date'),
            'account_product_id' => $product->id,
            'product_other' => $request->input('product_other'),
            'currency_id' => $currency->id,
            'currency_other' => $request->input('currency_other'),
            'account_class' => $product->product_class,
            'is_foreign_currency' => ! $currency->is_local,
            'operating_instruction_id' => $request->input('operating_instruction_id'),
            'operating_instruction_other' => $request->input('operating_instruction_other'),
            'special_instructions' => $request->input('special_instructions'),
            'sms_alerts_subscribed' => $request->boolean('sms_alerts_subscribed'),
            'digital_channels_opted' => $request->boolean('digital_channels_opted'),
            'digital_channels_biometric_verified' => $request->boolean('digital_channels_biometric_verified'),
            'statement_delivery_mode_id' => $request->input('statement_delivery_mode_id'),
            'statement_frequency_id' => $request->input('statement_frequency_id'),
            'initial_deposit' => $request->input('initial_deposit'),
            'zakat_applicable' => $product->is_zakat_applicable && ! $accountOpeningRequest->customer->is_zakat_exempt,
            'aof_version' => $accountOpeningRequest->aof_version,
        ];

        if ($account) {
            $account->update($payload);
        } else {
            $account = Account::create($payload + [
                'account_number' => generateUniqueIdWithPrefix($branch->code, 'accounts', 'account_number'),
                'iban' => $this->buildIban($branch->code),
                'status' => 'pending',
            ]);

            $accountOpeningRequest->update(['account_id' => $account->id]);
        }

        // The mailing address points at the customer address flagged as "mailing".
        $mailingAddress = $accountOpeningRequest->customer->addresses()->where('address_type', 'mailing')->first();
        $account->update(['mailing_address_id' => $mailingAddress?->id]);

        $account->chequeBooks()->delete();
        if ($request->boolean('cheque_book_required')) {
            $account->chequeBooks()->create([
                'is_required' => true,
                'quantity_required' => $request->input('cheque_book_quantity'),
                'leaves_count' => $request->input('cheque_book_leaves'),
                'leaves_other' => $request->input('cheque_book_leaves_other'),
                'requested_on' => $request->input('opening_date'),
            ]);
        }

        $account->debitCards()->delete();
        if ($request->filled('card_type_id') && $request->filled('name_on_card')) {
            $account->debitCards()->create([
                'card_type_id' => $request->input('card_type_id'),
                'name_on_card' => $request->input('name_on_card'),
                'requested_on' => $request->input('opening_date'),
            ]);
        }

        $accountOpeningRequest->update([
            'terms_and_conditions_accepted' => $request->boolean('terms_and_conditions_accepted'),
            'indemnity_undertaking_accepted' => $request->boolean('indemnity_undertaking_accepted'),
            'aof_copy_received_by_customer' => $request->boolean('aof_copy_received_by_customer'),
            'shariah_compliant_investment_authorized' => $request->boolean('shariah_compliant_investment_authorized'),
        ]);

        if ($request->boolean('terms_and_conditions_accepted') && $account->terms_accepted_at === null) {
            $account->update(['terms_accepted_at' => now()]);
        }
    }

    /**
     * BAJK IBAN skeleton: PK## BAJK + branch code + account serial.
     */
    private function buildIban(string $branchCode): string
    {
        $serial = str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT);

        return 'PK00BAJK'.str_pad(substr($branchCode, 0, 5), 5, '0', STR_PAD_LEFT).$serial;
    }

    /* ------------------------------------------------------------------ */
    /* Step 5 -- Applicants / signatories and specimen signatures */
    /* ------------------------------------------------------------------ */

    private function saveHolders(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $account = $accountOpeningRequest->account;
        abort_if($account === null, 422, 'Create the account before adding applicants.');

        $keptHolderIds = [];

        foreach ($request->input('holders', []) as $index => $row) {
            if (blank($row['customer_id'] ?? null)) {
                continue;
            }

            $holder = $account->holders()->updateOrCreate(
                [
                    'customer_id' => $row['customer_id'],
                    'holder_role' => $row['holder_role'],
                ],
                [
                    'applicant_number' => $index + 1,
                    'is_signatory' => (bool) ($row['is_signatory'] ?? false),
                    'signing_order' => $row['signing_order'] ?? null,
                    'relationship_id' => $row['relationship_id'] ?? null,
                    'designation' => $row['designation'] ?? null,
                    'is_active' => true,
                ]
            );

            $keptHolderIds[] = $holder->id;

            $signature = $request->file("holders.{$index}.signature_image");
            $thumb = $request->file("holders.{$index}.thumb_image");
            $stamp = $request->file("holders.{$index}.stamp_image");

            if ($signature || $thumb || $stamp) {
                $holder->specimenSignatures()->update(['is_active' => false]);

                $holder->specimenSignatures()->create([
                    'ss_card_number' => $row['ss_card_number'] ?? null,
                    'specimen_number' => $holder->specimenSignatures()->count() + 1,
                    'specimen_type' => $signature ? 'signature' : 'thumb_impression',
                    'signature_image_path' => $signature?->store("account-openings/{$account->id}/signatures", 'public'),
                    'thumb_left_image_path' => $thumb?->store("account-openings/{$account->id}/signatures", 'public'),
                    'organization_stamp_path' => $stamp?->store("account-openings/{$account->id}/signatures", 'public'),
                    'is_active' => true,
                    'effective_from' => now()->toDateString(),
                ]);
            }
        }

        $account->holders()->whereNotIn('id', $keptHolderIds)->delete();
    }

    /* ------------------------------------------------------------------ */
    /* Step 6 -- Customer Due Diligence, UBO and documentation checklist */
    /* ------------------------------------------------------------------ */

    private function saveDueDiligence(UpdateAccountOpeningStepRequest $request, AccountOpeningRequest $accountOpeningRequest): void
    {
        $account = $accountOpeningRequest->account;
        abort_if($account === null, 422, 'Create the account before recording due diligence.');

        $customer = $accountOpeningRequest->customer;

        $cddType = match ($customer->customer_type) {
            Customer::TYPE_INDIVIDUAL => CustomerDueDiligence::TYPE_INDIVIDUAL,
            Customer::TYPE_SOLE_PROPRIETOR => CustomerDueDiligence::TYPE_SOLE_PROPRIETOR,
            default => CustomerDueDiligence::TYPE_BUSINESS,
        };

        /** @var CustomerDueDiligence $dueDiligence */
        $dueDiligence = $account->dueDiligences()->updateOrCreate(
            ['customer_id' => $customer->id, 'cdd_type' => $cddType],
            $request->safe()->only([
                'customer_source', 'referred_by', 'employer_name', 'employer_designation', 'employer_address',
                'fund_provider_employer', 'fund_provider_id_number', 'fund_provider_relationship_id',
                'business_name', 'business_nature_text', 'business_address', 'type_of_channels',
                'type_of_counterparties', 'geographies_involved', 'nature_of_work',
                'home_remittance_country_id', 'home_remittance_relationship',
                'monthly_income', 'monthly_net_income', 'number_of_employees',
                'source_of_income_other', 'source_of_wealth_other', 'purpose_of_account_other',
                'credit_mode_other', 'debit_mode_other', 'counter_party_other',
                'initial_deposit', 'expected_monthly_credit_amount', 'expected_monthly_credit_count',
                'expected_monthly_debit_amount', 'expected_monthly_debit_count',
                'expected_iftt_amount', 'expected_oftt_amount', 'currency_symbol',
            ]) + [
                'physical_verification_conducted' => $request->boolean('physical_verification_conducted'),
                'proscribed_list_cleared' => $request->boolean('proscribed_list_cleared'),
                'is_dnfbp' => $request->boolean('is_dnfbp'),
            ]
        );

        $dueDiligence->incomeSources()->sync($request->input('income_sources', []));
        $dueDiligence->wealthSources()->sync($request->input('wealth_sources', []));
        $dueDiligence->accountPurposes()->sync($request->input('account_purposes', []));
        $dueDiligence->counterPartyTypes()->sync($request->input('counter_party_types', []));

        // Credit and debit use the same option list, so the pivot carries a direction.
        $dueDiligence->transactionModes()->detach();
        foreach (['credit', 'debit'] as $direction) {
            foreach ($request->input("{$direction}_modes", []) as $modeId) {
                $dueDiligence->transactionModes()->attach($modeId, ['direction' => $direction]);
            }
        }

        $account->ultimateBeneficialOwners()->delete();
        foreach ($request->input('ubos', []) as $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }

            $account->ultimateBeneficialOwners()->create([
                'customer_due_diligence_id' => $dueDiligence->id,
                'name' => $row['name'],
                'relationship_id' => $row['relationship_id'] ?? null,
                'identification_document_type_id' => $row['identification_document_type_id'] ?? null,
                'identification_number' => $row['identification_number'] ?? null,
                'declaration_form_received' => (bool) ($row['declaration_form_received'] ?? false),
            ]);
        }

        /*
        | Drop checklist placeholders that the current customer category no longer
        | requires. Anything already received (a file, or a status other than
        | pending) is captured evidence and is kept.
        */
        $requiredTypeIds = array_keys($request->input('documents', []));

        $accountOpeningRequest->documents()
            ->whereNotIn('document_type_id', $requiredTypeIds ?: ['-'])
            ->whereNull('file_path')
            ->where('status', 'pending')
            ->delete();

        foreach ($request->input('documents', []) as $documentTypeId => $row) {
            $file = $request->file("documents.{$documentTypeId}.file");

            $accountOpeningRequest->documents()->updateOrCreate(
                ['document_type_id' => $documentTypeId],
                array_filter([
                    'customer_id' => $customer->id,
                    'account_id' => $account->id,
                    'status' => $row['status'] ?? 'pending',
                    'is_attested' => (bool) ($row['is_attested'] ?? false),
                    'remarks' => $row['remarks'] ?? null,
                    'file_path' => $file?->store("account-openings/{$accountOpeningRequest->id}/documents", 'public'),
                    'file_name' => $file?->getClientOriginalName(),
                ], fn ($value): bool => $value !== null)
            );
        }

        $accountOpeningRequest->update($request->safe()->only([
            'sales_staff_name_1', 'sales_staff_employee_no_1',
            'sales_staff_name_2', 'sales_staff_employee_no_2',
        ]));
    }
}
