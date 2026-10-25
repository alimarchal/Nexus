<?php

use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\AccountPurpose;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\DocumentRequirement;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\IncomeSource;
use App\Models\OperatingInstruction;
use App\Models\User;
use App\Services\AccountOpeningWorkflow;
use App\Support\PakistanIban;
use Database\Seeders\BajkAccountOpeningSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->branch = Branch::factory()->create(['code' => '0001']);
    $this->otherBranch = Branch::factory()->create(['code' => '0002']);
    $this->admin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

function aoiStart(mixed $test, string $categoryCode = 'INDIVIDUAL', string $formType = AccountOpeningRequest::FORM_INDIVIDUAL, ?User $as = null): AccountOpeningRequest
{
    $test->actingAs($as ?? $test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => $formType,
        'customer_category_id' => CustomerCategory::code($categoryCode)->firstOrFail()->id,
        'request_date' => now()->toDateString(),
        'economic_sector_code' => '1234',
    ])->assertSessionHasNoErrors();

    return AccountOpeningRequest::latest('id')->firstOrFail();
}

function aoiPut(mixed $test, AccountOpeningRequest $request, string $step, array $data): TestResponse
{
    return $test->actingAs($test->admin)->put(route('account-openings.steps.update', [$request, $step]), $data);
}

function aoiAccount(mixed $test, AccountOpeningRequest $request, array $extra = []): TestResponse
{
    return aoiPut($test, $request, 'account', [
        'title_of_account' => 'Ali Raza',
        'opening_date' => now()->toDateString(),
        'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
        'currency_id' => Currency::code('PKR')->firstOrFail()->id,
        'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
        'terms_and_conditions_accepted' => 1,
        ...$extra,
    ]);
}

function aoiCustomer(mixed $test, string $cif, ?string $createdAt = null): Customer
{
    $customer = Customer::create([
        'cif_number' => $cif,
        'branch_id' => $test->branch->id,
        'cif_date' => now()->toDateString(),
        'customer_type' => 'individual',
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'status' => 'draft',
    ]);
    $customer->individual()->create(['full_name' => 'Customer '.$cif, 'date_of_birth' => '1990-01-01']);

    if ($createdAt) {
        $customer->forceFill(['created_at' => $createdAt])->saveQuietly();
    }

    return $customer;
}

function aoiPng(string $name): UploadedFile
{
    // The file name is appended after the PNG data so each image can be told apart once stored under a hashed name.
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==').$name);
}

function aoiBranchUser(mixed $test, Branch $branch): User
{
    $user = User::factory()->create(['branch_id' => $branch->id]);
    $user->assignRole('branch');

    return $user;
}

// ---------------------------------------------------------------- #162 / #167

test('the server accepts only a customer category that applies to the selected form', function () {
    $entityCategory = CustomerCategory::where('applies_to', 'entity')->firstOrFail();

    $this->actingAs($this->admin)->post(route('account-openings.store'), [
        'branch_id' => $this->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => $entityCategory->id,
        'request_date' => now()->toDateString(),
    ])->assertSessionHasErrors('customer_category_id');

    expect(AccountOpeningRequest::count())->toBe(0);

    aoiStart($this);
    expect(AccountOpeningRequest::count())->toBe(1);
});

test('a branch user can start a request only for their own branch', function () {
    $user = aoiBranchUser($this, $this->branch);
    $payload = [
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ];

    $this->actingAs($user)->post(route('account-openings.store'), $payload + ['branch_id' => $this->otherBranch->id])
        ->assertSessionHasErrors('branch_id');
    expect(AccountOpeningRequest::count())->toBe(0);

    $this->actingAs($user)->post(route('account-openings.store'), $payload + ['branch_id' => $this->branch->id])
        ->assertSessionHasNoErrors();
    expect(AccountOpeningRequest::count())->toBe(1);
});

