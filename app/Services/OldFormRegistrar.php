<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AccountOpeningWorkflowException;
use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerIdentification;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\MaritalStatus;
use App\Models\OperatingInstruction;
use App\Models\User;

/**
 * Turns an approved old (paper form) request into the same records the new form keeps: the customer (CIF) of each
 * applicant and the account with its holders, so the customer can be found by CNIC, CIF or account number and chosen as
 * a joint holder or signatory on a new form. A customer with the same CNIC (or, for a business, the same NTN) is linked,
 * never duplicated, and approving the same request again updates what it created instead of adding more.
 */
final class OldFormRegistrar
{
    private const PRODUCT_BY_PKR_TYPE = ['current' => 'BCA', 'pls_saving' => 'PLS', 'sd' => 'SDA', 'bmba' => 'BMBA', 'other' => 'BCA'];

    private const PRODUCT_BY_FCY_TYPE = ['current' => 'BCA_FCY', 'saving' => 'BSA_FCY', 'term' => 'TERM_FCY', 'bmba' => 'BMBA_FCY', 'other' => 'BCA_FCY'];

    private const CATEGORY_BY_BUSINESS_KIND = [
        'partnership' => 'PARTNERSHIP', 'joint_stock' => 'LIMITED_CO', 'govt_institution' => 'GOVT_INSTITUTION',
        'sole_proprietorship' => 'SOLE_PROP', 'foreign_mission' => 'FOREIGN_MISSION', 'association' => 'ASSOCIATION', 'other' => 'ENT_OTHERS',
    ];

    private const OPERATING_INSTRUCTION = [
        'singly' => 'SINGLY', 'either_or_survivor' => 'EITHER_OR_SURVIVOR', 'jointly' => 'JOINTLY', 'mandate' => 'MANDATE', 'other' => 'OI_OTHER',
    ];

