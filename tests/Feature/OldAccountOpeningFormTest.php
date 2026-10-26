<?php

use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\User;
use App\Services\AccountOpeningWorkflow;
use App\Support\PaperForm\PaperFormSchema;
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

/**
 * Every mandatory field of a single individual account.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function completeIndividual(array $overrides = []): array
{
    return [
        'account_nature' => 'individual', 'individual_kind' => 'single',
        'title_of_account' => 'Ali Raza', 'key_word' => 'Kashmir2026',
        'mailing_tehsil_district' => 'Muzaffarabad', 'mailing_city' => 'Muzaffarabad', 'mailing_telephone' => '05822-1234',
        'a1_name' => 'Ali Raza', 'a1_parent_relation' => 'so', 'a1_parent_name' => 'Hussain Raza', 'a1_gender' => 'male',
        'a1_marital_status' => 'married', 'a1_nationality' => 'Pakistani', 'a1_country_of_residence' => 'Pakistan', 'a1_occupation' => 'private_service',
        'introducer_name' => 'Imran Khan', 'introducer_bank_branch' => 'BAJK Main Branch', 'introducer_account_no' => '0012345',
        'authorized_by_name' => 'Manager One', 'authorized_by_employee_no' => 'E-1', 'checker_name' => 'Checker One', 'checker_employee_no' => 'E-2',
        'terms_accepted' => '1',
        ...$overrides,
    ];
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
        'key_word' => 'secretword', 'son_of' => 'Someone', 'preferred_language_1' => 'Urdu',
    ])->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->not->toHaveKeys(['son_of', 'preferred_language_1'])
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

    saveOldForm($this, $request, completeIndividual())->assertSessionHasNoErrors();

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

    expect(collect($history->changes)->first(fn ($change, $label) => str_starts_with($label, 'Title of Account'))['new'])->toBe('Ali Raza')
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
            ->assertSee('Takaful')
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

test('the edit screen lists every section of the paper form in paper order', function () {
    $request = startOldForm($this);

    $html = $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))->assertSuccessful()->getContent();

    $keys = ['header', 'currency_type', 'nature', 'particulars', 'personal', 'next_of_kin', 'business', 'bajk_relationships', 'other_banks', 'wallet',
        'zakat', 'products', 'operating', 'special_instructions', 'introducer', 'terms', 'signatures', 'bank_use', 'kyc_individual', 'kyc_business',
        'recommendation', 'authorization', 'documents'];
    $positions = array_map(fn ($key) => strpos($html, 'data-section="'.$key.'"'), $keys);

    expect($positions)->each->not->toBeFalse()
        ->and($positions)->toBe(collect($positions)->sort()->values()->all());
});

test('the header starts with the branch and date of the request', function () {
    $request = startOldForm($this);

    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))
        ->assertSee('value="'.$this->branch->code.'"', false)
        ->assertSee('value="'.now()->toDateString().'"', false);
});

test('a complete individual form saves and then submits for approval', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, completeIndividual([
        'branch_code' => '0101', 'account_no' => '77-1', 'relationship_id' => 'R-9',
        'pkr_account_types' => ['pls_saving', 'other'], 'pkr_account_types_other' => 'Roshan',
        'fcy_account_types' => ['term'], 'currency' => 'jpy',
        'email' => 'ali@example.com', 'mobile' => '0300-1234567', 'a1_dob' => '1990-05-01', 'a1_id_type' => 'cnic', 'a1_id_number' => '82101-1111111-1',
        'nok_name' => 'Sara Raza', 'nok_relationship' => 'Wife',
        'card_type' => 'gold', 'name_on_card' => 'ALI RAZA', 'zakat_deduction' => 'yes',
        'signing_authority' => 'other', 'signing_authority_other' => 'Wife on death', 'special_instructions' => 'Call before 5pm',
        'signatories' => [['name' => 'Ali Raza', 'signed' => '1', 'stamped' => '0']],
        'special_category' => ['illiterate'], 'kyc_customer_type' => 'referred', 'kyc_referred_by' => 'Imran',
        'kyc_source_of_funds' => ['family_business'], 'kyc_transaction_mode' => ['cash', 'other'], 'kyc_transaction_mode_other' => 'Hawala',
        'kyc_purpose' => ['personal_saving'], 'initial_deposit' => '5000',
        'recommendation' => [['name' => 'Staff One', 'employee_no' => 'E-5', 'signed' => '1']],
        'docs_individual' => ['d1', 'd2'],
    ]))->assertSessionHasNoErrors();

    $data = $request->fresh()->paperForm->data;

    expect($data)->toMatchArray([
        'currency' => 'jpy', 'fcy_account_types' => ['term'], 'pkr_account_types_other' => 'Roshan', 'signing_authority_other' => 'Wife on death',
        'kyc_transaction_mode_other' => 'Hawala', 'docs_individual' => ['d1', 'd2'], 'terms_accepted' => true,
    ])->and($data['signatories'][0])->toMatchArray(['name' => 'Ali Raza', 'signed' => true, 'stamped' => false]);

    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('success');
    expect($request->fresh()->status)->toBe('submitted');

    $this->actingAs($this->admin)->get(route('account-openings.print', $request))
        ->assertSee('Illiterate')->assertSee('Roshan')->assertSee('Hawala')->assertSee('Documents/Papers to be Obtained')
        ->assertSee('Attested copy of CNIC/Passport');
});

test('the Other specify text is kept only while Other is chosen', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'a1_occupation' => 'student', 'a1_occupation_other' => 'stale text'])
        ->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data['a1_occupation_other'])->toBeNull();

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'a1_occupation' => 'other', 'a1_occupation_other' => 'Shopkeeper'])
        ->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data['a1_occupation_other'])->toBe('Shopkeeper');
});

test('a joint account needs applicant 2 and a single account ignores applicant 2', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, completeIndividual(['a2_name' => 'Ignored', 'a2_gender' => 'female']))->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data)->not->toHaveKey('a2_name');

    saveOldForm($this, $request, completeIndividual(['individual_kind' => 'joint']))->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->get(route('account-openings.show', $request))->assertSee('Applicant 2: Name');

    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('error');
    expect($request->fresh()->status)->toBe('draft');
});

test('the business sections and mandatory fields apply to business accounts only', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'business', 'business_kind' => 'sole_proprietorship', 'title_of_account' => 'Raza Traders', 'business_name' => 'Raza Traders',
        'nature_of_business' => 'import_export', 'business_cnic' => '82101-2222222-2', 'a1_name' => 'Ignored', 'nok_name' => 'Ignored'])
        ->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->toMatchArray(['business_name' => 'Raza Traders', 'business_cnic' => '82101-2222222-2'])
        ->not->toHaveKeys(['a1_name', 'nok_name']);

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSee('Particulars of Account (Business Account)')
        ->assertSee('For Bank Use Only: KYC (Business Account)')
        ->assertDontSee('Next of Kin')
        ->assertSee('Office E-mail')->assertSee('Office Fax');
});

test('the documents checklist follows the nature of account', function () {
    $request = startOldForm($this);

    $checklists = [
        ['individual', null, 'Attested copy of CNIC/Passport', 'Partnership deed signed by all partners'],
        ['business', 'partnership', 'Partnership deed signed by all partners', 'Board resolution'],
        ['business', 'joint_stock', 'Certificate of Commencement of Business', 'Partnership deed signed by all partners'],
        ['business', 'sole_proprietorship', 'NTN/Sales Tax Registration certificate (optional)', 'Board resolution'],
        ['business', 'association', 'Instrument of Trust', 'Board resolution'],
        ['business', 'foreign_mission', 'Documents as per the Foreign Exchange manual', 'Instrument of Trust'],
        ['business', 'other', 'Letter of Administration or Probate', 'Instrument of Trust'],
    ];

    foreach ($checklists as [$nature, $kind, $shown, $hidden]) {
        saveOldForm($this, $request, array_filter(['account_nature' => $nature, 'individual_kind' => 'single', 'business_kind' => $kind]))->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->get(route('account-openings.print', $request))->assertSee($shown)->assertDontSee($hidden);
    }
});

test('the BAJK Wallet card applies to individual and sole proprietorship accounts only', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'business', 'business_kind' => 'partnership', 'card_type' => 'gold', 'name_on_card' => 'X'])->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data)->not->toHaveKeys(['card_type', 'name_on_card']);

    saveOldForm($this, $request, ['account_nature' => 'business', 'business_kind' => 'sole_proprietorship', 'card_type' => 'silver', 'name_on_card' => 'RAZA TRADERS'])->assertSessionHasNoErrors();
    expect($request->fresh()->paperForm->data)->toMatchArray(['card_type' => 'silver', 'name_on_card' => 'RAZA TRADERS']);
});

test('field formats are validated', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, completeIndividual(['email' => 'not-an-email', 'a1_dob' => now()->addDay()->toDateString(), 'card_type' => 'platinum', 'currency' => 'yen']))
        ->assertSessionHasErrors(['data.email', 'data.a1_dob', 'data.card_type', 'data.currency']);
});

test('the terms and conditions must be accepted before submitting, and the official text comes from the config files', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, completeIndividual(['terms_accepted' => '0']))->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('error');
    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))->assertSee('has not been loaded yet');

    config(['paper_form.terms' => ['The customer shall keep the key word secret.', 'The bank may close dormant accounts.'], 'paper_form.indemnity' => 'I indemnify the Bank.']);

    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))
        ->assertSee('The customer shall keep the key word secret.')->assertSee('I indemnify the Bank.')->assertDontSee('has not been loaded yet');
    $this->actingAs($this->admin)->get(route('account-openings.print', $request))->assertSee('The bank may close dormant accounts.');

    saveOldForm($this, $request, completeIndividual())->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('success');
});

test('an approved old form is locked and cannot be edited', function () {
    $request = startOldForm($this);
    saveOldForm($this, $request, completeIndividual())->assertSessionHasNoErrors();
    $request->update(['status' => 'approved', 'locked_at' => now()]);

    $this->actingAs($this->admin)->get(route('account-openings.paper.edit', $request))->assertRedirect(route('account-openings.show', $request));
    saveOldForm($this, $request, completeIndividual(['title_of_account' => 'Changed']))->assertRedirect(route('account-openings.show', $request));

    expect($request->fresh()->paperForm->display_name)->toBe('Ali Raza');
});

test('a stale answer left in a hidden field does not switch other fields on', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'individual', 'individual_kind' => 'single', 'business_kind' => 'partnership', 'partnership_kind' => 'registered', 'docs_partnership' => ['d1']])
        ->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->not->toHaveKeys(['business_kind', 'partnership_kind', 'docs_partnership']);
});

test('no radio group name is used in two sections, because a browser treats them as one group', function () {
    $radios = collect(PaperFormSchema::sections())
        ->flatMap(fn ($section) => collect($section['fields'])->where('type', 'radio')->pluck('name'));

    expect($radios->duplicates()->all())->toBe([]);
});

test('the business hold mail and customer type are saved under their own names', function () {
    $request = startOldForm($this);

    saveOldForm($this, $request, ['account_nature' => 'business', 'business_hold_mail' => 'yes', 'kyc_business_customer_type' => 'referred', 'kyc_business_referred_by' => 'Imran'])
        ->assertSessionHasNoErrors();

    expect($request->fresh()->paperForm->data)->toMatchArray(['business_hold_mail' => 'yes', 'kyc_business_customer_type' => 'referred', 'kyc_business_referred_by' => 'Imran']);
});

test('the form type of an old request follows its nature of account', function () {
    $request = startOldForm($this);
    expect($request->aof_form_type)->toBe(AccountOpeningRequest::FORM_INDIVIDUAL);

    saveOldForm($this, $request, ['account_nature' => 'business', 'business_kind' => 'partnership', 'partnership_kind' => 'registered'])->assertSessionHasNoErrors();
    expect($request->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_ENTITY);

    saveOldForm($this, $request, completeIndividual())->assertSessionHasNoErrors();
    expect($request->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_INDIVIDUAL);
});

test('the list filter by form type shows old and new requests of that type', function () {
    $business = startOldForm($this);
    saveOldForm($this, $business, ['account_nature' => 'business', 'business_kind' => 'joint_stock', 'company_kind' => 'private']);
    $individual = startOldForm($this);
    saveOldForm($this, $individual, completeIndividual());

    $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['aof_form_type' => AccountOpeningRequest::FORM_ENTITY]]))
        ->assertSuccessful()->assertSee($business->request_number)->assertDontSee($individual->request_number);
    $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL]]))
        ->assertSuccessful()->assertSee($individual->request_number)->assertDontSee($business->request_number);
});

test('the migration corrects the form type of existing old business requests and leaves the rest alone', function () {
    $business = startOldForm($this);
    $individual = startOldForm($this);
    $empty = startOldForm($this);
    $business->paperForm->update(['data' => ['account_nature' => 'business']]);
    $individual->paperForm->update(['data' => ['account_nature' => 'individual']]);
    AccountOpeningRequest::whereKey($individual->id)->update(['aof_form_type' => AccountOpeningRequest::FORM_ENTITY]);
    AccountOpeningRequest::whereKey($business->id)->update(['aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL]);

    (require database_path('migrations/2026_10_26_120000_set_old_account_opening_form_type_from_nature_of_account.php'))->up();

    expect($business->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_ENTITY)
        ->and($individual->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_INDIVIDUAL)
        ->and($empty->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_INDIVIDUAL);
});

/**
 * Save a complete old individual form and take it through submit and approve.
 *
 * @param  array<string, mixed>  $data
 */
