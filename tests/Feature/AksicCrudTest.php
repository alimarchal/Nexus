<?php

use App\Models\Aksic;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\District;
use App\Models\User;
use App\Services\AksicAmortizationScheduleGenerator;
use App\Services\AksicExcelService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Excel import / export are off unless .env switches them on (config/aksic.php).
    config(['aksic.excel_import' => true, 'aksic.excel_export' => true]);

    foreach (['view aksics', 'create aksics', 'edit aksics', 'delete aksics', 'approve aksics', 'import aksics'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    Permission::firstOrCreate(['name' => 'view all aksic cases']);
    $role = Role::create(['name' => 'aksic-manager']);
    $role->givePermissionTo(['view aksics', 'view all aksic cases', 'create aksics', 'edit aksics', 'delete aksics', 'approve aksics', 'import aksics']);

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
    $this->branch = Branch::factory()->create(['district_id' => $this->district->id]);
});

test('user can create aksic as pending without amortization schedule', function (): void {
    $payload = aksicPayload($this->district->id);

    $response = $this->actingAs($this->user)->post(route('aksic.store'), $payload);

    $aksic = Aksic::where('application_no', 'AKSIC-001')->first();

    $response->assertRedirect(route('aksic.show', $aksic));
    $this->assertDatabaseHas('aksics', [
        'application_no' => 'AKSIC-001',
        'cnic' => '12345-1234567-1',
        'total_rate' => 14.04,
        'status' => 'Pending',
        'total_interest' => null,
        'consent_entry' => 'Yes',
        'liquid_security' => 'Cash collateral',
        'personal_guarantees' => 'Two personal guarantees',
    ]);
    $this->assertDatabaseCount('aksic_amortizations', 0);
});

test('user can approve aksic and amortization schedule is generated with total interest', function (): void {
    $category = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    $subCategory = AksicBusinessCategory::create(['name' => 'Grocery', 'parent_id' => $category->id]);
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'aksic_rule_id' => AksicRule::where('district_id', $this->district->id)->value('id'),
        'business_category_id' => $category->id,
        'business_sub_category_id' => null,
        'status' => 'Pending',
        'total_interest' => null,
        'business_type' => 'Existing',
        'quota' => 'Male',
        'site_visit_completed' => true,
        'site_visit_date' => '2026-05-05',
    ]);

    $response = $this->actingAs($this->user)->post(route('aksic.approve', $aksic), [
        'business_sub_category_id' => $subCategory->id,
    ]);

    $response->assertRedirect(route('aksic.index'));
    $aksic->refresh();

    expect($aksic->status)->toBe('Approved');
    expect($aksic->business_sub_category_id)->toBe($subCategory->id);
    expect($aksic->total_interest)->not->toBeNull();
    // Row 1 is the markup-only broken period, then 60 regular instalments (Portal Change #3).
    $this->assertDatabaseCount('aksic_amortizations', 61);
    $this->assertDatabaseHas('aksic_amortizations', [
        'aksic_id' => $aksic->id,
        'installment_no' => 1,
        'due_date' => '2026-05-31 00:00:00',
        'days' => 21,
    ]);

    $firstTotalInterest = $aksic->total_interest;

    $this->actingAs($this->user)->post(route('aksic.approve', $aksic));

    $aksic->refresh();
    expect($aksic->total_interest)->toEqual($firstTotalInterest);
    // Row 1 is the markup-only broken period, then 60 regular instalments (Portal Change #3).
    $this->assertDatabaseCount('aksic_amortizations', 61);
});

test('user can update aksic without regenerating amortization schedule', function (): void {
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'aksic_rule_id' => AksicRule::where('district_id', $this->district->id)->value('id'),
        'business_type' => 'Existing',
        'quota' => 'Male',
    ]);

    $payload = aksicPayload($this->district->id);
    $payload['application_no'] = $aksic->application_no;
    $payload['cnic'] = $aksic->cnic;
    $payload['name'] = 'Updated Applicant';

    $response = $this->actingAs($this->user)->put(route('aksic.update', $aksic), $payload);

    $response->assertRedirect(route('aksic.show', $aksic));
    $aksic->refresh();

    expect($aksic->name)->toBe('Updated Applicant');
    expect($aksic->status)->toBe('Pending');
    $this->assertDatabaseCount('aksic_amortizations', 0);
});

