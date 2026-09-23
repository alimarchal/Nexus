<?php

use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\AccountPurpose;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\IncomeSource;
use App\Models\OperatingInstruction;
use App\Models\TransactionMode;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // The module migration seeds these; firstOrCreate keeps the test idempotent.
    foreach ([
        'view account openings',
        'create account openings',
        'edit account openings',
        'delete account openings',
        'approve account openings',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());

    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->branch = Branch::factory()->create();
});

/**
 * Start an AOF and return the request, so each step test can build on it.
 */
function startAccountOpening(mixed $test, string $categoryCode = 'INDIVIDUAL'): AccountOpeningRequest
{
    $category = CustomerCategory::code($categoryCode)->firstOrFail();

    $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => $category->id,
        'request_date' => now()->toDateString(),
    ])->assertRedirect();

    return AccountOpeningRequest::latest('id')->firstOrFail();
}

test('authorized user can view the account opening index', function () {
    $this->actingAs($this->admin)
        ->get(route('account-openings.index'))
        ->assertSuccessful()
        ->assertViewIs('account-openings.index');
});

test('user without permission cannot view the account opening index', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('account-openings.index'))
        ->assertForbidden();
});

test('starting an account opening creates the request and its CIF', function () {
    $accountOpeningRequest = startAccountOpening($this);

    expect($accountOpeningRequest->customer)->not->toBeNull()
        ->and($accountOpeningRequest->customer->customer_type)->toBe(Customer::TYPE_INDIVIDUAL)
        ->and($accountOpeningRequest->status)->toBe('draft')
        ->and($accountOpeningRequest->request_number)->toContain($this->branch->code);
});

test('a sole proprietorship needs both the individual and the business profile', function () {
    $category = CustomerCategory::code('SOLE_PROP')->firstOrFail();

    expect($category->resolvedCustomerType())->toBe(Customer::TYPE_SOLE_PROPRIETOR)
        ->and($category->requires_individual_profile)->toBeTrue()
        ->and($category->requires_organization_profile)->toBeTrue();
});

test('the CIF step stores the individual details and nationalities', function () {
    $accountOpeningRequest = startAccountOpening($this);
    $pakistan = Country::code('PK')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'cif']), [
            'full_name' => 'Ali Raza',
            'date_of_birth' => '1990-05-04',
            'gender_id' => Gender::code('MALE')->firstOrFail()->id,
            'nationalities' => [$pakistan->id],
        ])
        ->assertRedirect(route('account-openings.steps.edit', [$accountOpeningRequest, 'identification']));

    $customer = $accountOpeningRequest->fresh()->customer;

    expect($customer->individual->full_name)->toBe('Ali Raza')
        ->and($customer->nationalities)->toHaveCount(1)
        ->and($customer->nationalities->first()->is_primary)->toBeTrue();
});

test('the identification step requires a mobile number', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'identification']), [
            'identifications' => [['document_number' => '81303-1234567-1']],
            'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        ])
        ->assertSessionHasErrors('contacts.mobile.value');
});

test('the identification step stores identification, address and contact', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'identification']), [
            'identifications' => [[
                'identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id,
                'document_number' => '81303-1234567-1',
                'expiry_date' => '2030-01-01',
            ]],
            'addresses' => [
                'permanent_residential' => ['city' => 'Muzaffarabad', 'street_area' => 'Gojra'],
            ],
            'contacts' => [
                'mobile' => ['country_code' => '+92', 'value' => '3008169924'],
            ],
        ])
        ->assertSessionHasNoErrors();

    $customer = $accountOpeningRequest->fresh()->customer;

    expect($customer->identifications)->toHaveCount(1)
        ->and($customer->identifications->first()->is_primary)->toBeTrue()
        ->and($customer->addresses)->toHaveCount(1)
        ->and($customer->contacts->firstWhere('contact_type', 'mobile')->value)->toBe('3008169924');
});

test('the account step creates the account with a number and IBAN', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'account']), [
            'title_of_account' => 'Ali Raza',
            'opening_date' => now()->toDateString(),
            'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
            'currency_id' => Currency::code('PKR')->firstOrFail()->id,
            'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
            'cheque_book_required' => 1,
            'cheque_book_quantity' => 1,
            'cheque_book_leaves' => 25,
        ])
        ->assertSessionHasNoErrors();

    $account = $accountOpeningRequest->fresh()->account;

    expect($account)->toBeInstanceOf(Account::class)
        ->and($account->account_number)->not->toBeEmpty()
        ->and($account->iban)->toStartWith('PK')
        ->and($account->account_class)->toBe('saving')
        ->and($account->chequeBooks)->toHaveCount(1);
});

test('due diligence records income sources and credit or debit modes separately', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)->put(route('account-openings.steps.update', [$accountOpeningRequest, 'account']), [
        'title_of_account' => 'Ali Raza',
        'opening_date' => now()->toDateString(),
        'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
        'currency_id' => Currency::code('PKR')->firstOrFail()->id,
        'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
    ])->assertSessionHasNoErrors();

    $cash = TransactionMode::code('CASH')->firstOrFail();
    $clearing = TransactionMode::code('CLEARING')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'cdd']), [
            'customer_source' => 'walk_in',
            'income_sources' => [IncomeSource::code('SALARIED')->firstOrFail()->id],
            'account_purposes' => [AccountPurpose::code('SAVING')->firstOrFail()->id],
            'credit_modes' => [$cash->id],
            'debit_modes' => [$clearing->id],
            'expected_monthly_credit_amount' => 150000,
        ])
        ->assertSessionHasNoErrors();

    $dueDiligence = $accountOpeningRequest->fresh()->account->dueDiligences->first();

    expect($dueDiligence)->not->toBeNull()
        ->and($dueDiligence->incomeSources)->toHaveCount(1)
        ->and($dueDiligence->creditModes()->pluck('transaction_modes.id')->all())->toBe([$cash->id])
        ->and($dueDiligence->debitModes()->pluck('transaction_modes.id')->all())->toBe([$clearing->id]);
});

test('an incomplete request cannot be submitted', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)
        ->post(route('account-openings.submit', $accountOpeningRequest))
        ->assertSessionHas('error');

    expect($accountOpeningRequest->fresh()->status)->toBe('draft');
});