test('the start page lists only the branches the user may use', function () {
    $user = aoiBranchUser($this, $this->branch);

    $this->actingAs($user)->get(route('account-openings.create'))
        ->assertSuccessful()
        ->assertViewHas('branches', fn ($branches) => $branches->pluck('id')->all() === [$this->branch->id]);

    $this->flushSession();
    $this->actingAs($this->admin)->get(route('account-openings.create'))
        ->assertViewHas('branches', fn ($branches) => $branches->count() >= 2);
});

// ---------------------------------------------------------------- #163

test('the customer information step needs the name that belongs to the form type', function () {
    $individual = aoiStart($this);
    aoiPut($this, $individual, 'cif', ['business_name' => 'Only Business'])->assertSessionHasErrors('full_name');

    $entity = aoiStart($this, 'LIMITED_CO', AccountOpeningRequest::FORM_ENTITY);
    aoiPut($this, $entity, 'cif', ['full_name' => 'Only Person'])->assertSessionHasErrors('business_name');
    aoiPut($this, $entity, 'cif', ['business_name' => 'Acme Ltd'])->assertSessionHasNoErrors();

    $sole = aoiStart($this, 'SOLE_PROP');
    aoiPut($this, $sole, 'cif', ['business_name' => 'Ali Traders'])->assertSessionHasErrors('full_name');
    aoiPut($this, $sole, 'cif', ['full_name' => 'Ali Raza', 'date_of_birth' => '1990-05-04'])->assertSessionHasErrors('business_name');
    aoiPut($this, $sole, 'cif', ['full_name' => 'Ali Raza', 'date_of_birth' => '1990-05-04', 'business_name' => 'Ali Traders'])->assertSessionHasNoErrors();
});

// ---------------------------------------------------------------- #164

test('the document checklist accepts only document types of the customer category', function () {
    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();

    $requirement = DocumentRequirement::where('customer_category_id', $request->customer->customer_category_id)->firstOrFail();
    $payload = [
        'income_sources' => [IncomeSource::active()->forForm('individual')->firstOrFail()->id],
        'account_purposes' => [AccountPurpose::active()->forForm('individual')->firstOrFail()->id],
    ];

    aoiPut($this, $request, 'cdd', $payload + ['documents' => [(string) Str::uuid() => ['status' => 'received']]])
        ->assertSessionHasErrors();
    expect($request->documents()->count())->toBe(0);

    aoiPut($this, $request, 'cdd', $payload + ['documents' => [$requirement->document_type_id => ['status' => 'received']]])
        ->assertSessionHasNoErrors();
    expect($request->documents()->count())->toBe(1);
});

// ---------------------------------------------------------------- #165

test('the generated IBAN is a valid Pakistan IBAN and never repeats', function () {
    $first = aoiStart($this);
    aoiAccount($this, $first)->assertSessionHasNoErrors();
    $second = aoiStart($this);
    aoiAccount($this, $second)->assertSessionHasNoErrors();

    $ibans = Account::pluck('iban');

    expect($ibans)->toHaveCount(2)
        ->and($ibans->unique())->toHaveCount(2)
        ->and($ibans->every(fn (string $iban): bool => strlen($iban) === 24 && PakistanIban::isValid($iban)))->toBeTrue()
        ->and($ibans->every(fn (string $iban): bool => str_contains(substr($iban, 4, 4), 'BAJK')))->toBeTrue();
});

test('the IBAN check digits detect a wrong number', function () {
    $iban = PakistanIban::make(PakistanIban::accountNumber('0001', 7));

    expect(PakistanIban::isValid($iban))->toBeTrue()
        ->and(PakistanIban::isValid(substr($iban, 0, 23).'9'))->toBeFalse();
});

// ---------------------------------------------------------------- #166

