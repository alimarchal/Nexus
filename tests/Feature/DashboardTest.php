<?php

use App\Models\Aksic;
use App\Models\Branch;
use App\Models\District;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\FileManagementTransfer;
use App\Models\Region;
use App\Models\User;
use App\Services\DashboardService;
use App\Support\OfficeAccess;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * Home dashboard: AKSIC and file management figures are limited to the user's
 * own office; "view all aksic cases" opens every branch's AKSIC figures.
 */
beforeEach(function (): void {
    // A real (serializing) store, as in production: cached figures must be plain data.
    config(['cache.default' => 'file']);
    Cache::flush();
    foreach (['view dashboard', 'view aksics', OfficeAccess::VIEW_ALL_PERMISSION, 'view file management systems', 'create file management systems'] as $p) {
        Permission::firstOrCreate(['name' => $p]);
    }
    Role::firstOrCreate(['name' => 'branch'])->givePermissionTo(['view dashboard', 'view aksics', 'view file management systems']);
    Role::firstOrCreate(['name' => 'head-office'])->givePermissionTo(['view dashboard', 'view aksics', OfficeAccess::VIEW_ALL_PERMISSION]);

    $region = Region::create(['name' => 'Region A']);
    $district = District::factory()->create(['name' => 'Bagh']);
    $this->own = Branch::factory()->create(['region_id' => $region->id, 'district_id' => $district->id]);
    $this->other = Branch::factory()->create(['region_id' => $region->id, 'district_id' => $district->id]);

    Aksic::factory()->count(3)->create(['branch_id' => $this->own->id, 'district_id' => $district->id, 'status' => 'Pending', 'quota' => 'Female']);
    Aksic::factory()->count(5)->create(['branch_id' => $this->other->id, 'district_id' => $district->id, 'status' => 'Pending', 'quota' => 'Male']);

    $category = FileCategory::factory()->create();
    FileManagementSystem::factory()->count(2)->create(['fileable_type' => 'branch', 'fileable_id' => $this->own->id, 'file_category_id' => $category->id]);
    $otherFile = FileManagementSystem::factory()->create(['fileable_type' => 'branch', 'fileable_id' => $this->other->id, 'file_category_id' => $category->id]);
    FileManagementTransfer::query()->create([
        'file_management_system_id' => $otherFile->id,
        'source_fileable_type' => 'branch', 'source_fileable_id' => $this->other->id,
        'destination_fileable_type' => 'branch', 'destination_fileable_id' => $this->own->id,
        'requested_by' => ($requester = User::factory()->create())->id, 'recipient_id' => $requester->id, 'reason' => 'Audit', 'status' => 'pending',
    ]);
});

test('branch user dashboard shows only own branch aksic and file figures', function (): void {
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');

    $aksic = app(DashboardService::class)->aksic($user);
    $files = app(DashboardService::class)->files($user);

    expect($aksic['totals']['cases'])->toBe(3)
        ->and($aksic['totals']['pending'])->toBe(3)
        ->and($aksic['totals']['female_share'])->toEqual(100.0)
        ->and($aksic['breakdown_by'])->toBe('category')
        ->and($aksic['budget'])->toBeNull()
        ->and(array_sum($aksic['monthly']['total']))->toBe(3)
        ->and($files['totals']['files'])->toBe(2)
        ->and($files['totals']['incoming'])->toBe(1)
        ->and($files['totals']['outgoing'])->toBe(0);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    // Second request is served from the cache.
    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('PM Youth Loan Scheme')
        ->assertSee('File management')
        ->assertSee('db-aksic-monthly', false)
        ->assertSee('db-files-category', false);

    if ($dump = getenv('DUMP_DASHBOARD_HTML')) {
        file_put_contents($dump, $this->actingAs($user)->get(route('dashboard'))->getContent());
    }
});

test('view all aksic cases permission shows every branch on the dashboard', function (): void {
    $user = User::factory()->create();
    $user->assignRole('head-office');

    $aksic = app(DashboardService::class)->aksic($user);

    expect($aksic['totals']['cases'])->toBe(8)
        ->and($aksic['breakdown_by'])->toBe('district')
        ->and($aksic['breakdown'][0])->toMatchArray(['label' => 'Bagh', 'pending' => 8]);
    // head office has no file permission here, so the files section is left out.
    expect(app(DashboardService::class)->files($user))->toBeNull();

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('id="db-files"', false);
});

test('user without aksic permission gets no aksic section', function (): void {
    Role::findByName('branch')->revokePermissionTo('view aksics');
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');

    expect(app(DashboardService::class)->aksic($user))->toBeNull();
    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('id="db-aksic"', false)->assertSee('File management');
});
