<?php

use App\Models\Aksic;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Support\OfficeAccess;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * Branch users see only their branch's AKSIC cases, region users only their
 * region's branches, head office / division / super admin see every branch.
 */
beforeEach(function (): void {
    foreach (['view aksics', 'create aksics', 'edit aksics', 'delete aksics', 'approve aksics', 'import aksics'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
    foreach (['branch', 'region', 'head-office'] as $name) {
        Role::firstOrCreate(['name' => $name])->givePermissionTo(['view aksics', 'edit aksics']);
    }

    $this->regionA = Region::create(['name' => 'Region A']);
    $this->regionB = Region::create(['name' => 'Region B']);
    $district = District::factory()->create();

    $this->branchA1 = Branch::factory()->create(['region_id' => $this->regionA->id, 'district_id' => $district->id]);
    $this->branchA2 = Branch::factory()->create(['region_id' => $this->regionA->id, 'district_id' => $district->id]);
    $this->branchB1 = Branch::factory()->create(['region_id' => $this->regionB->id, 'district_id' => $district->id]);

    $this->caseA1 = Aksic::factory()->create(['branch_id' => $this->branchA1->id, 'district_id' => $district->id]);
    $this->caseA2 = Aksic::factory()->create(['branch_id' => $this->branchA2->id, 'district_id' => $district->id]);
    $this->caseB1 = Aksic::factory()->create(['branch_id' => $this->branchB1->id, 'district_id' => $district->id]);
});

function officeUser(string $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role);

    return $user->fresh();
}

test('branch user sees only own branch cases', function (): void {
    $user = officeUser('branch', ['branch_id' => $this->branchA1->id]);

    $ids = OfficeAccess::scope(Aksic::query(), $user)->pluck('id')->all();

    expect($ids)->toBe([$this->caseA1->id]);
    $this->actingAs($user)->get(route('aksic.show', $this->caseA1))->assertOk();
    $this->actingAs($user)->get(route('aksic.show', $this->caseB1))->assertNotFound();
    $this->actingAs($user)->get(route('aksic.edit', $this->caseA2))->assertNotFound();
});

test('region user sees every branch of the region only', function (): void {
    $user = officeUser('region', ['region_id' => $this->regionA->id]);

    $ids = OfficeAccess::scope(Aksic::query(), $user)->pluck('id')->sort()->values()->all();

    expect($ids)->toBe(collect([$this->caseA1->id, $this->caseA2->id])->sort()->values()->all());
    $this->actingAs($user)->get(route('aksic.show', $this->caseB1))->assertNotFound();
});

test('head office user sees all branches', function (): void {
    $user = officeUser('head-office');

    expect(OfficeAccess::scope(Aksic::query(), $user)->count())->toBe(3);
});

test('branch role without a branch sees nothing', function (): void {
    $user = officeUser('branch');

    expect(OfficeAccess::scope(Aksic::query(), $user)->count())->toBe(0);
});

test('aksic list only shows the branch user own cases', function (): void {
    $user = officeUser('branch', ['branch_id' => $this->branchA1->id]);

    $this->actingAs($user)->get(route('aksic.index'))
        ->assertOk()
        ->assertSee($this->caseA1->name)
        ->assertDontSee($this->caseB1->name);
});
