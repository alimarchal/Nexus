<?php

use App\Models\Aksic;
use App\Models\AksicRule;
use App\Models\District;
use App\Models\User;
use App\Services\AksicAmortizationScheduleGenerator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (['view aksics', 'create aksics', 'edit aksics', 'delete aksics'] as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role = Role::create(['name' => 'aksic-manager']);
    $role->givePermissionTo(['view aksics', 'create aksics', 'edit aksics', 'delete aksics']);

    $this->user = User::factory()->create();
    $this->user->assignRole($role);

    $this->district = District::factory()->create(['name' => 'Muzaffarabad']);
    AksicRule::create([
        'district_id' => $this->district->id,
        'district_name' => $this->district->name,
        'population_percentage' => 16.24,
        'proposed_beneficiaries' => 456,
        'male_percentage' => 48,
        'female_percentage' => 48,
        'special_person_percentage' => 2,
        'transgender_percentage' => 2,
        'requires_site_visit' => true,
        'requires_business_nature' => true,
        'is_active' => true,
    ]);
});

test('user can create aksic and amortization schedule is generated', function (): void {
    $payload = aksicPayload($this->district->id);

    $response = $this->actingAs($this->user)->post(route('aksic.store'), $payload);

    $aksic = Aksic::where('application_no', 'AKSIC-001')->first();

    $response->assertRedirect(route('aksic.show', $aksic));
    $this->assertDatabaseHas('aksics', [
        'application_no' => 'AKSIC-001',
        'cnic' => '12345-1234567-1',
        'total_rate' => 14.04,
    ]);
    $this->assertDatabaseCount('aksic_amortizations', 60);
    $this->assertDatabaseHas('aksic_amortizations', [
        'aksic_id' => $aksic->id,
        'installment_no' => 1,
        'due_date' => '2026-05-31 00:00:00',
        'days' => 21,
        'principal_amount_os' => '1000000.000000',
        'installment_per_month' => '16666.666666',
        'product' => '140400.000000',
        'interest_rate_per_month' => '384.657534',
        'total_interest' => '8077.808214',
    ]);
});

test('user can view aksic index and show pages', function (): void {
    $aksic = Aksic::factory()->create();
    $aksic->amortizations()->createMany(app(AksicAmortizationScheduleGenerator::class)->generate(
        '1000000',
        60,
        '2026-05-11',
        '12.00',
        '2.04',
    ));

    $this->actingAs($this->user)->get(route('aksic.index'))->assertSuccessful()->assertViewIs('aksics.index');
    $this->actingAs($this->user)->get(route('aksic.show', $aksic))->assertSuccessful()->assertViewIs('aksics.show');
});

test('user without aksic permission is forbidden', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('aksic.index'))->assertForbidden();
});

/**
 * @return array<string, string|int>
 */
function aksicPayload(int $districtId): array
{
    return [
        'application_no' => 'AKSIC-001',
        'name' => 'Test Applicant',
        'father_name' => 'Test Father',
        'cnic' => '12345-1234567-1',
        'phone' => '03001234567',
        'business_name' => 'Test Business',
        'business_type' => 'Existing',
        'is_startup_business' => '0',
        'quota' => 'Male',
        'district_id' => $districtId,
        'status' => 'Pending',
        'principal_amount' => '1000000',
        'tenure' => 60,
        'disbursement_date' => '2026-05-11',
        'site_visit_completed' => '0',
        'kibor_rate' => '12.00',
        'spread_rate' => '2.04',
    ];
}
