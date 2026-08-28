<?php

use App\Models\Division;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'view users']);
    Permission::firstOrCreate(['name' => 'create users']);
    Permission::firstOrCreate(['name' => 'edit users']);
    Permission::firstOrCreate(['name' => 'assign permissions']);

    $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
    $superAdmin->givePermissionTo(['view users', 'create users', 'edit users', 'assign permissions']);

    Role::firstOrCreate(['name' => 'region']);
    Role::firstOrCreate(['name' => 'division']);
    Role::firstOrCreate(['name' => 'head-office']);
    Role::firstOrCreate(['name' => 'president-office']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('a user can be created with a region and division assigned', function () {
    $region = Region::factory()->create();
    $division = Division::factory()->create();
    $regionRole = Role::where('name', 'region')->first();

    $response = $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'Region User',
        'email' => 'region-user@example.com',
        'password' => 'password123',
        'region_id' => $region->id,
        'division_id' => $division->id,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [$regionRole->id],
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'email' => 'region-user@example.com',
        'region_id' => $region->id,
        'division_id' => $division->id,
        'is_president_office' => false,
    ]);
});

test('a president office user is marked accordingly with no org unit', function () {
    $presidentOfficeRole = Role::where('name', 'president-office')->first();

    $response = $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'President Office User',
        'email' => 'president-office@example.com',
        'password' => 'password123',
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [$presidentOfficeRole->id],
        'is_president_office' => 1,
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'email' => 'president-office@example.com',
        'branch_id' => null,
        'region_id' => null,
        'division_id' => null,
        'head_office_id' => null,
        'is_president_office' => true,
    ]);
});

test('a head office user can be assigned a head office', function () {
    $headOffice = HeadOffice::factory()->create();
    $headOfficeRole = Role::where('name', 'head-office')->first();

    $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'Head Office User',
        'email' => 'head-office-user@example.com',
        'password' => 'password123',
        'head_office_id' => $headOffice->id,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [$headOfficeRole->id],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'head-office-user@example.com',
        'head_office_id' => $headOffice->id,
    ]);
});
