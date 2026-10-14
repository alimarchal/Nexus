<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    $this->branch = Branch::factory()->create();
});

test('the branch details page shows the code, name, region, district and the users of the branch', function () {
    $teller = User::factory()->create(['name' => 'Branch Teller One', 'branch_id' => $this->branch->id]);
    $teller->assignRole('branch');
    User::factory()->create(['name' => 'Someone Elsewhere']);

    $this->actingAs($this->superAdmin)->get(route('branches.show', $this->branch))
        ->assertSuccessful()
        ->assertSee($this->branch->code)
        ->assertSee($this->branch->name)
        ->assertSee($this->branch->region->name)
        ->assertSee($this->branch->district->name)
        ->assertSee('Branch Teller One')
        ->assertDontSee('Someone Elsewhere');
});

test('a branch without users says so', function () {
    $this->actingAs($this->superAdmin)->get(route('branches.show', $this->branch))->assertSee('No user is posted to this branch.');
});

test('the branches list has a View button next to Edit', function () {
    $this->actingAs($this->superAdmin)->get(route('branches.index'))->assertSee(route('branches.show', $this->branch), false);
});

test('an unknown branch address is Not Found, never a server error', function () {
    $this->actingAs($this->superAdmin)->get(route('branches.show', 999999))->assertNotFound();
});

test('a user without the view branches permission cannot open the details page', function () {
    $role = Role::firstOrCreate(['name' => 'nobody-branches', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('branches.show', $this->branch))->assertForbidden();
});