test('generated aksic cannot be edited or deleted except by super admin', function (): void {
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'aksic_rule_id' => AksicRule::where('district_id', $this->district->id)->value('id'),
        'business_type' => 'Existing',
        'quota' => 'Male',
    ]);
    $aksic->amortizations()->createMany(app(AksicAmortizationScheduleGenerator::class)->generate(
        '1000000',
        60,
        '2026-05-11',
        '12.00',
        '2.04',
    ));

    $payload = aksicPayload($this->district->id);
    $payload['application_no'] = $aksic->application_no;
    $payload['cnic'] = $aksic->cnic;

    $this->actingAs($this->user)->get(route('aksic.edit', $aksic))->assertForbidden();
    $this->actingAs($this->user)->put(route('aksic.update', $aksic), $payload)->assertForbidden();
    $this->actingAs($this->user)->delete(route('aksic.destroy', $aksic))->assertForbidden();
    $this->assertModelExists($aksic);

    $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdminRole->givePermissionTo(collect(['view aksics', 'edit aksics', 'delete aksics'])
        ->map(fn (string $permission): Permission => Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => $superAdminRole->guard_name,
        ])));
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole($superAdminRole);

    $this->actingAs($superAdmin)->get(route('aksic.edit', $aksic))->assertSuccessful();
    $this->actingAs($superAdmin)->delete(route('aksic.destroy', $aksic))->assertRedirect(route('aksic.index'));
    $this->assertSoftDeleted($aksic);
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

test('generated aksic show page hides edit and delete for non super admin', function (): void {
    $aksic = Aksic::factory()->create();
    $aksic->amortizations()->createMany(app(AksicAmortizationScheduleGenerator::class)->generate(
        '1000000',
        60,
        '2026-05-11',
        '12.00',
        '2.04',
    ));

    $response = $this->actingAs($this->user)->get(route('aksic.show', $aksic));

    $response->assertSuccessful();
    $response->assertDontSee(route('aksic.edit', $aksic), false);
    // destroy shares the show URL, so check for the DELETE form instead.
    $response->assertDontSee('name="_method" value="DELETE"', false);
});

test('user can view organized aksic create and edit forms with searchable branch select', function (): void {
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'business_type' => 'Existing',
        'quota' => 'Male',
    ]);

    $this->actingAs($this->user)->get(route('aksic.create'))
        ->assertSuccessful()
        ->assertSeeText('1. Applicant')
        ->assertSeeText('3. Financing & Security')
        ->assertSee('id="branch_id"', false)
        ->assertSee('select2 mt-1 block w-full', false);

    $this->actingAs($this->user)->get(route('aksic.edit', $aksic))
        ->assertSuccessful()
        ->assertSeeText('1. Applicant')
        ->assertSeeText('3. Financing & Security')
        ->assertSee('id="branch_id"', false)
        ->assertSee('select2 mt-1 block w-full', false);
});

test('aksic index lists cases with view icons and leaves approval to the case page', function (): void {
    $category = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    AksicBusinessCategory::create(['name' => 'Grocery', 'parent_id' => $category->id]);
    $generated = Aksic::factory()->create([
        'business_category_id' => $category->id,
        'status' => 'Approved',
        'total_interest' => '2500.000000',
    ]);
    $generated->amortizations()->createMany(app(AksicAmortizationScheduleGenerator::class)->generate(
        '1000000',
        60,
        '2026-05-11',
        '12.00',
        '2.04',
    ));
    $pending = Aksic::factory()->create([
        'business_category_id' => $category->id,
        'status' => 'Pending',
        'total_interest' => null,
    ]);

    $response = $this->actingAs($this->user)->get(route('aksic.index'));

    $response->assertSuccessful();
    $response->assertSeeText('Markup (Rs)');
    $response->assertSeeText('Pending approval');
    $response->assertSee('1,000,000', false);
    $response->assertSee('2,500', false);
    $response->assertSee(route('aksic.show', $generated), false);
    $response->assertSee(route('aksic.show', ['aksic' => $pending, 'nav' => 'pending']), false);
    // A generated schedule locks the case for non super admins.
    $response->assertDontSee(route('aksic.edit', $generated), false);
    $response->assertSee(route('aksic.edit', $pending), false);
    // Approval happens on the case page, not from the list.
    $response->assertDontSee(route('aksic.approve', $pending), false);
    $response->assertDontSee('Classic', false);
    $response->assertSee('open-import-aksic-modal', false);
});

