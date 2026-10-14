<?php

use App\Models\Box;
use App\Models\Branch;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    $this->branch = Branch::factory()->create();
    $this->branchUser = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->branchUser->assignRole('branch');
});

function actAsUser(mixed $test, User $user): mixed
{
    $test->flushSession();
    app('auth')->forgetGuards();

    return $test->actingAs($user);
}

test('the super admin gets the Create Box page with an office choice instead of a 403', function () {
    actAsUser($this, $this->superAdmin)->get(route('file-management-systems.boxes.create'))
        ->assertSuccessful()
        ->assertSee('Office this box belongs to')
        ->assertSee($this->branch->code.' - '.$this->branch->name);
});

test('the super admin can create a box for the chosen branch, region, division or head office', function () {
    $region = Region::factory()->create();
    $offices = ['branch:'.$this->branch->id => 'branch', 'region:'.$region->id => 'region'];

    foreach ($offices as $office => $type) {
        actAsUser($this, $this->superAdmin)->post(route('file-management-systems.boxes.store'), ['location' => 'Vault '.$type, 'capacity' => 50, 'office' => $office])
            ->assertSessionHasNoErrors()->assertRedirect(route('file-management-systems.boxes'));
    }

    expect(Box::where('location', 'Vault branch')->first())->boxable_type->toBe('branch')->boxable_id->toBe($this->branch->id)
        ->and(Box::where('location', 'Vault region')->first())->boxable_type->toBe('region')->boxable_id->toBe($region->id);
});

test('the super admin must choose an existing office', function () {
    actAsUser($this, $this->superAdmin)->post(route('file-management-systems.boxes.store'), ['location' => 'Vault'])->assertSessionHasErrors('office');
    actAsUser($this, $this->superAdmin)->post(route('file-management-systems.boxes.store'), ['location' => 'Vault', 'office' => 'branch:99999'])->assertStatus(422);
    actAsUser($this, $this->superAdmin)->post(route('file-management-systems.boxes.store'), ['location' => 'Vault', 'office' => 'planet:1'])->assertSessionHasErrors('office');

    expect(Box::count())->toBe(0);
});

test('a user posted to an office still creates boxes for their own office and cannot pick another', function () {
    $other = Branch::factory()->create();

    actAsUser($this, $this->branchUser)->get(route('file-management-systems.boxes.create'))->assertSuccessful()->assertDontSee('Office this box belongs to');
    actAsUser($this, $this->branchUser)->post(route('file-management-systems.boxes.store'), ['location' => 'Shelf 1', 'office' => 'branch:'.$other->id])->assertSessionHasNoErrors();

    expect(Box::firstOrFail())->boxable_id->toBe($this->branch->id);
});

test('a user with the permission but no office gets a clear message, and no button', function () {
    $role = Role::firstOrCreate(['name' => 'office-less-boxer', 'guard_name' => 'web'])->syncPermissions(['view file management systems', 'create boxes', 'manage boxes']);
    $user = User::factory()->create();
    $user->assignRole($role);

    actAsUser($this, $user)->get(route('file-management-systems.boxes'))->assertSuccessful()->assertDontSee(route('file-management-systems.boxes.create'), false)
        ->assertSee('You are not posted to a branch, region, division or head office');
    actAsUser($this, $user)->get(route('file-management-systems.boxes.create'))->assertForbidden();
    actAsUser($this, $user)->get(route('file-management-systems.index'))->assertDontSee(route('file-management-systems.boxes.create'), false);
});

test('the super admin sees the New Box button on the boxes list and the file list', function () {
    actAsUser($this, $this->superAdmin)->get(route('file-management-systems.boxes'))->assertSee(route('file-management-systems.boxes.create'), false);
    actAsUser($this, $this->superAdmin)->get(route('file-management-systems.index'))->assertSee(route('file-management-systems.boxes.create'), false);
});

test('box numbers stay unique when different offices create their first box', function () {
    $other = Branch::factory()->create();
    $otherUser = User::factory()->create(['branch_id' => $other->id]);
    $otherUser->assignRole('branch');

    actAsUser($this, $this->branchUser)->post(route('file-management-systems.boxes.store'), ['location' => 'A'])->assertSessionHasNoErrors();
    actAsUser($this, $otherUser)->post(route('file-management-systems.boxes.store'), ['location' => 'B'])->assertSessionHasNoErrors();
    actAsUser($this, $this->superAdmin)->post(route('file-management-systems.boxes.store'), ['location' => 'C', 'office' => 'branch:'.$this->branch->id])->assertSessionHasNoErrors();

    expect(Box::pluck('box_number')->sort()->values()->all())->toBe(['BOX-'.now()->year.'-001', 'BOX-'.now()->year.'-002', 'BOX-'.now()->year.'-003']);
});
