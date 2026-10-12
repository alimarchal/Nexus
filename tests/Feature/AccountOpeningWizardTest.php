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
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
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
        ->assertSessionHasErrors(['contacts.mobile.1.value' => 'Please enter at least one contact number to continue.']);
});

test('a missing mobile number never shows the technical field name to the user', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $response = $this->actingAs($this->admin)
        ->from(route('account-openings.steps.edit', [$accountOpeningRequest, 'identification']))
        ->followingRedirects()
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'identification']), [
            'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id, 'document_number' => '81303-1234567-1']],
            'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        ]);

    $response->assertSee('Please enter at least one contact number to continue.')
        ->assertDontSee('contacts.mobile1.value')
        ->assertDontSee('contacts.mobile.1.value field is required');
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
                'mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']],
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
            'terms_and_conditions_accepted' => 1,
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
        'terms_and_conditions_accepted' => 1,
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

/**
 * Submit the identification step with only the mobile number varying.
 */
function submitMobile(mixed $test, AccountOpeningRequest $request, string $code, string $number): TestResponse
{
    return $test->actingAs($test->admin)
        ->put(route('account-openings.steps.update', [$request, 'identification']), [
            'identifications' => [[
                'identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id,
                'document_number' => '81303-1234567-1',
            ]],
            'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
            'contacts' => ['mobile' => [1 => ['country_code' => $code, 'value' => $number]]],
        ]);
}

test('a +92 mobile number must be exactly ten digits', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitMobile($this, $accountOpeningRequest, '+92', '3008169924')->assertSessionHasNoErrors();

    foreach (['03008169924', '30081699245', '300816992'] as $bad) {
        submitMobile($this, $accountOpeningRequest, '+92', $bad)
            ->assertSessionHasErrors('contacts.mobile.1.value');
    }
});

test('a mobile number with letters or symbols is rejected', function () {
    $accountOpeningRequest = startAccountOpening($this);

    foreach (['30081699ab', '300-816-9924', '300 816 9924'] as $bad) {
        submitMobile($this, $accountOpeningRequest, '+92', $bad)
            ->assertSessionHasErrors(['contacts.mobile.1.value' => 'The mobile number can contain digits only. Remove letters, spaces and symbols.']);
    }
});

test('the ten digit limit applies to +92 only', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitMobile($this, $accountOpeningRequest, '+44', '7911123456')->assertSessionHasNoErrors();
    submitMobile($this, $accountOpeningRequest, '+971', '501234567')->assertSessionHasNoErrors();
    submitMobile($this, $accountOpeningRequest, '+44', '12')->assertSessionHasErrors('contacts.mobile.1.value');
});

test('an invalid mobile number does not overwrite the saved one', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitMobile($this, $accountOpeningRequest, '+92', '3008169924');
    submitMobile($this, $accountOpeningRequest, '+92', '03008169924');

    expect($accountOpeningRequest->fresh()->customer->contacts->firstWhere('contact_type', 'mobile')->value)->toBe('3008169924');
});

/**
 * Submit the compliance step with only the tax residency fields varying.
 *
 * @param  array<int, array<string, mixed>>  $residencies
 */
function submitTaxResidency(mixed $test, AccountOpeningRequest $request, bool $otherCountry, array $residencies): TestResponse
{
    return $test->actingAs($test->admin)
        ->put(route('account-openings.steps.update', [$request, 'compliance']), [
            'is_tax_resident_other_country' => $otherCountry ? 1 : 0,
            'tax_residencies' => $residencies,
        ]);
}

test('tax residency details are ignored and cleared while the other-country box is unticked', function () {
    $accountOpeningRequest = startAccountOpening($this);
    $uk = Country::where('iso2', 'GB')->firstOrFail();

    submitTaxResidency($this, $accountOpeningRequest, true, [['country_id' => $uk->id, 'tin' => 'UK-123']])->assertSessionHasNoErrors();
    expect($accountOpeningRequest->fresh()->customer->taxResidencies)->toHaveCount(1);

    submitTaxResidency($this, $accountOpeningRequest, false, [['country_id' => $uk->id, 'tin' => 'UK-123']])->assertSessionHasNoErrors();
    expect($accountOpeningRequest->fresh()->customer->taxResidencies)->toHaveCount(0);
});

