<?php

use App\Models\Aksic;
use App\Models\AksicClaim;
use App\Models\Branch;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * Claims follow the same office rules as AKSIC cases: a region user sees only
 * claim lines for their region's branches, head office sees everything.
 */
beforeEach(function (): void {
    Permission::firstOrCreate(['name' => 'view aksic claims']);
    Permission::firstOrCreate(['name' => 'view all aksic cases']);
    foreach (['region', 'head-office'] as $name) {
        Role::firstOrCreate(['name' => $name])->givePermissionTo('view aksic claims');
    }
    Role::findByName('head-office')->givePermissionTo('view all aksic cases');

    $district = District::factory()->create();
    $this->regionA = Region::create(['name' => 'Region A']);
    $this->regionB = Region::create(['name' => 'Region B']);
    $this->branchA = Branch::factory()->create(['region_id' => $this->regionA->id, 'district_id' => $district->id]);
    $this->branchB = Branch::factory()->create(['region_id' => $this->regionB->id, 'district_id' => $district->id]);
    $this->district = $district;

    $this->claimA = claimWithItems('CLM-202610-0001', [[$this->branchA, 100, 10]]);
    $this->claimB = claimWithItems('CLM-202610-0002', [[$this->branchB, 200, 20]]);
    $this->claimBoth = claimWithItems('CLM-202610-0003', [[$this->branchA, 100, 10], [$this->branchB, 300, 30]]);
});

function claimWithItems(string $no, array $lines): AksicClaim
{
    $claim = AksicClaim::create([
        'claim_no' => $no,
        'claim_date' => '2026-10-01',
        'period_from' => '2026-10-01',
        'period_to' => '2026-10-31',
        'total_loans' => count($lines),
        'total_principal_outstanding' => collect($lines)->sum(1),
        'total_markup' => collect($lines)->sum(2),
        'status' => 'Lodged',
    ]);

    foreach ($lines as [$branch, $principal, $markup]) {
        $claim->items()->create([
            'aksic_id' => Aksic::factory()->create(['branch_id' => $branch->id, 'district_id' => $branch->district_id])->id,
            'district_id' => $branch->district_id,
            'region_id' => $branch->region_id,
            'branch_id' => $branch->id,
            'installments_count' => 1,
            'principal_outstanding' => $principal,
            'markup_amount' => $markup,
        ]);
    }

    return $claim;
}

function claimUser(string $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role);

    return $user->fresh();
}

test('region user lists only claims that have lines in their region', function (): void {
    $user = claimUser('region', ['region_id' => $this->regionA->id]);

    $ids = $this->actingAs($user)->get(route('aksic-claims.index'))
        ->assertOk()->viewData('claims')->pluck('id')->all();

    expect($ids)->toContain($this->claimA->id, $this->claimBoth->id)
        ->not->toContain($this->claimB->id);
});

test('region user cannot widen the list with an office filter', function (): void {
    $user = claimUser('region', ['region_id' => $this->regionA->id]);

    $ids = $this->actingAs($user)
        ->get(route('aksic-claims.index', ['filter' => ['region_id' => $this->regionB->id]]))
        ->assertOk()->viewData('claims')->pluck('id')->all();

    expect($ids)->not->toContain($this->claimB->id);
});

test('region user cannot open another region claim by url', function (): void {
    $user = claimUser('region', ['region_id' => $this->regionA->id]);

    $this->actingAs($user)->get(route('aksic-claims.show', $this->claimB))->assertForbidden();
});

test('region user sees only own region lines and totals on a bank wide claim', function (): void {
    $user = claimUser('region', ['region_id' => $this->regionA->id]);

    $response = $this->actingAs($user)->get(route('aksic-claims.show', $this->claimBoth))->assertOk();

    $claim = $response->viewData('claim');
    expect($claim->items)->toHaveCount(1)
        ->and($claim->items->first()->branch_id)->toBe($this->branchA->id)
        ->and((float) $claim->total_markup)->toBe(10.0)
        ->and($claim->total_loans)->toBe(1);
});

test('summary only counts lines the user may see', function (): void {
    $user = claimUser('region', ['region_id' => $this->regionA->id]);

    $summary = $this->actingAs($user)->get(route('aksic-claims.index', ['group_by' => 'region']))
        ->assertOk()->viewData('summary');

    expect($summary->pluck('label')->all())->toBe(['Region A'])
        ->and($summary->sum('loans'))->toBe(2);
});

test('head office still sees every claim in full', function (): void {
    $user = claimUser('head-office');

    $ids = $this->actingAs($user)->get(route('aksic-claims.index'))->viewData('claims')->pluck('id')->all();
    expect($ids)->toContain($this->claimA->id, $this->claimB->id, $this->claimBoth->id);

    $claim = $this->actingAs($user)->get(route('aksic-claims.show', $this->claimBoth))->assertOk()->viewData('claim');
    expect($claim->items)->toHaveCount(2)->and((float) $claim->total_markup)->toBe(40.0);
});
