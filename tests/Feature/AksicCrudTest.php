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
    foreach (['view aksics', 'create aksics', 'edit aksics', 'delete aksics', 'approve aksics', 'import aksics'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $role = Role::create(['name' => 'aksic-manager']);
    $role->givePermissionTo(['view aksics', 'create aksics', 'edit aksics', 'delete aksics', 'approve aksics', 'import aksics']);

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
    ]);

    $response = $this->actingAs($this->user)->post(route('aksic.approve', $aksic), [
        'business_sub_category_id' => $subCategory->id,
    ]);

    $response->assertRedirect(route('aksic.index'));
    $aksic->refresh();

    expect($aksic->status)->toBe('Approved');
    expect($aksic->business_sub_category_id)->toBe($subCategory->id);
    expect($aksic->total_interest)->not->toBeNull();
    $this->assertDatabaseCount('aksic_amortizations', 60);
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
    $this->assertDatabaseCount('aksic_amortizations', 60);
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
    $response->assertDontSee(route('aksic.destroy', $aksic), false);
});

test('user can view organized aksic create and edit forms with searchable branch select', function (): void {
    $aksic = Aksic::factory()->create([
        'district_id' => $this->district->id,
        'business_type' => 'Existing',
        'quota' => 'Male',
    ]);

    $this->actingAs($this->user)->get(route('aksic.create'))
        ->assertSuccessful()
        ->assertSeeText('Applicant & Business')
        ->assertSeeText('Financial & Security')
        ->assertSee('id="branch_id"', false)
        ->assertSee('select2 mt-1 block w-full', false);

    $this->actingAs($this->user)->get(route('aksic.edit', $aksic))
        ->assertSuccessful()
        ->assertSeeText('Applicant & Business')
        ->assertSeeText('Financial & Security')
        ->assertSee('id="branch_id"', false)
        ->assertSee('select2 mt-1 block w-full', false);
});

test('aksic index shows generated loans as view only and pending loans with red generate action', function (): void {
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
    $response->assertSeeText('Total Interest');
    $response->assertSeeText('Loan Information');
    $response->assertSeeText('Business Category');
    $response->assertSeeText('Principal');
    $response->assertSeeText('KIBOR');
    $response->assertSeeText('Spread');
    $response->assertSeeText('Total Rate');
    $response->assertDontSee('<th class="py-2 px-2 text-left">Application No</th>', false);
    $response->assertSee(number_format((float) $generated->total_interest, 2));
    $response->assertSee('Retail', false);
    $response->assertSee('1,000,000.00', false);
    $response->assertSee(route('aksic.show', $generated), false);
    $response->assertDontSee(route('aksic.edit', $generated), false);
    $response->assertDontSee(route('aksic.approve', $generated), false);
    $response->assertSee(route('aksic.edit', $pending), false);
    $response->assertSee(route('aksic.approve', $pending), false);
    $response->assertSee('text-red-600 hover:text-red-800 hover:bg-red-100', false);
    $response->assertSee('open-import-aksic-modal', false);
    $response->assertDontSee('class="inline-flex items-center gap-2"', false);
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