test('only a draft or rejected request can be deleted, and its customer and account go with it', function () {
    $submitted = aoiStart($this);
    $submitted->update(['status' => 'submitted']);

    $this->actingAs($this->admin)->delete(route('account-openings.destroy', $submitted))->assertSessionHas('error');
    expect(AccountOpeningRequest::count())->toBe(1);

    $draft = aoiStart($this);
    aoiAccount($this, $draft)->assertSessionHasNoErrors();
    $customerId = $draft->fresh()->customer_id;
    $accountId = $draft->fresh()->account_id;

    $this->actingAs($this->admin)->delete(route('account-openings.destroy', $draft))->assertSessionHas('success');

    expect(AccountOpeningRequest::find($draft->id))->toBeNull()
        ->and(Customer::find($customerId))->toBeNull()
        ->and(Account::find($accountId))->toBeNull()
        ->and(Customer::find($submitted->customer_id))->not->toBeNull();
});

test('rejecting a request returns the customer to draft and the account to pending', function () {
    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();
    $request->fresh()->account->update(['status' => 'active']);
    $request->update(['status' => 'submitted', 'submitted_by' => $this->admin->id]);

    $checker = User::factory()->create();
    $checker->assignRole('head-office');

    app(AccountOpeningWorkflow::class)->reject($request->fresh(), $checker, 'Fix the address');

    expect($request->fresh()->status)->toBe('rejected')
        ->and($request->fresh()->customer->status)->toBe('draft')
        ->and($request->fresh()->account->status)->toBe('pending');
});

// ---------------------------------------------------------------- #168

test('clearing an address removes it from the customer', function () {
    $request = aoiStart($this);
    $base = [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id, 'document_number' => '81303-1234567-1']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
    ];

    aoiPut($this, $request, 'identification', $base + ['addresses' => [
        'permanent_residential' => ['city' => 'Muzaffarabad', 'street_area' => 'Gojra'],
        'mailing' => ['city' => 'Mirpur', 'street_area' => 'Main Road'],
    ]])->assertSessionHasNoErrors();
    expect($request->fresh()->customer->addresses)->toHaveCount(2);

    aoiPut($this, $request, 'identification', $base + ['addresses' => [
        'permanent_residential' => ['city' => 'Muzaffarabad', 'street_area' => 'Gojra'],
        'mailing' => ['city' => '', 'street_area' => ''],
    ]])->assertSessionHasNoErrors();

    expect($request->fresh()->customer->addresses()->pluck('address_type')->all())->toBe(['permanent_residential']);
});

test('the same customer cannot be entered twice with the same role', function () {
    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();
    $customerId = $request->fresh()->customer_id;

    aoiPut($this, $request, 'holders', ['holders' => [
        ['customer_id' => $customerId, 'holder_role' => 'primary', 'is_signatory' => 0],
        ['customer_id' => $customerId, 'holder_role' => 'primary', 'is_signatory' => 0],
    ]])->assertSessionHasErrors('holders.1.customer_id');
});

test('signature images are saved against the holder of the same row even when rows are reordered', function () {
    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();
    $primary = $request->fresh()->customer;
    $joint = aoiCustomer($this, 'CIF-JOINT');

    aoiPut($this, $request, 'holders', ['holders' => [
        1 => ['customer_id' => $joint->id, 'holder_role' => 'joint', 'is_signatory' => 1, 'signature_image' => aoiPng('joint-sign.png'), 'thumb_image' => aoiPng('joint-thumb.png')],
        0 => ['customer_id' => $primary->id, 'holder_role' => 'primary', 'is_signatory' => 1, 'signature_image' => aoiPng('primary-sign.png'), 'thumb_image' => aoiPng('primary-thumb.png')],
    ]])->assertSessionHasNoErrors();

    $account = $request->fresh()->account;
    $jointHolder = $account->holders()->where('customer_id', $joint->id)->firstOrFail();
    $primaryHolder = $account->holders()->where('customer_id', $primary->id)->firstOrFail();

    $stored = fn ($holder, string $column): string => Storage::disk('public')->get($holder->specimenSignatures()->first()->{$column});

    expect($stored($jointHolder, 'signature_image_path'))->toEndWith('joint-sign.png')
        ->and($stored($jointHolder, 'thumb_left_image_path'))->toEndWith('joint-thumb.png')
        ->and($stored($primaryHolder, 'signature_image_path'))->toEndWith('primary-sign.png')
        ->and($stored($primaryHolder, 'thumb_left_image_path'))->toEndWith('primary-thumb.png');
});

