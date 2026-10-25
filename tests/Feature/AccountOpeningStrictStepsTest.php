<?php

use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\CustomerCategory;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\OperatingInstruction;
use App\Models\User;
use App\Support\AccountOpeningSteps;
use Database\Seeders\BajkAccountOpeningSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(BajkAccountOpeningSeeder::class);
    $this->branch = Branch::factory()->create();
    $this->admin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

function startStrictAof(mixed $test): AccountOpeningRequest
{
    $response = $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ]);

    preg_match('/account-openings\/([0-9a-f-]{36})\//', $response->headers->get('Location'), $matches);

    return AccountOpeningRequest::findOrFail($matches[1]);
}

function fillCif(mixed $test, AccountOpeningRequest $request, array $extra = []): TestResponse
{
    return $test->actingAs($test->admin)->put(route('account-openings.steps.update', [$request, 'cif']), [
        'full_name' => 'Ali Raza', 'date_of_birth' => '1990-05-04', 'gender_id' => Gender::code('MALE')->firstOrFail()->id, ...$extra,
    ]);
}

test('with strict steps on, a later step stays closed until the earlier ones are complete', function () {
    config(['account_opening.strict_steps' => true]);
    $request = startStrictAof($this);

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'account']))
        ->assertRedirect(route('account-openings.steps.edit', [$request, 'cif']))
        ->assertSessionHas('error', 'Complete “Customer Information (CIF)” before opening “Account, Products & Services”.');

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'cif']))->assertSuccessful();
});

test('with strict steps on, saving a later step directly is refused and nothing is stored', function () {
    config(['account_opening.strict_steps' => true]);
    $request = startStrictAof($this);

    $this->actingAs($this->admin)->put(route('account-openings.steps.update', [$request, 'account']), [
        'title_of_account' => 'Ali Raza', 'opening_date' => now()->toDateString(),
        'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
        'currency_id' => Currency::code('PKR')->firstOrFail()->id,
        'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
        'terms_and_conditions_accepted' => 1,
    ])->assertRedirect(route('account-openings.steps.edit', [$request, 'cif']));

    expect($request->fresh()->account)->toBeNull();
});

test('with strict steps on, completing a step opens the next one', function () {
    config(['account_opening.strict_steps' => true]);
    $request = startStrictAof($this);

    fillCif($this, $request)->assertRedirect(route('account-openings.steps.edit', [$request, 'identification']));

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'identification']))->assertSuccessful();
    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'compliance']))
        ->assertRedirect(route('account-openings.steps.edit', [$request, 'identification']));
});

test('with strict steps on, an incomplete step cannot continue but Save and exit keeps the draft', function () {
    config(['account_opening.strict_steps' => true]);
    $request = startStrictAof($this);
    fillCif($this, $request);

    // Identification is incomplete without any identification document (an address alone is not enough).
    $this->actingAs($this->admin)->put(route('account-openings.steps.update', [$request, 'identification']), [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id]],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
    ])->assertSessionHasErrors('identifications.0.document_number');

    expect($request->fresh()->customer->addresses)->toHaveCount(0);

    $this->actingAs($this->admin)->put(route('account-openings.steps.update', [$request, 'identification']), [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id]],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
        'save_and_exit' => 1,
    ])->assertRedirect(route('account-openings.show', $request));
});

test('with strict steps off, staff move between steps freely', function () {
    config(['account_opening.strict_steps' => false]);
    $request = startStrictAof($this);

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'cdd']))->assertSuccessful();
    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'account']))->assertSuccessful();
});

test('the stepper shows locked steps with a padlock and the show page lists the step-wise progress', function () {
    config(['account_opening.strict_steps' => true]);
    $request = startStrictAof($this);

    $html = $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$request, 'cif']))->assertSuccessful()->getContent();
    expect($html)->toContain('data-step-locked="identification"')->toContain('data-step-locked="cdd"')->not->toContain('data-step-locked="cif"');

    $this->actingAs($this->admin)->get(route('account-openings.show', $request))
        ->assertSuccessful()
        ->assertSee('Step-wise progress')
        ->assertSee('data-state="locked"', false)
        ->assertSee('Locked until')
        ->assertSee('Next step to fill in')
        ->assertSee('Continue: Customer Information (CIF)');
});

test('the step helper reports which step blocks which', function () {
    config(['account_opening.strict_steps' => true]);
    $progress = [
        'a' => ['label' => 'A', 'complete' => true],
        'b' => ['label' => 'B', 'complete' => false],
        'c' => ['label' => 'C', 'complete' => false],
    ];

    expect(AccountOpeningSteps::blockedBy($progress, 'a'))->toBeNull()
        ->and(AccountOpeningSteps::blockedBy($progress, 'b'))->toBeNull()
        ->and(AccountOpeningSteps::blockedBy($progress, 'c'))->toBe('b')
        ->and(AccountOpeningSteps::locked($progress))->toBe(['c' => 'b'])
        ->and(AccountOpeningSteps::next($progress))->toBe('b');

    config(['account_opening.strict_steps' => false]);

    expect(AccountOpeningSteps::locked($progress))->toBe([]);
});
