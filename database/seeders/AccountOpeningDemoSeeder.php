<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountOpeningHistory;
use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\Branch;
use App\Models\Country;
use App\Models\CrsEntityClassification;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerDueDiligence;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\IncomeSource;
use App\Models\MaritalStatus;
use App\Models\OperatingInstruction;
use App\Models\Profession;
use App\Models\Relationship;
use App\Models\TransactionMode;
use App\Models\User;
use App\Models\WealthSource;
use App\Services\OldFormRegistrar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sample Account Opening requests for testing the list, filters, request page, maker-checker, history and print.
 *
 *   php artisan db:seed --class=AccountOpeningDemoSeeder                          (40 requests)
 *   ACCOUNT_OPENING_DEMO_COUNT=200 php artisan db:seed --class=AccountOpeningDemoSeeder
 *   ACCOUNT_OPENING_DEMO_COUNT=0 php artisan db:seed --class=AccountOpeningDemoSeeder   (remove the sample data only)
 *
 * It is not part of a normal seed: DatabaseSeeder runs it only when SEED_ACCOUNT_OPENING_DEMO=true in .env.
 * The requests cycle through both forms (Individual/Joint/Sole and Entity), both editions (new wizard and old paper
 * form) and the statuses draft, submitted, approved and rejected, spread over the branches, each with its history.
 * Every sample request, customer and account number starts with "DEMO-", so re-running the seeder replaces them and
 * "ACCOUNT_OPENING_DEMO_COUNT=0" removes them without touching real data.
 * It needs the reference data of BajkAccountOpeningSeeder (a normal seed has it) and at least one branch and one user.
 */
class AccountOpeningDemoSeeder extends Seeder
{
    private const PREFIX = 'DEMO-';

    private const STATUSES = ['draft', 'submitted', 'approved', 'rejected'];

    /** @var list<string> */
    private array $male = ['Muhammad Ali', 'Ahmed Raza', 'Usman Khan', 'Bilal Hussain', 'Zeeshan Abbasi', 'Faisal Mir', 'Kamran Awan', 'Imran Qureshi'];

    /** @var list<string> */
    private array $female = ['Ayesha Bibi', 'Fatima Noor', 'Sana Kausar', 'Rabia Khan', 'Mehwish Abbasi', 'Hina Shah', 'Nazia Parveen', 'Saima Akhtar'];

    /** @var list<string> */
    private array $companies = ['Kashmir Traders', 'Jhelum Enterprises', 'Neelum Builders', 'Mirpur Agro Industries', 'Muzaffarabad Welfare Trust', 'Poonch Mills'];

