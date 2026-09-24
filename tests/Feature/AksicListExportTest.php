<?php

use App\Models\Aksic;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\AksicDemoCasesSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * Demo seeder, large per-page sizes, CSV export and print header on the
 * AKSIC list (/product/aksic).
 */
beforeEach(function (): void {
    // Excel import / export are off unless .env switches them on (config/aksic.php).
    config(['aksic.excel_import' => true, 'aksic.excel_export' => true]);

    Permission::firstOrCreate(['name' => 'view aksics']);
    Role::firstOrCreate(['name' => 'head-office'])->givePermissionTo('view aksics');

    $region = Region::create(['name' => 'Region A']);
    $district = District::factory()->create(['name' => 'Muzaffarabad']);
    Branch::factory()->count(2)->create(['region_id' => $region->id, 'district_id' => $district->id]);
    AksicRule::create([
        'district_id' => $district->id, 'district_name' => 'Muzaffarabad', 'population_percentage' => 16.24,
        'proposed_beneficiaries' => 456, 'male_percentage' => 48, 'female_percentage' => 48,
        'special_person_percentage' => 2, 'transgender_percentage' => 2,
        'requires_site_visit' => true, 'requires_business_nature' => true, 'is_active' => true,
    ]);
    $parent = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    AksicBusinessCategory::create(['name' => 'Grocery', 'parent_id' => $parent->id]);

    $this->user = User::factory()->create();
    $this->user->assignRole('head-office');
});

test('demo seeder creates cases with schedules and can remove them again', function (): void {
    putenv('AKSIC_DEMO_COUNT=40');
    $this->seed(AksicDemoCasesSeeder::class);

    expect(Aksic::where('application_no', 'like', 'DEMO-%')->count())->toBe(40);
    $approved = Aksic::where('status', 'Approved')->first();
    expect($approved)->not->toBeNull();
    expect($approved->amortizations()->count())->toBe(61);
    expect((float) $approved->total_interest)->toBeGreaterThan(0);
    // Demo data is complete per the rules, so every pending case can be approved.
    expect(Aksic::where('status', 'Pending')->get()->every(fn (Aksic $a) => $a->approvalBlockers() === []))->toBeTrue();
    expect(Aksic::whereNull('site_visit_date')->orWhere('consent_entry', '!=', 'Yes')->count())->toBe(0);

    putenv('AKSIC_DEMO_COUNT=0');
    $this->seed(AksicDemoCasesSeeder::class);
    expect(Aksic::count())->toBe(0);
    putenv('AKSIC_DEMO_COUNT');
});

test('list shows 300 rows per page and the print header with filters', function (): void {
    putenv('AKSIC_DEMO_COUNT=320');
    $this->seed(AksicDemoCasesSeeder::class);
    putenv('AKSIC_DEMO_COUNT');

    $response = $this->actingAs($this->user)->get(route('aksic.index', ['per_page' => 300, 'filter' => ['quota' => 'Male']]));

    $response->assertOk()
        ->assertSee('ak-print-head', false)
        ->assertSee('Quota: Male')
        ->assertSee('Export to Excel (CSV)');
    expect(substr_count($response->getContent(), 'class="ak-primary-link"'))->toBe(min(300, Aksic::where('quota', 'Male')->count()));
});

test('export streams the filtered cases as csv', function (): void {
    putenv('AKSIC_DEMO_COUNT=60');
    $this->seed(AksicDemoCasesSeeder::class);
    putenv('AKSIC_DEMO_COUNT');

    $response = $this->actingAs($this->user)->get(route('aksic.export', ['filter' => ['schedule' => 'generated']]));
    $response->assertOk();
    $csv = $response->streamedContent();
    $lines = array_values(array_filter(explode("\n", trim($csv))));

    expect($lines[0])->toContain('Application No');
    expect(count($lines) - 1)->toBe(Aksic::where('status', 'Approved')->count());
});

test('excel import, excel export and demo data stay hidden unless switched on in .env', function (): void {
    config(['aksic.excel_import' => false, 'aksic.excel_export' => false, 'aksic.demo_data' => false]);
    Permission::firstOrCreate(['name' => 'import aksics']);
    $this->user->givePermissionTo('import aksics');

    $this->actingAs($this->user)->get(route('aksic.index'))
        ->assertOk()
        ->assertDontSee('Import from Excel')
        ->assertDontSee('Export to Excel')
        ->assertDontSee('demo cases');
    $this->actingAs($this->user)->get(route('aksic.export'))->assertNotFound();
    $this->actingAs($this->user)->get(route('aksic.template'))->assertNotFound();
    $this->actingAs($this->user)->post(route('aksic.demo-data'), ['count' => 0])->assertNotFound();

    config(['aksic.excel_import' => true, 'aksic.excel_export' => true]);

    $this->actingAs($this->user)->get(route('aksic.index'))
        ->assertOk()
        ->assertSee('Import from Excel')
        ->assertSee('Export to Excel')
        ->assertDontSee('demo cases');
});