// ---------------------------------------------------------------- #169

test('any customer can be found by CIF number, name or CNIC, however old', function () {
    $oldest = aoiCustomer($this, 'CIF-OLDEST', now()->subYears(5)->toDateTimeString());
    $oldest->identifications()->create([
        'identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id,
        'document_number' => '81303-7777777-7',
        'is_primary' => true,
    ]);

    $rows = [];
    foreach (range(1, 205) as $number) {
        $rows[] = [
            'id' => (string) Str::uuid(), 'cif_number' => 'CIF-NEW-'.$number, 'branch_id' => $this->branch->id, 'cif_date' => now()->toDateString(),
            'customer_type' => 'individual', 'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
            'customer_category_id' => $oldest->customer_category_id, 'status' => 'draft', 'created_at' => now(), 'updated_at' => now(),
        ];
    }
    Customer::insert($rows);

    foreach (['CIF-OLDEST', 'Customer CIF-OLDEST', '7777777'] as $term) {
        $this->actingAs($this->admin)->getJson(route('account-openings.customers.search', ['q' => $term]))
            ->assertSuccessful()
            ->assertJsonPath('results.0.id', $oldest->id);
    }

    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();
    expect($this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'holders']))->assertSuccessful()->getContent())
        ->not->toContain('CIF-NEW-100');
});

test('a branch user finds only customers of their own branch', function () {
    $other = aoiCustomer($this, 'CIF-OTHER');
    $other->update(['branch_id' => $this->otherBranch->id]);
    aoiCustomer($this, 'CIF-MINE');

    $this->actingAs(aoiBranchUser($this, $this->branch))->getJson(route('account-openings.customers.search', ['q' => 'CIF-']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'results');
});

// ---------------------------------------------------------------- #170

test('a blank identification gives a clear field error', function () {
    $request = aoiStart($this);

    aoiPut($this, $request, 'identification', [
        'identifications' => [['document_number' => '']],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
    ])->assertSessionHasErrors(['identifications.0.document_number' => 'Enter at least one identification document.']);
});

test('the identification expiry must follow the issue date and an expired document needs the accepted tick', function () {
    $request = aoiStart($this);
    $row = fn (array $extra): array => [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id, 'document_number' => '81303-1234567-1', ...$extra]],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
    ];

    aoiPut($this, $request, 'identification', $row(['issue_date' => '2025-01-01', 'expiry_date' => '2024-01-01']))
        ->assertSessionHasErrors('identifications.0.expiry_date');
    aoiPut($this, $request, 'identification', $row(['expiry_date' => '2020-01-01']))
        ->assertSessionHasErrors('identifications.0.is_expired_accepted');
    aoiPut($this, $request, 'identification', $row(['expiry_date' => '2020-01-01', 'is_expired_accepted' => 1]))
        ->assertSessionHasNoErrors();
});

test('the minor tick must match the date of birth', function () {
    $request = aoiStart($this);
    $gender = Gender::code('MALE')->firstOrFail()->id;

    aoiPut($this, $request, 'cif', ['full_name' => 'Ali', 'gender_id' => $gender, 'date_of_birth' => '1990-05-04', 'is_minor' => 1, 'guardian_name' => 'Guardian'])
        ->assertSessionHasErrors('is_minor');
    aoiPut($this, $request, 'cif', ['full_name' => 'Ali', 'gender_id' => $gender, 'date_of_birth' => now()->subYears(10)->toDateString()])
        ->assertSessionHasErrors('is_minor');
    aoiPut($this, $request, 'cif', ['full_name' => 'Ali', 'gender_id' => $gender, 'date_of_birth' => now()->subYears(10)->toDateString(), 'is_minor' => 1, 'guardian_name' => 'Guardian'])
        ->assertSessionHasNoErrors();
});