    public function run(): void
    {
        $count = (int) env('ACCOUNT_OPENING_DEMO_COUNT', 40);

        $this->removeExisting();

        if ($count <= 0) {
            $this->command?->info('Demo account opening requests removed.');

            return;
        }

        $branches = Branch::query()->orderBy('id')->limit(12)->get();
        $users = User::query()->orderBy('id')->limit(2)->get();

        if ($branches->isEmpty() || $users->isEmpty() || ! CustomerCategory::query()->exists() || ! AccountProduct::query()->exists()) {
            $this->command?->error('Need branches, a user and the Account Opening reference data (BajkAccountOpeningSeeder) first.');

            return;
        }

        mt_srand(20261026); // the same demo data every run
        $maker = $users->first();
        $checker = $users->count() > 1 ? $users->last() : $maker;
        $bar = $this->command?->getOutput()->createProgressBar($count);

        for ($i = 1; $i <= $count; $i++) {
            DB::transaction(fn () => $this->createRequest($i, $branches[($i - 1) % $branches->count()], $maker, $checker));
            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();
        $this->command?->info("{$count} demo account opening requests created.");
    }

    private function createRequest(int $i, Branch $branch, User $maker, User $checker): void
    {
        $old = $i % 2 === 0;
        $entity = intdiv($i - 1, 2) % 2 === 1;
        $status = self::STATUSES[intdiv($i - 1, 4) % 4] ?? 'draft';
        $date = Carbon::now()->subDays(mt_rand(1, 90))->startOfDay();
        $number = self::PREFIX.$branch->code.'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

        $request = $old
            ? $this->oldRequest($i, $branch, $entity, $number, $date, $maker)
            : $this->newRequest($i, $branch, $entity, $number, $date, $maker);

        $this->applyStatus($request, $status, $date, $maker, $checker);
    }

    /* ------------------------------------------------------------------ */
    /* New form (online wizard) */
    /* ------------------------------------------------------------------ */

    private function newRequest(int $i, Branch $branch, bool $entity, string $number, Carbon $date, User $maker): AccountOpeningRequest
    {
        $category = CustomerCategory::query()->where('code', $entity ? $this->pick(['GOVT_INSTITUTION', 'PRIVATE', 'PARTNERSHIP', 'NGO_NPO']) : 'INDIVIDUAL')->first()
            ?? CustomerCategory::query()->firstOrFail();
        $customer = $entity ? $this->entityCustomer($i, $branch, $category, $date) : $this->individualCustomer($i, $branch, $category, $date);
        $signatory = $entity ? $this->individualCustomer($i + 1000, $branch, CustomerCategory::query()->where('code', 'INDIVIDUAL')->first() ?? $category, $date, 'DEMO-S') : null;

        $account = $this->account($i, $branch, $customer, $entity, $date);
        $account->holders()->create(['customer_id' => $customer->id, 'holder_role' => 'primary', 'applicant_number' => 1, 'is_signatory' => ! $entity, 'signing_order' => 1]);

        if ($signatory) {
            $account->holders()->create(['customer_id' => $signatory->id, 'holder_role' => 'authorized_signatory', 'applicant_number' => 2, 'is_signatory' => true, 'signing_order' => 1, 'designation' => 'Authorized signatory']);
        } elseif ($i % 3 === 0) {
            $joint = $this->individualCustomer($i + 2000, $branch, $category, $date, 'DEMO-J');
            $account->holders()->create(['customer_id' => $joint->id, 'holder_role' => 'joint', 'applicant_number' => 2, 'is_signatory' => true, 'signing_order' => 2]);
        }

        $this->dueDiligence($account, $customer, $entity);

        return AccountOpeningRequest::create([
            'request_number' => $number,
            'aof_form_type' => $entity ? AccountOpeningRequest::FORM_ENTITY : AccountOpeningRequest::FORM_INDIVIDUAL,
            'form_edition' => AccountOpeningRequest::EDITION_NEW,
            'aof_version' => $entity ? 'BAJK-AOF-Govt-Public-Private-28-Jan-2026' : 'BAJK-AOF-Individual-19-Jan-2026',
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'account_id' => $account->id,
            'request_date' => $date,
            'terms_and_conditions_accepted' => true,
            'indemnity_undertaking_accepted' => true,
            'aof_copy_received_by_customer' => true,
            'shariah_compliant_investment_authorized' => $i % 5 === 0,
            'sales_staff_name_1' => $maker->name,
            'sales_staff_employee_no_1' => 'E-'.(1000 + $i),
            'checker_name' => 'Demo Checker',
            'checker_employee_no' => 'E-'.(2000 + $i),
            'checked_on' => $date,
            'status' => 'draft',
            'created_by' => $maker->id,
        ]);
    }

    private function individualCustomer(int $i, Branch $branch, CustomerCategory $category, Carbon $date, string $prefix = self::PREFIX.'C'): Customer
    {
        $female = $i % 2 === 1;
        $name = $this->pick($female ? $this->female : $this->male);
        $pakistan = Country::query()->where('name', 'Pakistan')->first();

        $customer = Customer::create([
            'cif_number' => $prefix.'-'.$branch->code.'-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'branch_id' => $branch->id,
            'cif_date' => $date,
            'customer_type' => Customer::TYPE_INDIVIDUAL,
            'aof_form_type' => Customer::FORM_INDIVIDUAL,
            'customer_category_id' => $category->id,
            'status' => 'draft',
        ]);

        $customer->individual()->create([
            'title' => $female ? 'ms' : 'mr',
            'full_name' => 'DEMO '.$name,
            'parent_or_spouse_name' => $this->pick($this->male),
            'parentage_relationship_id' => Relationship::query()->value('id'),
            'gender_id' => Gender::query()->where('code', $female ? 'FEMALE' : 'MALE')->value('id'),
            'marital_status_id' => MaritalStatus::query()->where('code', $this->pick(['MARRIED', 'SINGLE']))->value('id'),
            'date_of_birth' => Carbon::now()->subYears(mt_rand(22, 65))->subDays(mt_rand(0, 300))->toDateString(),
            'birth_country_id' => $pakistan?->id,
            'birth_city' => 'Muzaffarabad',
            'residence_country_id' => $pakistan?->id,
            'profession_id' => Profession::query()->inRandomOrder()->value('id'),
            'ntn' => (string) mt_rand(1000000, 9999999).'-'.mt_rand(1, 9),
        ]);

        $this->profile($customer, $i, $pakistan, true);

        $customer->fatcaDetail()->create([
            'q1_is_us_person' => false, 'q2_country_of_birth_us' => false, 'q3_has_us_address_or_phone' => false, 'q4_has_us_mandate_or_links' => false,
            'is_tax_resident_other_country' => false, 'self_certification_date' => $date,
        ]);
        $customer->nextOfKin()->create([
            'name' => 'DEMO '.$this->pick($this->male), 'relationship_id' => Relationship::query()->value('id'),
            'identification_number' => $this->cnic($i + 5000), 'city' => 'Muzaffarabad', 'country_id' => $pakistan?->id, 'telephone' => '0582'.mt_rand(100000, 999999),
        ]);

        return $customer;
    }

    private function entityCustomer(int $i, Branch $branch, CustomerCategory $category, Carbon $date): Customer
    {
        $pakistan = Country::query()->where('name', 'Pakistan')->first();

        $customer = Customer::create([
            'cif_number' => self::PREFIX.'C-'.$branch->code.'-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'branch_id' => $branch->id,
            'cif_date' => $date,
            'customer_type' => Customer::TYPE_ENTITY,
            'aof_form_type' => Customer::FORM_ENTITY,
            'customer_category_id' => $category->id,
            'economic_sector_code' => '8411',
            'status' => 'draft',
        ]);

        $customer->organization()->create([
            'business_name' => 'DEMO '.$this->pick($this->companies).' '.$i,
            'business_registration_number' => 'REG-'.mt_rand(10000, 99999),
            'business_incorporation_date' => Carbon::now()->subYears(mt_rand(2, 25))->toDateString(),
            'incorporation_country_id' => $pakistan?->id,
            'ntn' => (string) mt_rand(1000000, 9999999).'-'.mt_rand(1, 9),
            'number_of_employees' => mt_rand(5, 200),
        ]);

        $this->profile($customer, $i, $pakistan, false);

        $customer->fatcaDetail()->create([
            'entity_incorporated_in_us' => false, 'incorporation_country_id' => $pakistan?->id, 'nfe_type' => 'active',
            'crs_entity_classification_id' => CrsEntityClassification::query()->value('id'), 'is_tax_resident_other_country' => false, 'self_certification_date' => $date,
        ]);

        return $customer;
    }

    /**
     * Identification, nationality, addresses and contacts of a customer.
     */
    private function profile(Customer $customer, int $i, ?Country $pakistan, bool $individual): void
    {
        $customer->identifications()->create([
            'identification_document_type_id' => IdentificationDocumentType::query()->where('code', 'CNIC')->value('id'),
            'document_number' => $this->cnic($i),
            'issue_date' => Carbon::now()->subYears(3)->toDateString(),
            'expiry_date' => Carbon::now()->addYears(7)->toDateString(),
            'place_of_issuance' => 'Muzaffarabad',
            'is_primary' => true,
        ]);

        if ($pakistan) {
            $customer->nationalities()->create(['country_id' => $pakistan->id, 'is_primary' => true, 'sequence' => 1]);
        }

        foreach ([$individual ? 'permanent_residential' : 'registered_business', 'mailing'] as $type) {
            $customer->addresses()->create([
                'address_type' => $type, 'house_office_no' => (string) mt_rand(1, 200), 'street_area' => 'Main Road',
                'tehsil_district' => 'Muzaffarabad', 'city' => 'Muzaffarabad', 'country_id' => $pakistan?->id, 'postal_code' => '13100', 'is_primary' => $type === 'mailing',
            ]);
        }

        $customer->contacts()->create(['contact_type' => 'mobile', 'country_code' => '+92', 'value' => '3'.mt_rand(0, 4).mt_rand(10000000, 99999999), 'sequence' => 1, 'is_primary' => true]);
        $customer->contacts()->create(['contact_type' => 'email_personal_office', 'value' => 'demo'.$i.'@example.com', 'sequence' => 1]);
    }

    private function account(int $i, Branch $branch, Customer $customer, bool $entity, Carbon $date): Account
    {
        $product = AccountProduct::query()->where('code', $this->pick($entity ? ['BCA', 'PLS'] : ['BCA', 'PLS', 'SDA', 'BMBA']))->first() ?? AccountProduct::query()->firstOrFail();
        $currency = Currency::query()->where('code', 'PKR')->firstOrFail();

        return Account::create([
            'account_number' => self::PREFIX.$branch->code.'-A'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'iban' => Account::nextIban($branch),
            'title_of_account' => $customer->displayName(),
            'branch_id' => $branch->id,
            'opening_date' => $date,
            'account_product_id' => $product->id,
            'currency_id' => $currency->id,
            'account_class' => $product->product_class,
            'is_foreign_currency' => false,
            'operating_instruction_id' => OperatingInstruction::query()->where('code', $entity ? 'JOINTLY' : 'SINGLY')->value('id') ?? OperatingInstruction::query()->value('id'),
            'sms_alerts_subscribed' => true,
            'digital_channels_opted' => $i % 3 === 0,
            'initial_deposit' => $this->pick([500, 1000, 5000, 10000, 25000]),
            'zakat_applicable' => (bool) $product->is_zakat_applicable,
            'terms_accepted_at' => $date,
            'status' => 'pending',
        ]);
    }

    private function dueDiligence(Account $account, Customer $customer, bool $entity): void
    {
        $dd = CustomerDueDiligence::create([
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'cdd_type' => $entity ? CustomerDueDiligence::TYPE_BUSINESS : CustomerDueDiligence::TYPE_INDIVIDUAL,
            'customer_source' => $this->pick([CustomerDueDiligence::SOURCE_WALK_IN, CustomerDueDiligence::SOURCE_MARKETED, CustomerDueDiligence::SOURCE_REFERRED]),
            'physical_verification_conducted' => true,
            'proscribed_list_cleared' => true,
            'employer_name' => $entity ? null : 'Demo Employer',
            'monthly_income' => $this->pick([40000, 75000, 120000, 250000]),
            'business_name' => $entity ? $customer->displayName() : null,
            'initial_deposit' => $account->initial_deposit,
            'expected_monthly_credit_amount' => $this->pick([50000, 150000, 500000]),
            'expected_monthly_credit_count' => mt_rand(3, 30),
            'expected_monthly_debit_amount' => $this->pick([30000, 100000, 400000]),
            'expected_monthly_debit_count' => mt_rand(3, 30),
        ]);

        $dd->incomeSources()->attach(IncomeSource::query()->inRandomOrder()->limit(2)->pluck('id')->all());
        $dd->wealthSources()->attach(WealthSource::query()->inRandomOrder()->limit(1)->pluck('id')->all());

        $modes = TransactionMode::query()->inRandomOrder()->limit(2)->pluck('id');
        foreach ($modes as $modeId) {
            $dd->transactionModes()->attach($modeId, ['direction' => 'credit']);
            $dd->transactionModes()->attach($modeId, ['direction' => 'debit']);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Old form (paper) */
    /* ------------------------------------------------------------------ */

    private function oldRequest(int $i, Branch $branch, bool $entity, string $number, Carbon $date, User $maker): AccountOpeningRequest
    {
        $request = AccountOpeningRequest::create([
            'request_number' => $number,
            'aof_form_type' => AccountOpeningRequest::formTypeForNature($entity ? 'business' : 'individual'),
            'form_edition' => AccountOpeningRequest::EDITION_OLD,
            'aof_version' => 'BAJK-AOF-Old-Paper-Form',
            'branch_id' => $branch->id,
            'request_date' => $date,
            'status' => 'draft',
            'created_by' => $maker->id,
        ]);

        $data = $entity ? $this->businessPaperData($i, $branch) : $this->individualPaperData($i, $branch);
        $keyWord = 'Kashmir'.$i;
        $request->paperForm()->create([
            'data' => $data + ['terms_accepted' => true],
            'key_word' => $entity ? null : $keyWord,
            'display_name' => 'DEMO '.($entity ? $data['business_name'] : $data['a1_name']),
        ]);

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function individualPaperData(int $i, Branch $branch): array
    {
        $name = $this->pick($i % 4 === 0 ? $this->female : $this->male);

        return [
            'branch_code' => $branch->code, 'branch_name' => $branch->name, 'account_no' => self::PREFIX.$branch->code.'-P'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'pkr_account_types' => [$this->pick(['current', 'pls_saving', 'sd'])], 'account_nature' => 'individual', 'individual_kind' => 'single',
            'title_of_account' => 'DEMO '.$name, 'mailing_tehsil_district' => 'Muzaffarabad', 'mailing_city' => 'Muzaffarabad', 'mailing_post_code' => '13100', 'mailing_telephone' => '05822-'.mt_rand(100000, 999999),
            'email' => 'demo-old'.$i.'@example.com', 'mobile' => '03'.mt_rand(0, 4).mt_rand(10000000, 99999999),
            'a1_title' => 'mr', 'a1_name' => $name, 'a1_parent_relation' => 'so', 'a1_parent_name' => $this->pick($this->male), 'a1_gender' => 'male',
            'a1_marital_status' => 'married', 'a1_dob' => Carbon::now()->subYears(mt_rand(22, 60))->toDateString(), 'a1_nationality' => 'Pakistani', 'a1_country_of_residence' => 'Pakistan',
            'a1_id_type' => 'cnic', 'a1_id_number' => $this->cnic($i + 3000), 'a1_occupation' => 'private_service',
            'introducer_name' => 'Demo Introducer', 'introducer_bank_branch' => $branch->name, 'introducer_account_no' => '00'.mt_rand(10000, 99999),
            'authorized_by_name' => 'Demo Manager', 'authorized_by_employee_no' => 'E-1', 'checker_name' => 'Demo Checker', 'checker_employee_no' => 'E-2',
            'signing_authority' => 'singly', 'signatories' => [['name' => $name, 'signed' => true, 'stamped' => false]], 'docs_individual' => ['d1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function businessPaperData(int $i, Branch $branch): array
    {
        $name = $this->pick($this->companies).' '.$i;

        return [
            'branch_code' => $branch->code, 'branch_name' => $branch->name, 'account_no' => self::PREFIX.$branch->code.'-P'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'pkr_account_types' => ['current'], 'account_nature' => 'business', 'business_kind' => 'partnership', 'partnership_kind' => 'registered',
            'title_of_account' => $name, 'business_name' => $name, 'business_ntn' => (string) mt_rand(1000000, 9999999).'-'.mt_rand(1, 9),
            'mailing_tehsil_district' => 'Muzaffarabad', 'mailing_city' => 'Muzaffarabad', 'mailing_post_code' => '13100', 'mailing_telephone' => '05822-'.mt_rand(100000, 999999),
            'email' => 'demo-biz'.$i.'@example.com', 'signing_authority' => 'jointly',
            'authorized_by_name' => 'Demo Manager', 'authorized_by_employee_no' => 'E-1', 'checker_name' => 'Demo Checker', 'checker_employee_no' => 'E-2',
            'signatories' => [['name' => 'Partner One', 'signed' => true, 'stamped' => true], ['name' => 'Partner Two', 'signed' => true, 'stamped' => true]],
            'docs_partnership' => ['d1', 'd2'],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Workflow state and history */
    /* ------------------------------------------------------------------ */

    private function applyStatus(AccountOpeningRequest $request, string $status, Carbon $date, User $maker, User $checker): void
    {
        $this->history($request, 'created', $maker, $date->copy()->setTime(9, 0), note: 'Demo request started.');
        $this->history($request, 'step_saved', $maker, $date->copy()->setTime(9, 30), $request->isOldEdition() ? 'paper' : 'cif');

        if ($status === 'draft') {
            return;
        }

        $submittedAt = $date->copy()->setTime(11, 0);
        $request->forceFill(['status' => 'submitted', 'submitted_by' => $maker->id, 'submitted_at' => $submittedAt])->save();
        $request->customer?->update(['status' => 'pending_verification']);
        $this->history($request, 'submitted', $maker, $submittedAt);

        if ($status === 'submitted') {
            return;
        }

        $decidedAt = $date->copy()->setTime(15, 0);

        if ($status === 'rejected') {
            $request->forceFill(['status' => 'rejected', 'rejected_by' => $checker->id, 'rejected_at' => $decidedAt, 'rejection_reason' => 'Demo: the CNIC copy is not clear. Please attach a new copy.'])->save();
            $request->customer?->update(['status' => 'draft']);
            $this->history($request, 'rejected', $checker, $decidedAt, note: 'Demo: the CNIC copy is not clear. Please attach a new copy.');

            return;
        }

        $request->forceFill([
            'status' => 'approved', 'approved_by' => $checker->id, 'approved_at' => $decidedAt, 'locked_at' => $decidedAt,
            'authorized_by_name' => $checker->name, 'authorized_on' => $date,
        ])->save();

        if ($request->isOldEdition()) {
            // Approving an old request creates its customer and account, the same as in the application.
            app(OldFormRegistrar::class)->register($request->loadMissing(['branch', 'paperForm', 'account']), $checker);
            $request->unsetRelation('customer')->unsetRelation('account');
            $this->markDemo($request);
        } else {
            $request->customer?->update(['status' => 'active', 'approved_by' => $checker->id, 'approved_at' => $decidedAt]);
            $request->account?->update(['status' => 'active', 'approved_by' => $checker->id, 'approved_at' => $decidedAt]);
        }

        $this->history($request, 'approved', $checker, $decidedAt, note: 'Approved and locked.');
    }

    /**
     * The customer and account an old request created get the DEMO- prefix too, so they are removed with it.
     */
    private function markDemo(AccountOpeningRequest $request): void
    {
        $request->refresh();

        foreach ($request->account?->holders()->with('customer')->get() ?? [] as $holder) {
            if ($holder->customer && ! str_starts_with($holder->customer->cif_number, self::PREFIX)) {
                $holder->customer->update(['cif_number' => self::PREFIX.$holder->customer->cif_number]);
            }
        }

        if ($request->account && ! str_starts_with($request->account->account_number, self::PREFIX)) {
            $request->account->update(['account_number' => self::PREFIX.$request->account->account_number]);
        }
    }

    private function history(AccountOpeningRequest $request, string $event, User $user, Carbon $when, ?string $step = null, ?string $note = null): void
    {
        $history = new AccountOpeningHistory;
        $history->forceFill([
            'account_opening_request_id' => $request->id, 'event' => $event, 'step' => $step, 'user_id' => $user->id, 'note' => $note, 'created_at' => $when,
        ])->save();
    }

    /* ------------------------------------------------------------------ */
    /* Helpers */
    /* ------------------------------------------------------------------ */

    /**
     * Remove the demo requests with their histories, uploads, customers and accounts (children go with them).
     */
    private function removeExisting(): void
    {
        $requests = AccountOpeningRequest::withTrashed()->where('request_number', 'like', self::PREFIX.'%')->get();

        foreach ($requests as $request) {
            $request->clearMediaCollection();
        }

        $requestIds = $requests->pluck('id');
        DB::table('account_opening_histories')->whereIn('account_opening_request_id', $requestIds)->delete();
        DB::table('account_opening_paper_forms')->whereIn('account_opening_request_id', $requestIds)->delete();
        AccountOpeningRequest::withTrashed()->whereIn('id', $requestIds)->forceDelete();

        Account::withTrashed()->where('account_number', 'like', self::PREFIX.'%')->get()->each->forceDelete();
        Customer::withTrashed()->where('cif_number', 'like', self::PREFIX.'%')->get()->each->forceDelete();
    }

    /**
     * @template T
     *
     * @param  list<T>  $items
     * @return T
     */
    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    /**
     * A fake CNIC that cannot belong to a real customer (it starts with 99999).
     */
    private function cnic(int $seed): string
    {
        return '99999-'.str_pad((string) $seed, 7, '0', STR_PAD_LEFT).'-'.($seed % 10);
    }
}
