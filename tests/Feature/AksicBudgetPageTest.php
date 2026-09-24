<?php

use App\Models\Aksic;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Services\AksicBudgetService;
use Database\Seeders\AksicDemoCasesSeeder;
use Spatie\Permission\Models\Permission;

/*
 * Markup budget page (/product/aksic-budgets/{id}) and the AKSIC rules report:
 * position figures, the three views and the print layout.
 */
beforeEach(function (): void {
    foreach (['view aksic budget', 'manage aksic budget', 'view aksics'] as $p) {
        Permission::firstOrCreate(['name' => $p]);
    }
    $region = Region::create(['name' => 'Region A']);
    foreach (['Muzaffarabad' => 60, 'Bagh' => 40] as $name => $pct) {
        $district = District::factory()->create(['name' => $name]);
        Branch::factory()->create(['region_id' => $region->id, 'district_id' => $district->id]);
        AksicRule::create([
            'district_id' => $district->id, 'district_name' => $name, 'population_percentage' => $pct,
            'proposed_beneficiaries' => 300, 'male_percentage' => 48, 'female_percentage' => 48,
            'special_person_percentage' => 2, 'transgender_percentage' => 2,
            'requires_site_visit' => true, 'requires_business_nature' => true, 'is_active' => true,
        ]);
    }
    $parent = AksicBusinessCategory::create(['name' => 'Retail', 'parent_id' => 0]);
    AksicBusinessCategory::create(['name' => 'Grocery', 'parent_id' => $parent->id]);

    putenv('AKSIC_DEMO_COUNT=40');
    $this->seed(AksicDemoCasesSeeder::class);
    putenv('AKSIC_DEMO_COUNT');

    $this->budget = app(AksicBudgetService::class)->create([
        'title' => 'FY 2026-27 Markup Budget', 'reference_no' => 'FD/AKSIC/12', 'total_amount' => 500000000,
        'existing_business_percentage' => 25, 'new_business_percentage' => 75, 'enforcement' => 'block',
    ], true);

    $this->user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'view reports']);
    $this->user->givePermissionTo(['view aksic budget', 'manage aksic budget', 'view aksics', 'view reports']);
});

test('budget page shows the position with all three views ready for print', function (): void {
    $used = (float) Aksic::where('status', 'Approved')->sum('total_interest');

    $html = $this->actingAs($this->user)->get(route('aksic-budgets.show', $this->budget))
        ->assertOk()
        ->assertSee('Budget summary')
        ->assertSee('District-wise position')
        ->assertSee('Gender-wise position')
        ->assertSee('Existing / New business position')
        ->assertSee('Print full report')
        ->assertSee('Prepared by')
        ->getContent();

    if ($dump = getenv('DUMP_BUDGET_HTML')) {
        file_put_contents($dump, $html);
    }

    // District figures are rounded to paisa before totalling, so allow the last digit.
    expect($html)->toContain('Rs '.substr(number_format($used, 2), 0, -1));

    // Only the open view is shown on screen; the other two are print-all only.
    expect(substr_count($html, 'bgt-view bgt-alt'))->toBe(2);
    expect($html)->toContain('print-color-adjust: exact');

});

test('rules report totals match the cases', function (): void {
    $html = $this->actingAs($this->user)->get(route('reports.aksic-rules-report'))->assertOk()->getContent();

    expect($html)->toContain('Muzaffarabad')->toContain('Bagh')->toContain('Applications by quota');

    // Applications = all cases; loans done and amounts = approved cases only.
    $this->actingAs($this->user)->get(route('reports.aksic-rules-report'))
        ->assertViewHas('totals', fn (array $totals) => $totals['applications'] === Aksic::count()
            && $totals['loans_done'] === Aksic::where('status', 'Approved')->count()
            && $totals['pending'] === Aksic::where('status', 'Pending')->count()
            && abs($totals['principal_amount'] - (float) Aksic::where('status', 'Approved')->sum('principal_amount')) < 0.01
            && abs($totals['interest_amount'] - (float) Aksic::where('status', 'Approved')->sum('total_interest')) < 0.05);

    if ($dump = getenv('DUMP_REPORT_HTML')) {
        file_put_contents($dump, $html);
    }
});