    /**
     * @throws AccountOpeningWorkflowException when the paper form lacks what the customer or the account needs
     */
    public function register(AccountOpeningRequest $request, User $user): void
    {
        $values = $request->paperForm?->values() ?? [];
        $nature = $values['account_nature'] ?? null;

        if (! in_array($nature, ['individual', 'business'], true)) {
            throw new AccountOpeningWorkflowException('The old form has no Nature of Account, so the customer and the account cannot be created from it.');
        }

        $holders = $nature === 'business'
            ? [$this->businessCustomer($request, $values, $user)]
            : $this->individualCustomers($request, $values, $user);

        $primary = $holders[0];
        $account = $this->account($request, $values, $primary, $user);

        foreach ($holders as $index => $customer) {
            $account->holders()->updateOrCreate(['customer_id' => $customer->id], [
                'holder_role' => $this->holderRole($nature, $values, $index),
                'applicant_number' => $index + 1,
                'is_signatory' => true,
                'signing_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $request->update(['customer_id' => $primary->id, 'account_id' => $account->id]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return list<Customer>
     */
    private function individualCustomers(AccountOpeningRequest $request, array $values, User $user): array
    {
        $applicants = ($values['individual_kind'] ?? null) === 'joint' ? [1, 2] : [1];
        $already = $request->account?->holders()->get()->keyBy('applicant_number') ?? collect();
        $customers = [];

        foreach ($applicants as $n) {
            $name = trim((string) ($values["a{$n}_name"] ?? ''));

            if ($name === '') {
                throw new AccountOpeningWorkflowException("The old form has no name for applicant {$n}, so the customer cannot be created from it.");
            }

            $earlier = $already->get($n);

            if ($earlier && $earlier->customer) {
                $customers[] = $earlier->customer;

                continue;
            }

            $number = trim((string) ($values["a{$n}_id_number"] ?? ''));
            $type = ['cnic' => 'CNIC', 'passport' => 'PASSPORT', 'alien_card' => 'ARC'][$values["a{$n}_id_type"] ?? 'cnic'] ?? 'CNIC';
            $linked = $number !== '' ? $this->customerByIdentification($number) : null;

            if ($linked) {
                $customers[] = $linked;

                continue;
            }

            $customer = $this->newCustomer($request, $user, 'INDIVIDUAL', Customer::TYPE_INDIVIDUAL, Customer::FORM_INDIVIDUAL);
            $customer->individual()->create([
                'title' => $values["a{$n}_title"] ?? null,
                'full_name' => $name,
                'parent_or_spouse_name' => $values["a{$n}_parent_name"] ?? null,
                'gender_id' => Gender::query()->where('code', strtoupper((string) ($values["a{$n}_gender"] ?? '')))->value('id'),
                'marital_status_id' => MaritalStatus::query()->where('code', strtoupper((string) ($values["a{$n}_marital_status"] ?? '')))->value('id'),
                'date_of_birth' => $values["a{$n}_dob"] ?? null,
                'residence_country_id' => $this->countryId($values["a{$n}_country_of_residence"] ?? null),
                'employer_institution_name' => $values["a{$n}_employer"] ?? null,
                'ntn' => $values["a{$n}_ntn"] ?? null,
            ]);

            if ($number !== '') {
                $customer->identifications()->create([
                    'identification_document_type_id' => IdentificationDocumentType::query()->where('code', $type)->value('id')
                        ?? IdentificationDocumentType::query()->where('code', 'CNIC')->value('id'),
                    'document_number' => $number,
                    'is_primary' => true,
                ]);
            }

            if ($n === 1) {
                $this->addAddressesAndContacts($customer, $values, 'permanent_residential');
            }

            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function businessCustomer(AccountOpeningRequest $request, array $values, User $user): Customer
    {
        if ($request->customer) {
            return $request->customer;
        }

        $kind = $values['business_kind'] ?? 'other';
        $isSoleProprietor = $kind === 'sole_proprietorship';
        $name = trim((string) ($values['business_name'] ?? $values['title_of_account'] ?? ''));

        if ($name === '') {
            throw new AccountOpeningWorkflowException('The old form has no business name, so the customer cannot be created from it.');
        }

        $cnic = trim((string) ($values['business_cnic'] ?? ''));
        $ntn = trim((string) ($values['business_ntn'] ?? ''));

        $linked = $isSoleProprietor && $cnic !== ''
            ? $this->customerByIdentification($cnic)
            : ($ntn !== '' ? Customer::query()->whereHas('organization', fn ($query) => $query->where('ntn', $ntn))->first() : null);

        if ($linked) {
            return $linked;
        }

        $customer = $this->newCustomer(
            $request, $user, self::CATEGORY_BY_BUSINESS_KIND[$kind] ?? 'ENT_OTHERS',
            $isSoleProprietor ? Customer::TYPE_SOLE_PROPRIETOR : Customer::TYPE_ENTITY,
            $isSoleProprietor ? Customer::FORM_INDIVIDUAL : Customer::FORM_ENTITY,
        );

        $customer->organization()->create([
            'business_name' => $name,
            'business_commencement_date' => $values['commencement_date'] ?? null,
            'ntn' => $ntn !== '' ? $ntn : null,
        ]);

        if ($isSoleProprietor) {
            $customer->individual()->create(['full_name' => (string) ($values['title_of_account'] ?? $name)]);
        }

        if ($cnic !== '') {
            $customer->identifications()->create([
                'identification_document_type_id' => IdentificationDocumentType::query()->where('code', 'CNIC')->value('id'),
                'document_number' => $cnic,
                'is_primary' => true,
            ]);
        }

        $this->addAddressesAndContacts($customer, $values, 'registered_business');

        return $customer;
    }

    private function newCustomer(AccountOpeningRequest $request, User $user, string $categoryCode, string $type, string $formType): Customer
    {
        $branch = $request->branch;
        $category = CustomerCategory::query()->where('code', $categoryCode)->first();

        if (! $category) {
            throw new AccountOpeningWorkflowException('The customer categories are not set up, so the customer cannot be created from the old form.');
        }

        return Customer::create([
            'cif_number' => generateUniqueIdWithPrefix($branch->code, 'customers', 'cif_number'),
            'branch_id' => $branch->id,
            'cif_date' => $request->request_date,
            'customer_type' => $type,
            'aof_form_type' => $formType,
            'customer_category_id' => $category->id,
            'status' => 'active',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * The customer already holding this CNIC (with or without dashes), if any.
     */
    private function customerByIdentification(string $number): ?Customer
    {
        $digits = preg_replace('/\D/', '', $number) ?? '';
        $variants = array_unique(array_filter([
            $number,
            $digits,
            strlen($digits) === 13 ? substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12) : null,
        ]));

        $identification = CustomerIdentification::query()->whereIn('document_number', $variants)->orderBy('created_at')->first();

        return $identification?->customer;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function addAddressesAndContacts(Customer $customer, array $values, string $permanentType): void
    {
        $pakistan = $this->countryId('Pakistan');

        foreach (['mailing' => 'mailing', 'permanent' => $permanentType] as $prefix => $type) {
            if (blank($values["{$prefix}_city"] ?? null) && blank($values["{$prefix}_tehsil_district"] ?? null)) {
                continue;
            }

            $customer->addresses()->create([
                'address_type' => $type,
                'tehsil_district' => $values["{$prefix}_tehsil_district"] ?? null,
                'city' => $values["{$prefix}_city"] ?? null,
                'postal_code' => $values["{$prefix}_post_code"] ?? null,
                'country_id' => $pakistan,
                'is_primary' => $prefix === 'mailing',
            ]);
        }

        $contacts = [
            ['mobile', $values['mobile'] ?? null],
            ['email_personal_office', $values['email'] ?? null],
            ['telephone_residence_office', $values['mailing_telephone'] ?? null],
            ['fax_personal_office', $values['fax'] ?? null],
        ];

        foreach ($contacts as $position => [$type, $value]) {
            if (filled($value)) {
                $customer->contacts()->create(['contact_type' => $type, 'value' => (string) $value, 'sequence' => 1, 'is_primary' => $position === 0]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function account(AccountOpeningRequest $request, array $values, Customer $primary, User $user): Account
    {
        $branch = $request->branch;
        $foreign = ! empty($values['fcy_account_types']);
        $currencyCode = $foreign ? strtoupper((string) ($values['currency'] ?? 'USD')) : 'PKR';
        $currency = Currency::query()->where('code', $currencyCode)->first() ?? Currency::query()->where('code', $foreign ? 'USD' : 'PKR')->first();
        $typeKey = collect((array) ($foreign ? $values['fcy_account_types'] : ($values['pkr_account_types'] ?? [])))->first();
        $productCode = ($foreign ? self::PRODUCT_BY_FCY_TYPE : self::PRODUCT_BY_PKR_TYPE)[$typeKey] ?? ($foreign ? 'BCA_FCY' : 'BCA');
        $product = AccountProduct::query()->where('code', $productCode)->first();
        $instruction = OperatingInstruction::query()->where('code', self::OPERATING_INSTRUCTION[$values['signing_authority'] ?? 'singly'] ?? 'SINGLY')->first();

        if (! $currency || ! $product || ! $instruction) {
            throw new AccountOpeningWorkflowException('The account products, currencies or operating instructions are not set up, so the account cannot be created from the old form.');
        }

        $payload = [
            'title_of_account' => (string) ($values['title_of_account'] ?? $primary->displayName() ?? ''),
            'branch_id' => $branch->id,
            'opening_date' => $request->request_date,
            'account_product_id' => $product->id,
            'currency_id' => $currency->id,
            'account_class' => $product->product_class,
            'is_foreign_currency' => ! $currency->is_local,
            'operating_instruction_id' => $instruction->id,
            'special_instructions' => $values['special_instructions'] ?? null,
            'initial_deposit' => is_numeric($values['initial_deposit'] ?? null) ? $values['initial_deposit'] : null,
            'zakat_applicable' => ($values['zakat_deduction'] ?? null) === 'yes',
            'terms_accepted_at' => filled($values['terms_accepted'] ?? null) ? now() : null,
            'aof_version' => $request->aof_version,
        ];

        $account = $request->account;

        if ($account) {
            $account->update($payload);
        } else {
            $number = trim((string) ($values['account_no'] ?? ''));
            $number = $number !== '' && ! Account::withTrashed()->where('account_number', $number)->exists()
                ? $number
                : generateUniqueIdWithPrefix($branch->code, 'accounts', 'account_number');

            $account = Account::create($payload + [
                'account_number' => $number,
                'iban' => Account::nextIban($branch),
                'status' => 'active',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        }

        $account->update(['mailing_address_id' => $primary->addresses()->where('address_type', 'mailing')->value('id')]);

        return $account;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function holderRole(string $nature, array $values, int $index): string
    {
        return match (true) {
            $index === 0 && $nature === 'business' && ($values['business_kind'] ?? null) === 'sole_proprietorship' => 'sole_proprietor',
            $index === 0 => 'primary',
            default => 'joint',
        };
    }

    private function countryId(?string $name): ?string
    {
        return filled($name) ? Country::query()->where('name', trim($name))->value('id') : null;
    }
}
