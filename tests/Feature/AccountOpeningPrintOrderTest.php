<?php

use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\CustomerCategory;
use App\Models\User;
use App\Models\ZakatExemptionReason;
use Database\Seeders\BajkAccountOpeningSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view account openings', 'create account openings', 'edit account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->branch = Branch::factory()->create();
});

/**
 * Start a request of the new form and return the section numbers of its print, in the order they are printed.
 *
 * @return array{0: AccountOpeningRequest, 1: list<string>}
 */
function printedSectionNumbers(mixed $test, string $formType, string $categoryCode, ?Closure $prepare = null): array
{
    $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => $formType,
        'customer_category_id' => CustomerCategory::code($categoryCode)->firstOrFail()->id,
        'request_date' => now()->toDateString(),
        'economic_sector_code' => $formType === AccountOpeningRequest::FORM_ENTITY ? '8411' : null,
    ])->assertSessionHasNoErrors();

    $request = AccountOpeningRequest::latest('id')->firstOrFail();

    if ($prepare) {
        $prepare($request);
    }

    $html = $test->actingAs($test->admin)->get(route('account-openings.print', $request))->assertSuccessful()->getContent();
    preg_match_all('/<h2 class="section">#(\d+)/', $html, $matches);

    return [$request, $matches[1]];
}

test('the print follows the section order of the paper form for the individual and the entity form', function (string $formType, string $category) {
    [, $numbers] = printedSectionNumbers($this, $formType, $category);

    expect($numbers)->toBe(array_values(array_unique($numbers)));
    expect(array_map('intval', $numbers))->toBe(collect($numbers)->map(fn ($n) => (int) $n)->sort()->values()->all());
    // The sections after the account block, in the order of the paper form.
    expect(array_slice($numbers, array_search('16', $numbers)))->toBe(['16', '26', '27', '28', '30', '34']);
})->with([
    'individual' => [AccountOpeningRequest::FORM_INDIVIDUAL, 'INDIVIDUAL'],
    'entity' => [AccountOpeningRequest::FORM_ENTITY, 'GOVT_INSTITUTION'],
]);

test('every section heading carries its section number', function () {
    $this->actingAs($this->admin)->post(route('account-openings.store'), [
        'branch_id' => $this->branch->id, 'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id, 'request_date' => now()->toDateString(),
    ]);

    $html = $this->actingAs($this->admin)->get(route('account-openings.print', AccountOpeningRequest::firstOrFail()))->getContent();

    expect(substr_count($html, '<h2 class="section">'))->toBe(preg_match_all('/<h2 class="section">#\d+/', $html));
});

test('the zakat exemption and the PEP sections are printed when the form holds data for them', function () {
    [, $without] = printedSectionNumbers($this, AccountOpeningRequest::FORM_INDIVIDUAL, 'INDIVIDUAL');
    expect($without)->not->toContain('10')->not->toContain('15');

    [, $with] = printedSectionNumbers($this, AccountOpeningRequest::FORM_INDIVIDUAL, 'INDIVIDUAL', function (AccountOpeningRequest $request): void {
        $request->customer->update([
            'is_zakat_exempt' => true,
            'zakat_exemption_reason_id' => ZakatExemptionReason::firstOrFail()->id,
            'is_pep' => true,
            'pep_form_attached' => true,
        ]);
    });

    expect($with)->toContain('10', '15');
    $positions = array_flip($with);
    expect($positions['10'])->toBeGreaterThan($positions['08'])->toBeLessThan($positions['12'])
        ->and($positions['15'])->toBeGreaterThan($positions['12'])->toBeLessThan($positions['16']);
});

test('the terms, indemnity and recommendation are printed in their own sections', function () {
    [$request] = printedSectionNumbers($this, AccountOpeningRequest::FORM_INDIVIDUAL, 'INDIVIDUAL', function (AccountOpeningRequest $request): void {
        $request->update(['terms_and_conditions_accepted' => true, 'indemnity_undertaking_accepted' => true, 'authorized_by_name' => 'Manager One']);
    });

    $html = $this->actingAs($this->admin)->get(route('account-openings.print', $request))->getContent();

    expect($html)->toContain('#26 &middot; Terms &amp; Conditions', '#28 &middot; Indemnity &amp; Undertaking', '#34 &middot; Recommendation');
    expect(strpos($html, 'Terms &amp; Conditions accepted'))->toBeLessThan(strpos($html, '#27 &middot;'))
        ->and(strpos($html, 'Indemnity &amp; Undertaking signed'))->toBeGreaterThan(strpos($html, '#27 &middot;'))->toBeLessThan(strpos($html, '#30 &middot;'))
        ->and(strpos($html, 'Manager One'))->toBeGreaterThan(strpos($html, '#34 &middot;'));
});
