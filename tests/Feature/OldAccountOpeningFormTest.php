<?php

use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\CustomerCategory;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view account openings', 'create account openings', 'edit account openings', 'delete account openings', 'approve account openings', 'submit account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->branch = Branch::factory()->create();
});

function startOldForm(mixed $test): AccountOpeningRequest
{
    $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'form_edition' => 'old',
        'branch_id' => $test->branch->id,
        'request_date' => now()->toDateString(),
    ])->assertSessionHasNoErrors()->assertRedirect();

    return AccountOpeningRequest::latest('id')->firstOrFail();
}

function saveOldForm(mixed $test, AccountOpeningRequest $request, array $data): TestResponse
{
    return $test->actingAs($test->admin)->put(route('account-openings.paper.update', $request), ['data' => $data]);
}

test('starting an old account opening needs only the branch and date and opens the paper form', function () {
    $request = startOldForm($this);

    expect($request->form_edition)->toBe('old')
        ->and($request->customer_id)->toBeNull()
        ->and($request->paperForm)->not->toBeNull();

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'cif']))
        ->assertRedirect(route('account-openings.paper.edit', $request));

    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))
        ->assertSuccessful()
        ->assertSee('Old Account Opening Form (BAJK paper form)')
        ->assertSee('Nature of Account');
});

test('the new form cannot open the paper form screen', function () {
    $this->actingAs($this->admin)->post(route('account-openings.store'), [
        'branch_id' => $this->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ])->assertRedirect();

    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', AccountOpeningRequest::firstOrFail()))->assertNotFound();
});

test('the paper form saves the key word, languages, hold mail, supplementary card and expected balance', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, [
        'account_nature' => 'individual',
        'individual_kind' => 'joint',
        'title_of_account' => 'Ali Raza and Sara Raza',
        'key_word' => 'Kashmir2026',
        'hold_mail' => 'yes',
        'preferred_language_1' => 'Urdu',
        'preferred_language_2' => 'English',
        'supplementary_card' => 'no',
        'expected_average_balance' => '250000',
    ])->assertSessionHasNoErrors()->assertRedirect(route('account-openings.show', $request));

    $form = $request->fresh()->paperForm;

    expect($form->key_word)->toBe('Kashmir2026')
        ->and($form->display_name)->toBe('Ali Raza and Sara Raza')
        ->and($form->data)->toMatchArray([
            'hold_mail' => 'yes', 'preferred_language_1' => 'Urdu', 'preferred_language_2' => 'English',
            'supplementary_card' => 'no', 'expected_average_balance' => 250000.0,
        ])
        ->and(DB::table('account_opening_paper_forms')->value('key_word'))->not->toContain('Kashmir2026');
});

test('the key word must be at least 6 characters', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'key_word' => 'abc'])
        ->assertSessionHasErrors('data.key_word');
});

test('applicant 2 and individual sections only apply to the matching nature of account', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, [
        'account_nature' => 'individual', 'individual_kind' => 'single',
        'preferred_language_1' => 'Urdu', 'preferred_language_2' => 'English',
    ])->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->not->toHaveKey('preferred_language_2');

    saveOldForm($this, $request, [
        'account_nature' => 'business',
        'key_word' => 'secretword', 'hold_mail' => 'yes', 'preferred_language_1' => 'Urdu',
    ])->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->not->toHaveKeys(['hold_mail', 'preferred_language_1'])
        ->and($request->fresh()->paperForm->key_word)->toBeNull();
});

test('the request page lists the mandatory fields still empty and blocks submitting until they are filled', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single'])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSuccessful()
        ->assertSee('Mandatory fields still empty')
        ->assertSee('Key/Secret Word');

    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('error');
    expect($request->fresh()->status)->toBe('draft');

    saveOldForm($this, $request, [
        'account_nature' => 'individual', 'individual_kind' => 'single',
        'title_of_account' => 'Ali Raza', 'key_word' => 'Kashmir2026',
        'introducer_name' => 'Imran Khan', 'introducer_bank_branch' => 'BAJK Main Branch', 'introducer_account_no' => '0012345',
    ])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('success');
    expect($request->fresh()->status)->toBe('submitted');
});

test('the saved form is shown on the request page and print, and the key word is not printed', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, [
        'account_nature' => 'individual', 'individual_kind' => 'single', 'title_of_account' => 'Ali Raza',
        'key_word' => 'Kashmir2026', 'supplementary_card' => 'yes', 'expected_average_balance' => '1500',
    ]);

    foreach (['show', 'print'] as $page) {
        $this->actingAs($this->admin)->get(route("account-openings.$page", $request))
            ->assertSuccessful()
            ->assertSee('Ali Raza')
            ->assertSee('Supplementary Card Required')
            ->assertSee('1,500.00')
            ->assertDontSee('Kashmir2026');
    }
});