test('the minimum initial deposit applies to local currency only and is checked on the account step', function () {
    config(['account_opening.minimum_initial_deposit' => 1000]);
    $request = aoiStart($this);

    aoiAccount($this, $request, ['initial_deposit' => '10'])->assertSessionHasErrors('initial_deposit');
    aoiAccount($this, $request, ['initial_deposit' => '10', 'currency_id' => Currency::code('USD')->firstOrFail()->id])
        ->assertSessionHasNoErrors();
    aoiAccount($this, $request, ['initial_deposit' => '1500'])->assertSessionHasNoErrors();

    aoiPut($this, $request, 'cdd', [
        'income_sources' => [IncomeSource::active()->forForm('individual')->firstOrFail()->id],
        'account_purposes' => [AccountPurpose::active()->forForm('individual')->firstOrFail()->id],
        'initial_deposit' => '10',
    ])->assertSessionDoesntHaveErrors('initial_deposit');
});

test('the server accepts only income sources and account purposes of the selected form', function () {
    $request = aoiStart($this);
    aoiAccount($this, $request)->assertSessionHasNoErrors();

    $entityOnlyIncome = IncomeSource::active()->forForm('entity')->whereNotIn('id', IncomeSource::active()->forForm('individual')->select('id'))->first();
    $entityOnlyPurpose = AccountPurpose::active()->forForm('entity')->whereNotIn('id', AccountPurpose::active()->forForm('individual')->select('id'))->first();

    expect($entityOnlyIncome ?? $entityOnlyPurpose)->not->toBeNull();

    aoiPut($this, $request, 'cdd', [
        'income_sources' => [$entityOnlyIncome?->id ?? IncomeSource::active()->forForm('individual')->firstOrFail()->id],
        'account_purposes' => [$entityOnlyPurpose?->id ?? AccountPurpose::active()->forForm('individual')->firstOrFail()->id],
    ])->assertSessionHasErrors();
});

test('a checker who opens a submitted request puts it under review', function () {
    $request = aoiStart($this);
    $request->update(['status' => 'submitted', 'submitted_by' => $this->admin->id]);

    $checker = User::factory()->create();
    $checker->assignRole('head-office');

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))->assertSuccessful();
    expect($request->fresh()->status)->toBe('submitted');

    $this->flushSession();
    $this->actingAs($checker)->get(route('account-openings.show', $request))->assertSuccessful();
    expect($request->fresh()->status)->toBe('under_review');
});

test('submitting needs the submit permission only, not the edit permission', function () {
    $request = aoiStart($this);
    $role = Role::firstOrCreate(['name' => 'submit-only', 'guard_name' => 'web']);
    $role->syncPermissions(['view account openings', 'submit account openings']);
    $user = User::factory()->create();
    $user->assignRole($role);
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->post(route('account-openings.submit', $request));

    expect($response->status())->not->toBe(403);
});

// ---------------------------------------------------------------- #159

test('submitting warns when no other user can approve the request', function () {
    config(['account_opening.maker_checker.enabled' => true, 'account_opening.maker_checker.allow_self_approval' => false]);
    $request = aoiStart($this);
    $workflow = app(AccountOpeningWorkflow::class);

    // Only the maker (super admin) can approve: nobody else.
    expect($workflow->hasAnotherChecker($request, $this->admin))->toBeFalse();

    $checker = User::factory()->create();
    $checker->assignRole('head-office');

    expect($workflow->hasAnotherChecker($request, $this->admin))->toBeTrue();
});