function approveOldIndividual(mixed $test, array $data = []): AccountOpeningRequest
{
    config(['account_opening.maker_checker.allow_self_approval' => true]);

    $request = startOldForm($test);
    saveOldForm($test, $request, completeIndividual([
        'account_no' => '0101-5550001', 'pkr_account_types' => ['pls_saving'], 'mobile' => '0300-1234567', 'email' => 'ali@example.com',
        'a1_dob' => '1990-05-01', 'a1_id_type' => 'cnic', 'a1_id_number' => '82101-1111111-1', 'signing_authority' => 'singly',
        'mailing_post_code' => '13100', ...$data,
    ]))->assertSessionHasNoErrors();
    $test->actingAs($test->admin)->post(route('account-openings.submit', $request))->assertSessionHas('success');
    $test->actingAs($test->admin)->post(route('account-openings.approve', $request))->assertSessionHas('success');

    return $request->fresh();
}

test('approving an old request creates the customer and the account from the paper form', function () {
    $request = approveOldIndividual($this);

    $customer = $request->customer;
    $account = $request->account;

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSee('Created from this form')->assertSee($customer->cif_number)->assertSee('0101-5550001');

    expect($customer)->not->toBeNull()
        ->and($customer->cif_number)->not->toBeEmpty()
        ->and($customer->status)->toBe('active')
        ->and($customer->individual->full_name)->toBe('Ali Raza')
        ->and($customer->individual->date_of_birth->toDateString())->toBe('1990-05-01')
        ->and($customer->identifications->first()->document_number)->toBe('82101-1111111-1')
        ->and($customer->contacts->pluck('value')->all())->toContain('0300-1234567', 'ali@example.com')
        ->and($customer->addresses->pluck('address_type')->all())->toContain('mailing')
        ->and($account->account_number)->toBe('0101-5550001')
        ->and($account->title_of_account)->toBe('Ali Raza')
        ->and($account->status)->toBe('active')
        ->and($account->product->code)->toBe('PLS')
        ->and($account->currency->code)->toBe('PKR')
        ->and($account->iban)->toStartWith('PK')
        ->and($account->holders)->toHaveCount(1)
        ->and($account->holders->first()->customer_id)->toBe($customer->id)
        ->and($account->holders->first()->holder_role)->toBe('primary');
});