test('the history records the change without the key word', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'title_of_account' => 'Ali Raza', 'key_word' => 'Kashmir2026']);

    $history = $request->histories()->where('event', 'step_saved')->firstOrFail();

    expect($history->changes['Title of Account']['new'])->toBe('Ali Raza')
        ->and(json_encode($history->changes))->not->toContain('Kashmir2026');
});

test('the old request appears in the list under its title and can be found by it', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'title_of_account' => 'Zubair Ahmed']);

    $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => 'Zubair']]))
        ->assertSee('Zubair Ahmed')
        ->assertSee($request->request_number);
});

test('other banks, products of interest and the introducer are saved and shown', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, [
        'account_nature' => 'individual', 'individual_kind' => 'single', 'title_of_account' => 'Ali Raza',
        'other_banks' => [
            ['bank_name' => 'HBL', 'deposit' => '1', 'credit_cards' => '0', 'loans' => '1', 'other' => ''],
            ['bank_name' => 'UBL', 'deposit' => '0', 'credit_cards' => '1', 'loans' => '0', 'other' => 'Locker'],
        ],
        'other_products' => ['credit_cards', 'other'],
        'other_products_other' => 'Takaful',
        'introducer_name' => 'Imran Khan', 'introducer_cnic' => '82101-1234567-1', 'introducer_telephone' => '0300-1234567',
        'introducer_address' => 'Muzaffarabad', 'introducer_bank_branch' => 'BAJK Main Branch', 'introducer_account_no' => '0012345',
        'introducer_signed' => '1', 'introducer_known_years' => '3', 'introducer_known_months' => '6', 'introducer_verified_by' => 'Branch Manager',
    ])->assertSessionHasNoErrors();

    $data = $request->fresh()->paperForm->data;

    expect($data['other_banks'])->toHaveCount(2)
        ->and($data['other_banks'][0])->toMatchArray(['bank_name' => 'HBL', 'deposit' => true, 'credit_cards' => false, 'loans' => true])
        ->and($data['other_banks'][1])->toMatchArray(['bank_name' => 'UBL', 'credit_cards' => true, 'other' => 'Locker'])
        ->and($data['other_products'])->toBe(['credit_cards', 'other'])
        ->and($data['other_products_other'])->toBe('Takaful')
        ->and($data)->toMatchArray(['introducer_name' => 'Imran Khan', 'introducer_known_years' => 3, 'introducer_known_months' => 6, 'introducer_signed' => true]);

    foreach (['show', 'print'] as $page) {
        $this->actingAs($this->admin)->get(route("account-openings.$page", $request))
            ->assertSuccessful()
            ->assertSee('Your Relationship with Other Banks')
            ->assertSee('HBL')->assertSee('UBL')->assertSee('Locker')
            ->assertSee('Credit Cards, Other (specify) — Takaful', false)
            ->assertSee('Imran Khan')->assertSee('BAJK Main Branch');
    }
});

test('the specify box for Other products is dropped when Other is not ticked', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'other_products' => ['mortgage'], 'other_products_other' => 'stale'])
        ->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data['other_products_other'])->toBeNull();
});

test('contact persons and existing BAJK relationships belong to business accounts only', function () {
    $request = startOldForm($this);

    $business = [
        'account_nature' => 'business',
        'contact_persons' => [
            ['name' => 'Sara', 'designation' => 'Accountant', 'telephone' => '05822-1'],
            ['name' => '', 'designation' => '', 'telephone' => ''],
            ['name' => 'Bilal', 'designation' => 'Director', 'telephone' => '05822-2'],
        ],
        'bajk_relationships' => [['branch_name' => 'Main', 'branch_code' => '0101', 'account_no' => '555', 'account_title' => 'Current']],
    ];

    saveOldForm($this, $request, $business)->assertSessionHasNoErrors();
    $data = $request->fresh()->paperForm->data;

    expect($data['contact_persons'])->toHaveCount(2)
        ->and($data['contact_persons'][1]['name'])->toBe('Bilal')
        ->and($data['bajk_relationships'][0]['branch_code'])->toBe('0101');

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSee('Contact Persons')->assertSee('Accountant')->assertSee('Your Existing Relationships with BAJK');

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single'] + $business)->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data)->not->toHaveKeys(['contact_persons', 'bajk_relationships']);
});

test('no more than four contact persons and two other banks are taken', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'business', 'contact_persons' => array_fill(0, 5, ['name' => 'X'])])
        ->assertSessionHasErrors('data.contact_persons');
    saveOldForm($this, $request, ['account_nature' => 'business', 'other_banks' => array_fill(0, 3, ['bank_name' => 'X'])])
        ->assertSessionHasErrors('data.other_banks');
});

test('the introducer name, bank/branch and account number are mandatory before submitting', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'title_of_account' => 'Ali Raza', 'key_word' => 'Kashmir2026'])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSee('Mandatory fields still empty')
        ->assertSee('Bank/Branch')->assertSee('Account No.');

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'introducer_known_months' => '12'])
        ->assertSessionHasErrors('data.introducer_known_months');
});
