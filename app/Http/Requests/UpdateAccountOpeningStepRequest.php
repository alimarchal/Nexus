<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Country;
use App\Support\MobileNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for one wizard step of the Account Opening Form.
 *
 * The step is taken from the route, so a single request class keeps all six
 * rule sets together and they stay readable next to each other.
 */
class UpdateAccountOpeningStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit account openings') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ((string) $this->route('step')) {
            'cif' => $this->cifRules(),
            'identification' => $this->identificationRules(),
            'compliance' => $this->complianceRules(),
            'account' => $this->accountRules(),
            'holders' => $this->holderRules(),
            'cdd' => $this->dueDiligenceRules(),
            default => [],
        };
    }

    /**
     * Plain-language messages for the fields staff most often miss, so the form
     * never shows a raw rule key such as "contacts.mobile.1.value".
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contacts.mobile.1.value.required' => 'Please enter at least one contact number to continue.',
            'contacts.mobile.1.country_code.required' => 'Please enter the country code for the mobile number, for example +92.',
            'initial_deposit.min' => 'The initial deposit must be at least '.number_format((float) config('account_opening.minimum_initial_deposit'), 2).'.',
            'initial_deposit.decimal' => 'The initial deposit can have at most 2 decimal places.',
            'initial_deposit.numeric' => 'Enter the initial deposit as an amount, for example '.number_format((float) config('account_opening.minimum_initial_deposit'), 2, '.', '').'.',
            'terms_and_conditions_accepted.accepted' => 'Please review and accept the Terms and Conditions before continuing.',
            'identifications.*.identification_document_type_id.required_with' => 'Please select the document type for the identification number you entered.',
        ];
    }

    /**
     * The mobile number must suit its country code: exactly 10 digits for +92,
     * digits only and within the international limit for any other code.
     */
    public function withValidator(Validator $validator): void
    {
        if ((string) $this->route('step') === 'compliance') {
            $this->validateTaxResidency($validator);

            return;
        }

        if ((string) $this->route('step') === 'holders') {
            $this->validateSignatoryImages($validator);

            return;
        }

        if ((string) $this->route('step') !== 'identification') {
            return;
        }

        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('contacts.mobile.1.value')) {
                return;
            }

            $problem = MobileNumber::problem(
                $this->input('contacts.mobile.1.country_code'),
                $this->input('contacts.mobile.1.value'),
            );

            if ($problem !== null) {
                $validator->errors()->add('contacts.mobile.1.value', $problem);
            }
        });
    }

    /**
     * Every authorised signatory needs a specimen signature and a thumb
     * impression before the wizard moves on. An image saved earlier counts, so a
     * later edit does not have to upload it again. "Save & exit" keeps a draft.
     */
    private function validateSignatoryImages(Validator $validator): void
    {
        if ($this->boolean('save_and_exit')) {
            return;
        }

        $validator->after(function (Validator $validator): void {
            $account = $this->route('accountOpeningRequest')?->account;

            foreach ((array) $this->input('holders', []) as $index => $row) {
                if (blank($row['customer_id'] ?? null) || ! ($row['is_signatory'] ?? false)) {
                    continue;
                }

                $saved = $account?->holders()
                    ->where('customer_id', $row['customer_id'])
                    ->where('holder_role', $row['holder_role'] ?? null)
                    ->first()
                    ?->specimenSignatures()
                    ->where('is_active', true)
                    ->latest('effective_from')
                    ->first();

                $number = (int) $index + 1;

                if (! $this->hasFile("holders.{$index}.signature_image") && blank($saved?->signature_image_path) && ! $validator->errors()->has("holders.{$index}.signature_image")) {
                    $validator->errors()->add("holders.{$index}.signature_image", "Please upload the specimen signature for applicant {$number} before continuing.");
                }

                if (! $this->hasFile("holders.{$index}.thumb_image") && blank($saved?->thumb_left_image_path) && ! $validator->errors()->has("holders.{$index}.thumb_image")) {
                    $validator->errors()->add("holders.{$index}.thumb_image", "Please upload the thumb impression for applicant {$number} before continuing.");
                }
            }
        });
    }

    /**
     * Tax residency details apply only to a country other than Pakistan and the
     * USA: when the box is ticked at least one such country is needed.
     */
    private function validateTaxResidency(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->boolean('is_tax_resident_other_country')) {
                return;
            }

            $countryIds = collect($this->input('tax_residencies', []))
                ->pluck('country_id')
                ->filter()
                ->values();

            if ($countryIds->isEmpty()) {
                $validator->errors()->add('tax_residencies', 'Select the country of tax residence, or untick "Tax resident of a country other than Pakistan & USA".');

                return;
            }

            $excluded = Country::query()->whereIn('iso2', ['PK', 'US'])->whereIn('id', $countryIds)->exists();

            if ($excluded) {
                $validator->errors()->add('tax_residencies', 'Pakistan and the USA are covered elsewhere on this form; choose a different country of tax residence.');
            }
        });
    }

    /**
     * Initial deposit: optional, but when entered it must reach the configured
     * minimum and carry at most two decimals.
     *
     * @return array<int, string>
     */
    private function initialDepositRules(): array
    {
        return ['nullable', 'numeric', 'decimal:0,2', 'min:'.config('account_opening.minimum_initial_deposit')];
    }

    /**
     * AOF #04 to #06 -- Customer Category plus the Individual / Business detail blocks.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function cifRules(): array
    {
        return [
            'customer_category_other' => ['nullable', 'string', 'max:150'],
            'category_code_description' => ['nullable', 'string', 'max:200'],
            'economic_sector_id' => ['nullable', 'uuid', 'exists:economic_sectors,id'],

            // Customer Details For Individuals
            'title' => ['nullable', 'string', 'max:10'],
            'full_name' => ['nullable', 'required_without:business_name', 'string', 'max:150'],
            'full_name_ur' => ['nullable', 'string', 'max:150'],
            'parentage_relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'parent_or_spouse_name' => ['nullable', 'string', 'max:150'],
            'mother_maiden_name' => ['nullable', 'string', 'max:150'],
            'gender_id' => ['nullable', 'uuid', 'exists:genders,id'],
            'marital_status_id' => ['nullable', 'uuid', 'exists:marital_statuses,id'],
            'marital_status_other' => ['nullable', 'string', 'max:80'],
            'education_level_id' => ['nullable', 'uuid', 'exists:education_levels,id'],
            'education_other' => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'required_with:full_name', 'date', 'before:today'],
            'birth_country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'birth_city' => ['nullable', 'string', 'max:100'],
            'residence_country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'profession_id' => ['nullable', 'uuid', 'exists:professions,id'],
            'profession_other' => ['nullable', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:120'],
            'ntn' => ['nullable', 'string', 'max:30'],
            'is_minor' => ['nullable', 'boolean'],
            'guardian_name' => ['nullable', 'required_if:is_minor,1', 'string', 'max:150'],
            'guardian_relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'employer_institution_name' => ['nullable', 'string', 'max:200'],
            'employer_institution_address' => ['nullable', 'string', 'max:500'],
            'has_availed_mnp' => ['nullable', 'boolean'],
            'mnp_new_provider' => ['nullable', 'string', 'max:100'],
            'requires_witness' => ['nullable', 'boolean'],
            'witness_name' => ['nullable', 'required_if:requires_witness,1', 'string', 'max:150'],
            'witness_relation' => ['nullable', 'string', 'max:100'],
            'witness_cnic' => ['nullable', 'string', 'max:25'],

            // Customer Details For Business
            'business_name' => ['nullable', 'required_without:full_name', 'string', 'max:200'],
            'business_name_ur' => ['nullable', 'string', 'max:200'],
            'other_business_of_proprietor' => ['nullable', 'string', 'max:200'],
            'business_nature_id' => ['nullable', 'uuid', 'exists:business_natures,id'],
            'business_nature_other' => ['nullable', 'string', 'max:150'],
            'business_registration_number' => ['nullable', 'string', 'max:60'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'business_commencement_date' => ['nullable', 'date'],
            'business_incorporation_date' => ['nullable', 'date'],
            'years_in_business' => ['nullable', 'integer', 'min:0', 'max:200'],
            'incorporation_country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'business_ntn' => ['nullable', 'string', 'max:30'],
            'sales_tax_registration_number' => ['nullable', 'string', 'max:40'],
            'chamber_membership_number' => ['nullable', 'string', 'max:60'],
            'tax_exempt_on_cash_withdrawal' => ['nullable', 'boolean'],
            'tax_exempt_on_profit' => ['nullable', 'boolean'],
            'parent_company_name' => ['nullable', 'string', 'max:200'],
            'group_companies' => ['nullable', 'string', 'max:1000'],
            'main_geographic_area' => ['nullable', 'string', 'max:200'],
            'outside_pakistan_country' => ['nullable', 'string', 'max:100'],
            'outside_pakistan_province' => ['nullable', 'string', 'max:100'],
            'number_of_employees' => ['nullable', 'integer', 'min:0'],
            'is_dnfbp' => ['nullable', 'boolean'],

            'photograph' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'thumb_impression_taken' => ['nullable', 'boolean'],
            'witness_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],

            'nationalities' => ['nullable', 'array', 'max:3'],
            'nationalities.*' => ['nullable', 'uuid', 'exists:countries,id'],
            'special_categories' => ['nullable', 'array'],
            'special_categories.*' => ['uuid', 'exists:special_categories,id'],
            'special_category_values' => ['nullable', 'array'],
            'special_category_values.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * AOF #07 to #11 -- Identification, addresses, contacts, zakat, next of kin.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function identificationRules(): array
    {
        $customer = $this->route('accountOpeningRequest')?->customer;
        $needsIdentification = $customer?->needsIndividualProfile() ?? true;

        return [
            'identifications' => [$needsIdentification ? 'required' : 'nullable', 'array'],
            'identifications.*.identification_document_type_id' => ['required_with:identifications.*.document_number', 'nullable', 'uuid', 'exists:identification_document_types,id'],
            'identifications.*.document_number' => ['nullable', 'string', 'max:50'],
            'identifications.*.issue_date' => ['nullable', 'date'],
            'identifications.*.expiry_date' => ['nullable', 'date'],
            'identifications.*.place_of_issuance' => ['nullable', 'string', 'max:120'],
            'identifications.*.nadra_token_number' => ['nullable', 'string', 'max:60'],
            'identifications.*.is_expired_accepted' => ['nullable', 'boolean'],
            'identifications.*.verisys_verified' => ['nullable', 'boolean'],
            'identifications.*.is_attested' => ['nullable', 'boolean'],

            'addresses' => ['required', 'array'],
            'addresses.*.house_office_no' => ['nullable', 'string', 'max:100'],
            'addresses.*.street_area' => ['nullable', 'string', 'max:150'],
            'addresses.*.tehsil_district' => ['nullable', 'string', 'max:120'],
            'addresses.*.nearest_landmark' => ['nullable', 'string', 'max:150'],
            'addresses.*.city' => ['nullable', 'string', 'max:100'],
            'addresses.*.country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:20'],

            'contacts' => ['nullable', 'array'],
            'contacts.mobile.1.value' => ['required', 'string', 'max:30'],
            'contacts.mobile.1.country_code' => ['required', 'string', 'max:8'],
            'contacts.email_personal_office.1.value' => ['nullable', 'email', 'max:150'],
            'contacts.*.*.value' => ['nullable', 'string', 'max:150'],
            'contacts.*.*.country_code' => ['nullable', 'string', 'max:8'],

            'is_zakat_exempt' => ['nullable', 'boolean'],
            'zakat_exemption_reason_id' => ['nullable', 'required_if:is_zakat_exempt,1', 'uuid', 'exists:zakat_exemption_reasons,id'],
            'zakat_exemption_other' => ['nullable', 'string', 'max:150'],
            'cz50_submitted_on' => ['nullable', 'date'],

            'next_of_kin' => ['nullable', 'array'],
            'next_of_kin.*.name' => ['nullable', 'string', 'max:150'],
            'next_of_kin.*.relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'next_of_kin.*.identification_document_type_id' => ['nullable', 'uuid', 'exists:identification_document_types,id'],
            'next_of_kin.*.identification_number' => ['nullable', 'string', 'max:50'],
            'next_of_kin.*.address' => ['nullable', 'string', 'max:255'],
            'next_of_kin.*.tehsil_district' => ['nullable', 'string', 'max:120'],
            'next_of_kin.*.nearest_landmark' => ['nullable', 'string', 'max:150'],
            'next_of_kin.*.city' => ['nullable', 'string', 'max:100'],
            'next_of_kin.*.country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'next_of_kin.*.postal_code' => ['nullable', 'string', 'max:20'],
            'next_of_kin.*.telephone' => ['nullable', 'string', 'max:30'],
            'next_of_kin.*.email' => ['nullable', 'email', 'max:150'],
        ];
    }

    /**
     * AOF #12 to #15 -- FATCA, CRS classification, tax residency and PEP.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function complianceRules(): array
    {
        return [
            'q1_is_us_person' => ['nullable', 'boolean'],
            'q2_country_of_birth_us' => ['nullable', 'boolean'],
            'q3_has_us_address_or_phone' => ['nullable', 'boolean'],
            'q4_has_us_mandate_or_links' => ['nullable', 'boolean'],
            'form_w9_signed' => ['nullable', 'boolean'],
            'form_w8_ben_signed' => ['nullable', 'boolean'],
            'us_nationality_revoked' => ['nullable', 'boolean'],

            'entity_incorporated_in_us' => ['nullable', 'boolean'],
            'incorporation_country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'nfe_type' => ['nullable', Rule::in(['active', 'passive'])],
            'form_w8_ben_e_signed' => ['nullable', 'boolean'],
            'crs_entity_classification_id' => ['nullable', 'uuid', 'exists:crs_entity_classifications,id'],
            'giin' => ['nullable', 'string', 'max:30'],
            'listed_stock_exchange' => ['nullable', 'string', 'max:150'],

            'is_tax_resident_other_country' => ['nullable', 'boolean'],
            'self_certification_date' => ['nullable', 'date'],

            'tax_residencies' => ['nullable', 'array'],
            'tax_residencies.*.country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'tax_residencies.*.tin' => ['nullable', 'string', 'max:50'],
            'tax_residencies.*.no_tin_reason' => ['nullable', Rule::in(['A', 'B', 'C'])],
            'tax_residencies.*.reason_b_explanation' => ['nullable', 'required_if:tax_residencies.*.no_tin_reason,B', 'string', 'max:1000'],

            'controlling_persons' => ['nullable', 'array', 'max:20'],
            'controlling_persons.*.person_name' => ['nullable', 'string', 'max:150'],
            'controlling_persons.*.designation' => ['nullable', 'string', 'max:120'],
            'controlling_persons.*.shareholding_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'controlling_persons.*.controlling_person_type_id' => ['nullable', 'uuid', 'exists:controlling_person_types,id'],
            'controlling_persons.*.declaration_basis' => ['nullable', Rule::in(['fatca_10_percent', 'crs_20_percent', 'both'])],

            'is_pep' => ['nullable', 'boolean'],
            'pep_form_attached' => ['nullable', 'boolean'],
        ];
    }

    /**
     * AOF #16 to #26 -- Products, operating instructions, services, cheque book, card.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function accountRules(): array
    {
        return [
            'title_of_account' => ['required', 'string', 'max:200'],
            'opening_date' => ['required', 'date'],
            'profit_center' => ['nullable', 'string', 'max:20'],
            'account_product_id' => ['required', 'uuid', 'exists:account_products,id'],
            'product_other' => ['nullable', 'string', 'max:150'],
            'currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'currency_other' => ['nullable', 'string', 'max:60'],
            'operating_instruction_id' => ['required', 'uuid', 'exists:operating_instructions,id'],
            'operating_instruction_other' => ['nullable', 'string', 'max:150'],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
            'sms_alerts_subscribed' => ['nullable', 'boolean'],
            'digital_channels_opted' => ['nullable', 'boolean'],
            'digital_channels_biometric_verified' => ['nullable', 'boolean'],
            'statement_delivery_mode_id' => ['nullable', 'uuid', 'exists:statement_delivery_modes,id'],
            'statement_frequency_id' => ['nullable', 'uuid', 'exists:statement_frequencies,id'],
            'initial_deposit' => $this->initialDepositRules(),

            'cheque_book_required' => ['nullable', 'boolean'],
            'cheque_book_quantity' => ['nullable', 'required_if:cheque_book_required,1', 'integer', 'min:1', 'max:100'],
            'cheque_book_leaves' => ['nullable', 'required_if:cheque_book_required,1', 'integer', Rule::in([10, 25, 50, 100])],
            'cheque_book_leaves_other' => ['nullable', 'string', 'max:60'],

            'card_type_id' => ['nullable', 'uuid', 'exists:card_types,id'],
            'name_on_card' => ['nullable', 'required_with:card_type_id', 'string', 'max:19'],

            // Terms must be accepted to move on; "Save & exit" may still keep a draft.
            'terms_and_conditions_accepted' => $this->boolean('save_and_exit') ? ['nullable', 'boolean'] : ['accepted'],
            'indemnity_undertaking_accepted' => ['nullable', 'boolean'],
            'aof_copy_received_by_customer' => ['nullable', 'boolean'],
            'shariah_compliant_investment_authorized' => ['nullable', 'boolean'],
        ];
    }

    /**
     * AOF #02 and #30 -- Applicants, roles and specimen signatures.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function holderRules(): array
    {
        return [
            'holders' => ['required', 'array', 'min:1'],
            'holders.*.customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'holders.*.holder_role' => ['required_with:holders.*.customer_id', 'nullable', Rule::in([
                'primary', 'joint', 'sole_proprietor', 'minor', 'guardian', 'authorized_signatory',
                'mandate_holder', 'agent', 'executor', 'trustee', 'office_bearer',
            ])],
            'holders.*.is_signatory' => ['nullable', 'boolean'],
            'holders.*.signing_order' => ['nullable', 'integer', 'min:1', 'max:20'],
            'holders.*.relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'holders.*.designation' => ['nullable', 'string', 'max:120'],
            'holders.*.ss_card_number' => ['nullable', 'string', 'max:40'],
            'holders.*.signature_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'holders.*.thumb_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'holders.*.stamp_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * AOF #31 to #34 -- Due diligence, UBO, documentation checklist, recommendation.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function dueDiligenceRules(): array
    {
        return [
            'customer_source' => ['nullable', Rule::in(['walk_in', 'marketed', 'referred'])],
            'referred_by' => ['nullable', 'required_if:customer_source,referred', 'string', 'max:150'],
            'physical_verification_conducted' => ['nullable', 'boolean'],
            'proscribed_list_cleared' => ['nullable', 'boolean'],

            'income_sources' => ['required', 'array', 'min:1'],
            'income_sources.*' => ['uuid', 'exists:income_sources,id'],
            'source_of_income_other' => ['nullable', 'string', 'max:200'],
            'wealth_sources' => ['nullable', 'array'],
            'wealth_sources.*' => ['uuid', 'exists:wealth_sources,id'],
            'source_of_wealth_other' => ['nullable', 'string', 'max:200'],

            'employer_name' => ['nullable', 'string', 'max:200'],
            'employer_designation' => ['nullable', 'string', 'max:120'],
            'employer_address' => ['nullable', 'string', 'max:500'],
            'fund_provider_employer' => ['nullable', 'string', 'max:200'],
            'fund_provider_id_number' => ['nullable', 'string', 'max:50'],
            'fund_provider_relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'is_dnfbp' => ['nullable', 'boolean'],
            'business_name' => ['nullable', 'string', 'max:200'],
            'business_nature_text' => ['nullable', 'string', 'max:200'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'type_of_channels' => ['nullable', 'string', 'max:200'],
            'type_of_counterparties' => ['nullable', 'string', 'max:200'],
            'geographies_involved' => ['nullable', 'string', 'max:200'],
            'nature_of_work' => ['nullable', 'string', 'max:200'],
            'home_remittance_country_id' => ['nullable', 'uuid', 'exists:countries,id'],
            'home_remittance_relationship' => ['nullable', 'string', 'max:120'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_net_income' => ['nullable', 'numeric', 'min:0'],
            'number_of_employees' => ['nullable', 'integer', 'min:0'],

            'credit_modes' => ['nullable', 'array'],
            'credit_modes.*' => ['uuid', 'exists:transaction_modes,id'],
            'debit_modes' => ['nullable', 'array'],
            'debit_modes.*' => ['uuid', 'exists:transaction_modes,id'],
            'credit_mode_other' => ['nullable', 'string', 'max:120'],
            'debit_mode_other' => ['nullable', 'string', 'max:120'],

            'account_purposes' => ['required', 'array', 'min:1'],
            'account_purposes.*' => ['uuid', 'exists:account_purposes,id'],
            'purpose_of_account_other' => ['nullable', 'string', 'max:200'],
            'counter_party_types' => ['nullable', 'array'],
            'counter_party_types.*' => ['uuid', 'exists:counter_party_types,id'],
            'counter_party_other' => ['nullable', 'string', 'max:120'],

            'initial_deposit' => $this->initialDepositRules(),
            'expected_monthly_credit_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_monthly_credit_count' => ['nullable', 'integer', 'min:0'],
            'expected_monthly_debit_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_monthly_debit_count' => ['nullable', 'integer', 'min:0'],
            'expected_iftt_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_oftt_amount' => ['nullable', 'numeric', 'min:0'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],

            'ubos' => ['nullable', 'array'],
            'ubos.*.name' => ['nullable', 'string', 'max:150'],
            'ubos.*.relationship_id' => ['nullable', 'uuid', 'exists:relationships,id'],
            'ubos.*.identification_document_type_id' => ['nullable', 'uuid', 'exists:identification_document_types,id'],
            'ubos.*.identification_number' => ['nullable', 'string', 'max:50'],
            'ubos.*.declaration_form_received' => ['nullable', 'boolean'],

            'documents' => ['nullable', 'array'],
            'documents.*.status' => ['nullable', Rule::in(['pending', 'received', 'verified', 'waived', 'rejected'])],
            'documents.*.is_attested' => ['nullable', 'boolean'],
            'documents.*.remarks' => ['nullable', 'string', 'max:500'],
            'documents.*.file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],

            'sales_staff_name_1' => ['nullable', 'string', 'max:150'],
            'sales_staff_employee_no_1' => ['nullable', 'string', 'max:30'],
            'sales_staff_name_2' => ['nullable', 'string', 'max:150'],
            'sales_staff_employee_no_2' => ['nullable', 'string', 'max:30'],
        ];
    }
}