test('a customer with the same CNIC is linked, not duplicated', function () {
    $first = approveOldIndividual($this);
    $second = approveOldIndividual($this, ['account_no' => '0101-5550002', 'title_of_account' => 'Ali Raza (savings)']);

    expect($second->customer_id)->toBe($first->customer_id)
        ->and($second->account_id)->not->toBe($first->account_id)
        ->and(Customer::count())->toBe(1)
        ->and(Account::count())->toBe(2);

    // The same CNIC typed without dashes is the same customer.
    $third = approveOldIndividual($this, ['account_no' => '0101-5550003', 'a1_id_number' => '8210111111111']);
    expect($third->customer_id)->toBe($first->customer_id)->and(Customer::count())->toBe(1);
});

test('a joint old request creates a customer for each applicant', function () {
    $request = approveOldIndividual($this, [
        'individual_kind' => 'joint',
        'a2_name' => 'Sara Raza', 'a2_parent_relation' => 'do', 'a2_parent_name' => 'Hussain Raza', 'a2_gender' => 'female',
        'a2_marital_status' => 'married', 'a2_nationality' => 'Pakistani', 'a2_country_of_residence' => 'Pakistan', 'a2_occupation' => 'house_wife',
        'a2_id_type' => 'cnic', 'a2_id_number' => '82101-2222222-2',
    ]);

    expect($request->account->holders)->toHaveCount(2)
        ->and($request->account->holders->pluck('holder_role')->all())->toBe(['primary', 'joint'])
        ->and($request->account->holders->map(fn ($holder) => $holder->customer->displayName())->all())->toBe(['Ali Raza', 'Sara Raza']);
});