test('ticking the other-country box requires a country other than Pakistan or the USA', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitTaxResidency($this, $accountOpeningRequest, true, [[]])->assertSessionHasErrors('tax_residencies');

    foreach (['PK', 'US'] as $iso) {
        submitTaxResidency($this, $accountOpeningRequest, true, [['country_id' => Country::where('iso2', $iso)->firstOrFail()->id]])
            ->assertSessionHasErrors('tax_residencies');
    }

    expect($accountOpeningRequest->fresh()->customer->taxResidencies)->toHaveCount(0);
});

test('the tax residency fields render greyed out by default and without Pakistan or the USA', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $html = $this->actingAs($this->admin)
        ->get(route('account-openings.steps.edit', [$accountOpeningRequest, 'compliance']))
        ->assertSuccessful()
        ->assertSee('x-data="{ other: false }"', false)
        ->assertSee('x-bind:disabled="!other"', false)
        ->getContent();

    $taxSection = Str::after($html, 'data-tax-residency');
    $taxSection = Str::before($taxSection, 'Politically Exposed Person');

    expect($taxSection)->toContain('United Kingdom')->not->toContain('>Pakistan<')->not->toContain('United States of America');
});

test('the tax residency fields are active when the other-country box was saved ticked', function () {
    $accountOpeningRequest = startAccountOpening($this);
    submitTaxResidency($this, $accountOpeningRequest, true, [['country_id' => Country::where('iso2', 'GB')->firstOrFail()->id]]);

    $this->actingAs($this->admin)
        ->get(route('account-openings.steps.edit', [$accountOpeningRequest, 'compliance']))
        ->assertSee('x-data="{ other: true }"', false);
});

/**
 * Post the account step with the minimum valid fields; $extra overrides or adds fields.
 *
 * @param  array<string, mixed>  $extra
 */
function submitAccountStep(mixed $test, AccountOpeningRequest $request, array $extra = []): TestResponse
{
    return $test->actingAs($test->admin)
        ->put(route('account-openings.steps.update', [$request, 'account']), [
            'title_of_account' => 'Ali Raza',
            'opening_date' => now()->toDateString(),
            'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
            'currency_id' => Currency::code('PKR')->firstOrFail()->id,
            'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
            ...$extra,
        ]);
}

test('the account step cannot continue until the terms and conditions are accepted', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitAccountStep($this, $accountOpeningRequest)
        ->assertSessionHasErrors(['terms_and_conditions_accepted' => 'Please review and accept the Terms and Conditions before continuing.']);

    submitAccountStep($this, $accountOpeningRequest, ['terms_and_conditions_accepted' => 0])
        ->assertSessionHasErrors('terms_and_conditions_accepted');

    expect($accountOpeningRequest->fresh()->account)->toBeNull();
});

test('accepting the terms and conditions lets the account step continue', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitAccountStep($this, $accountOpeningRequest, ['terms_and_conditions_accepted' => 1])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account-openings.steps.edit', [$accountOpeningRequest, 'holders']));
});

test('save and exit may keep a draft without the terms being accepted yet', function () {
    $accountOpeningRequest = startAccountOpening($this);

    submitAccountStep($this, $accountOpeningRequest, ['save_and_exit' => 1])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account-openings.show', $accountOpeningRequest));
});

test('the terms and conditions error is shown beside the checkbox', function () {
    $accountOpeningRequest = startAccountOpening($this);

    $this->actingAs($this->admin)
        ->from(route('account-openings.steps.edit', [$accountOpeningRequest, 'account']))
        ->followingRedirects()
        ->put(route('account-openings.steps.update', [$accountOpeningRequest, 'account']), [
            'title_of_account' => 'Ali Raza',
            'opening_date' => now()->toDateString(),
            'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
            'currency_id' => Currency::code('PKR')->firstOrFail()->id,
            'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
        ])
        ->assertSee('Please review and accept the Terms and Conditions before continuing.');
});
