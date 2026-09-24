<?php

use App\Models\AccountOpeningRequest;
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
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/*
 * Home dashboard: AKSIC, file management and account opening figures are
 * limited to the user's own office; "view all aksic cases" opens every
 * branch's AKSIC figures; each section also needs its dashboard permission.
 */
beforeEach(function (): void {
    // A real (serializing) store, as in production: cached figures must be plain data.
    config(['cache.default' => 'file']);
    Cache::flush();
    $sections = ['view aksic dashboard', 'view file management dashboard', 'view account opening dashboard'];
    foreach (['view dashboard', 'view aksics', OfficeAccess::VIEW_ALL_PERMISSION, 'view file management systems', 'create file management systems', 'view account openings', ...$sections] as $p) {
        Permission::firstOrCreate(['name' => $p]);
    }
    Role::firstOrCreate(['name' => 'branch'])->givePermissionTo(['view dashboard', 'view aksics', 'view file management systems', 'view account openings', ...$sections]);
    Role::firstOrCreate(['name' => 'head-office'])->givePermissionTo(['view dashboard', 'view aksics', OfficeAccess::VIEW_ALL_PERMISSION, 'view account openings', 'view aksic dashboard', 'view account opening dashboard']);

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

    $n = 0;
    foreach ([[$this->own, 'submitted'], [$this->own, 'approved'], [$this->own, 'draft'], [$this->other, 'submitted'], [$this->other, 'rejected']] as [$branch, $status]) {
        AccountOpeningRequest::query()->create([
            'request_number' => 'AOF-TEST-'.++$n, 'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
            'branch_id' => $branch->id, 'request_date' => now()->toDateString(), 'status' => $status,
        ]);
    }
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

test('account opening section shows only the branch own requests', function (): void {
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');

    $aof = app(DashboardService::class)->accountOpenings($user);

    expect($aof['totals'])->toMatchArray(['requests' => 3, 'awaiting' => 1, 'approved' => 1, 'in_progress' => 1, 'rejected' => 0, 'individual' => 3])
        ->and($aof['breakdown_by'])->toBe('category')
        ->and(array_sum($aof['monthly']['total']))->toBe(3)
        ->and($aof['monthly']['approved'][11])->toBe(1)
        ->and($aof['awaiting'])->toHaveCount(1)
        ->and($aof['awaiting'][0]['number'])->toBe('AOF-TEST-1');

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee('id="db-aof"', false)->assertSee('db-aof-status', false)->assertSee('AOF-TEST-1')->assertDontSee('AOF-TEST-4');
});

test('head office sees every branch in the account opening section', function (): void {
    $user = User::factory()->create();
    $user->assignRole('head-office');

    $aof = app(DashboardService::class)->accountOpenings($user);

    expect($aof['totals']['requests'])->toBe(5)
        ->and($aof['totals']['awaiting'])->toBe(2)
        ->and($aof['breakdown_by'])->toBe('branch');
});

test('a section is hidden when its dashboard permission is removed', function (): void {
    Role::findByName('branch')->revokePermissionTo(['view account opening dashboard', 'view aksic dashboard']);
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');

    expect(app(DashboardService::class)->accountOpenings($user))->toBeNull()
        ->and(app(DashboardService::class)->aksic($user))->toBeNull();

    // The module itself stays open: only the dashboard section is hidden.
    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertDontSee('id="db-aof"', false)->assertDontSee('id="db-aksic"', false)->assertSee('id="db-files"', false);
    $this->actingAs($user)->get(route('account-openings.index'))->assertOk();
});

test('a user with no module on the dashboard is sent to a module and sees no dashboard link', function (): void {
    Role::findByName('branch', 'web')->revokePermissionTo(['view aksics', 'view account openings']);
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');
    // Only files left: the dashboard still shows (files section).
    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('id="db-files"', false);

    Role::findByName('branch', 'web')->revokePermissionTo('view file management dashboard');
    $user = $user->fresh();

    expect(app(DashboardService::class)->isVisibleTo($user))->toBeFalse();
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('file-management-systems.index'));
    $this->actingAs($user)->get(route('file-management-systems.index'))->assertOk()
        ->assertDontSee('href="'.route('dashboard').'"', false);
});

test('a user with the permissions but no office posting gets no dashboard', function (): void {
    $user = User::factory()->create(); // branch role, but no branch set
    $user->assignRole('branch');

    expect(app(DashboardService::class)->sectionsFor($user))->toBe([]);
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('aksic.index'));
});

test('a user without any module permission lands on the profile page', function (): void {
    $user = User::factory()->create(['branch_id' => $this->own->id]);

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('profile.show'));
});

test('super admin always sees the dashboard', function (): void {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee('id="db-aksic"', false)->assertSee('id="db-aof"', false)->assertSee('id="db-files"', false)
        ->assertSee('href="'.route('dashboard').'"', false);
});

test('before the dashboard permissions are migrated a branch user with a module still gets the dashboard', function (): void {
    Permission::query()->whereIn('name', ['view aksic dashboard', 'view file management dashboard', 'view account opening dashboard'])->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $user = User::factory()->create(['branch_id' => $this->own->id]);
    $user->assignRole('branch');

    expect(app(DashboardService::class)->sectionsFor($user))->toBe(['aksic', 'files', 'account_openings']);
    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('id="db-aksic"', false);
});

test('super admin is warned about migrations that have not been run', function (): void {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    // All migrations ran in the test database: no warning.
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertDontSee('not applied yet');

    DB::table('migrations')->where('migration', '2026_09_23_000004_seed_access_permissions')->delete();

    $this->actingAs($admin)->get(route('dashboard'))->assertOk()
        ->assertSee('1 database update not applied yet')->assertSee('2026_09_23_000004_seed_access_permissions');
});