test('a business old request creates an entity customer linked by its NTN', function () {
    config(['account_opening.maker_checker.allow_self_approval' => true]);
    $request = startOldForm($this);
    saveOldForm($this, $request, ['account_nature' => 'business', 'business_kind' => 'partnership', 'partnership_kind' => 'registered', 'business_name' => 'Kashmir Traders', 'title_of_account' => 'Kashmir Traders', 'business_ntn' => 'NTN-77', 'pkr_account_types' => ['current']]);
    expect($request->fresh()->aof_form_type)->toBe(AccountOpeningRequest::FORM_ENTITY);

    $workflow = app(AccountOpeningWorkflow::class);
    $request->forceFill(['status' => 'submitted', 'submitted_by' => $this->admin->id])->save();
    $workflow->approve($request->fresh(), $this->admin);

    $customer = $request->fresh()->customer;
    expect($customer->customer_type)->toBe(Customer::TYPE_ENTITY)
        ->and($customer->organization->business_name)->toBe('Kashmir Traders')
        ->and($customer->customerCategory->code)->toBe('PARTNERSHIP')
        ->and($request->fresh()->account->product->code)->toBe('BCA');
});

test('approving the request again after an unlock updates its account instead of adding another', function () {
    $request = approveOldIndividual($this);
    $accountId = $request->account_id;
    $customerId = $request->customer_id;

    $this->actingAs($this->admin)->post(route('account-openings.unlock', $request), ['reason' => 'Title corrected'])->assertSessionHas('success');
    saveOldForm($this, $request->fresh(), completeIndividual(['title_of_account' => 'Ali Raza Khan', 'account_no' => '0101-5550001', 'a1_id_number' => '82101-1111111-1', 'pkr_account_types' => ['pls_saving']]))->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('account-openings.submit', $request))->assertSessionHas('success');
    $this->actingAs($this->admin)->post(route('account-openings.approve', $request))->assertSessionHas('success');

    $request->refresh();
    expect($request->account_id)->toBe($accountId)->and($request->customer_id)->toBe($customerId)
        ->and($request->account->title_of_account)->toBe('Ali Raza Khan')
        ->and(Customer::count())->toBe(1)->and(Account::count())->toBe(1);
});