test('user without aksic permission is forbidden', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('aksic.index'))->assertForbidden();
});

test('user can download aksic import template', function (): void {
    $this->actingAs($this->user)
        ->get(route('aksic.template'))
        ->assertDownload('aksic_import_template.xlsx');

    $template = app(AksicExcelService::class)->createTemplate();
    $spreadsheet = (new XlsxReader)->load($template['path']);
    $headings = $spreadsheet->getActiveSheet()->rangeToArray('A1:V1')[0];

    expect($headings)
        ->toContain('Consent Entry')
        ->toContain('Liquid Security')
        ->not->toContain('Sanction Date')
        ->not->toContain('New Business')
        ->not->toContain('Startup Business');

    @unlink($template['path']);
});

test('user can import new aksic rows and duplicate rows are skipped', function (): void {
    $category = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    Aksic::factory()->create([
        'application_no' => 'AKSIC-DUP-001',
        'cnic' => '11111-1111111-1',
        'district_id' => $this->district->id,
        'business_type' => 'Existing',
        'quota' => 'Male',
    ]);

    $file = aksicImportFile([
        [
            'AKSIC-DUP-001',
            '22222-2222222-2',
            'Duplicate Applicant',
            'Duplicate Father',
            '03001111111',
            'Duplicate Business',
            'Existing',
            'Male',
            '',
            $category->name,
            $this->district->name,
            $this->branch->code,
            1000000,
            60,
            '2026-05-11',
            12,
            2.04,
            14.04,
            'No',
            '',
            '',
            '',
        ],
        [
            'AKSIC-IMPORT-001',
            '33333-3333333-3',
            'Imported Applicant',
            'Imported Father',
            '03002222222',
            'Imported Business',
            'New',
            'Male',
            '',
            $category->name,
            $this->district->name,
            $this->branch->code,
            1000000,
            60,
            '2026-05-11',
            12,
            2.04,
            14.04,
            'Yes',
            '2026-05-12',
            'Imported liquid security',
            'Imported guarantee',
        ],
    ]);

    $response = $this->actingAs($this->user)->post(route('aksic.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('aksic.index'));
    $this->assertDatabaseHas('aksics', [
        'application_no' => 'AKSIC-IMPORT-001',
        'cnic' => '33333-3333333-3',
        'status' => 'Pending',
        'total_interest' => null,
        'is_startup_business' => true,
        'consent_entry' => 'Yes',
        'liquid_security' => 'Imported liquid security',
        'personal_guarantees' => 'Imported guarantee',
    ]);
    $this->assertDatabaseMissing('aksics', [
        'application_no' => 'AKSIC-DUP-001',
        'cnic' => '22222-2222222-2',
    ]);
    $this->assertDatabaseCount('aksic_amortizations', 0);
    expect(session('import_errors')[0])->toContain('skipped');
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
        'quota' => 'Male',
        'district_id' => $districtId,
        'status' => 'Pending',
        'principal_amount' => '1000000',
        'tenure' => 60,
        'disbursement_date' => '2026-05-11',
        'site_visit_completed' => '0',
        'consent_entry' => 'Yes',
        'consent_date' => '2026-05-12',
        'liquid_security' => 'Cash collateral',
        'personal_guarantees' => 'Two personal guarantees',
        'kibor_rate' => '12.00',
        'spread_rate' => '2.04',
    ];
}

/**
 * @param  array<int, array<int, mixed>>  $rows
 */
function aksicImportFile(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([
        [
            'Application No',
            'CNIC',
            'Applicant Name',
            'Father Name',
            'Phone',
            'Business Name',
            'Business Type',
            'Quota',
            'Gender',
            'Business Category',
            'District',
            'Branch',
            'Principal Amount',
            'Tenure',
            'Disbursement Date',
            'KIBOR Rate',
            'Spread Rate',
            'Total Rate',
            'Consent Entry',
            'Consent Date',
            'Liquid Security',
            'Personal Guarantees',
            'Account No',
            'Mortgage',
            'Site Visit Completed',
            'Site Visit Date',
        ],
        ...$rows,
    ]);

    $path = tempnam(sys_get_temp_dir(), 'aksic-import').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'aksic-import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

test('create and edit forms use calendar date inputs and save the picked date', function (): void {
    $this->actingAs($this->user)->get(route('aksic.create'))
        ->assertSuccessful()
        ->assertSee('type="date"', false)
        ->assertDontSee('dd.mm.yyyy', false);

    // The browser calendar posts Y-m-d; the list and case page show it as D.M.Y.
    $payload = array_merge(aksicPayload($this->district->id), ['disbursement_date' => '2026-06-15', 'consent_date' => '2026-06-10']);
    $this->actingAs($this->user)->post(route('aksic.store'), $payload)->assertRedirect();

    $aksic = Aksic::where('application_no', 'AKSIC-001')->firstOrFail();
    expect($aksic->disbursement_date->toDateString())->toBe('2026-06-15');

    $this->actingAs($this->user)->get(route('aksic.edit', $aksic))
        ->assertSuccessful()
        ->assertSee('value="2026-06-15"', false);
});

test('approval is refused until the case meets its district rule', function (): void {
    $category = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    $subCategory = AksicBusinessCategory::create(['name' => 'Grocery', 'parent_id' => $category->id]);
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'aksic_rule_id' => AksicRule::where('district_id', $this->district->id)->value('id'),
        'business_category_id' => $category->id,
        'status' => 'Pending',
        'total_interest' => null,
        'business_type' => 'Existing',
        'quota' => 'Male',
        'site_visit_completed' => false,
        'site_visit_date' => null,
    ]);

    expect($aksic->approvalBlockers())->toContain('Site visit must be completed and its date entered.');

    $this->actingAs($this->user)->get(route('aksic.show', $aksic))
        ->assertOk()->assertSee('Complete case to approve')->assertSee('Site visit must be completed');

    $this->actingAs($this->user)->post(route('aksic.approve', $aksic), ['business_sub_category_id' => $subCategory->id])
        ->assertSessionHas('error');
    expect($aksic->fresh()->status)->toBe('Pending');
    $this->assertDatabaseCount('aksic_amortizations', 0);

    $aksic->update(['site_visit_completed' => true, 'site_visit_date' => '2026-05-05']);
    expect($aksic->fresh()->approvalBlockers())->toBe([]);
});

test('import reads the site visit columns and stops at the district beneficiary limit', function (): void {
    $category = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    AksicRule::where('district_id', $this->district->id)->update(['proposed_beneficiaries' => 1]);
    $row = fn (string $app, string $cnic) => [
        $app, $cnic, 'Imported Applicant', 'Imported Father', '03002222222', 'Imported Business', 'New', 'Male', '',
        $category->name, $this->district->name, $this->branch->code, 1000000, 60, '2026-05-11', 12, 2.04, 14.04,
        'Yes', '2026-05-09', 'Cheques', 'Guarantee', '', '', 'Yes', '2026-05-08',
    ];

    $this->actingAs($this->user)->post(route('aksic.import'), [
        'file' => aksicImportFile([$row('AKSIC-CAP-001', '44444-4444444-3'), $row('AKSIC-CAP-002', '55555-5555555-5')]),
    ]);

    $imported = Aksic::where('application_no', 'AKSIC-CAP-001')->first();
    expect($imported)->not->toBeNull();
    expect($imported->site_visit_completed)->toBeTrue();
    expect($imported->site_visit_date->toDateString())->toBe('2026-05-08');
    expect(Aksic::where('application_no', 'AKSIC-CAP-002')->exists())->toBeFalse();
    expect(collect(session('import_errors'))->implode(' '))->toContain('quota is full');
});
