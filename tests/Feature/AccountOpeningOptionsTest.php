<?php

use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\Branch;
use App\Models\CardType;
use App\Models\Currency;
use App\Models\CustomerCategory;
use App\Models\IncomeSource;
use App\Models\OperatingInstruction;
use App\Models\SpecialCategory;
use App\Models\StatementDeliveryMode;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['view account openings', 'create account openings', 'edit account openings', 'delete account openings', 'approve account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->branch = Branch::factory()->create();
});

function startIndividualAof(mixed $test): AccountOpeningRequest
{
    $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ])->assertRedirect();

    return AccountOpeningRequest::latest('id')->firstOrFail();
}

test('the options the paper form has but the system lacked are seeded', function () {
    expect(Currency::code('JPY')->exists())->toBeTrue()
        ->and(AccountProduct::code('TERM_FCY')->first())->toMatchArray(['product_class' => 'term', 'currency_type' => 'foreign'])
        ->and(AccountProduct::code('BMBA_FCY')->first())->toMatchArray(['product_class' => 'saving', 'currency_type' => 'foreign'])
        ->and(CardType::code('BAJK_SILVER')->exists())->toBeTrue()
        ->and(CardType::code('BAJK_GOLD')->exists())->toBeTrue()
        ->and(SpecialCategory::code('ILLITERATE')->exists())->toBeTrue()
        ->and(IncomeSource::code('FAMILY_BUSINESS')->exists())->toBeTrue()
        ->and(StatementDeliveryMode::code('HOLD_MAIL')->first()->available_for)->toBe('both');
});

test('seeding the options a second time does not duplicate them', function () {
    $before = [Currency::count(), AccountProduct::count(), CardType::count(), SpecialCategory::count(), IncomeSource::count()];

    $this->seed(BajkAccountOpeningSeeder::class);

    expect([Currency::count(), AccountProduct::count(), CardType::count(), SpecialCategory::count(), IncomeSource::count()])->toBe($before);
});

test('an individual account can be saved as a foreign currency term account in yen with hold mail', function () {
    $aof = startIndividualAof($this);

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$aof, 'account']), [
            'title_of_account' => 'Ali Raza',
            'opening_date' => now()->toDateString(),
            'account_product_id' => AccountProduct::code('TERM_FCY')->firstOrFail()->id,
            'currency_id' => Currency::code('JPY')->firstOrFail()->id,
            'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
            'statement_delivery_mode_id' => StatementDeliveryMode::code('HOLD_MAIL')->firstOrFail()->id,
            'terms_and_conditions_accepted' => 1,
        ])
        ->assertSessionHasNoErrors();

    $account = $aof->fresh()->account;

    expect($account->account_class)->toBe('term')
        ->and($account->currency->code)->toBe('JPY')
        ->and($account->statementDeliveryMode->code)->toBe('HOLD_MAIL');
});

test('a rupee account can take the gold debit card', function () {
    $aof = startIndividualAof($this);

    $this->actingAs($this->admin)
        ->put(route('account-openings.steps.update', [$aof, 'account']), [
            'title_of_account' => 'Ali Raza',
            'opening_date' => now()->toDateString(),
            'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
            'currency_id' => Currency::code('PKR')->firstOrFail()->id,
            'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
            'card_type_id' => CardType::code('BAJK_GOLD')->firstOrFail()->id,
            'name_on_card' => 'ALI RAZA',
            'terms_and_conditions_accepted' => 1,
        ])
        ->assertSessionHasNoErrors();

    expect($aof->fresh()->account->debitCards->first()->cardType->code)->toBe('BAJK_GOLD');
});

test('the wizard steps offer the new options on an individual request', function () {
    $aof = startIndividualAof($this);

    $account = $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$aof, 'account']))->assertSuccessful()->getContent();
    expect($account)->toContain('BAJK Term Account - FCY')
        ->and($account)->toContain('Bemisal Mahana Bachat Account (BMBA) - FCY')
        ->and($account)->toContain('JPY - Japanese Yen')
        ->and($account)->toContain('BAJK Silver Debit Card')
        ->and($account)->toContain('BAJK Gold Debit Card')
        ->and($account)->toContain('Hold Mail Facility');

    $this->actingAs($this->admin)->get(route('account-openings.steps.edit', [$aof, 'cif']))->assertSuccessful()->assertSee('Illiterate');
});