test('a customer created from an old request is found for the holder box of a new form', function () {
    $request = approveOldIndividual($this);

    $results = $this->actingAs($this->admin)->getJson(route('account-openings.customers.search', ['q' => '82101-1111111-1']))->assertSuccessful()->json('results');

    expect($results)->toHaveCount(1)->and($results[0]['id'])->toBe($request->customer_id)->and($results[0]['text'])->toContain('Ali Raza');
});

test('old requests are found in the list by CNIC and account number, before and after approval', function () {
    $pending = startOldForm($this);
    saveOldForm($this, $pending, completeIndividual(['account_no' => '0101-9990001', 'a1_id_number' => '82101-3333333-3']))->assertSessionHasNoErrors();
    $approved = approveOldIndividual($this);

    foreach (['82101-3333333-3', '0101-9990001'] as $term) {
        $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => $term]]))
            ->assertSuccessful()->assertSee($pending->request_number)->assertDontSee($approved->request_number);
    }
    foreach (['82101-1111111-1', '0101-5550001'] as $term) {
        $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => $term]]))
            ->assertSuccessful()->assertSee($approved->request_number)->assertDontSee($pending->request_number);
    }
});

test('the customer and account keep their status when an old request is rejected or unlocked', function () {
    $request = approveOldIndividual($this);

    $this->actingAs($this->admin)->post(route('account-openings.unlock', $request), ['reason' => 'Check'])->assertSessionHas('success');

    expect($request->fresh()->customer->status)->toBe('active')->and($request->fresh()->account->status)->toBe('active');
});
