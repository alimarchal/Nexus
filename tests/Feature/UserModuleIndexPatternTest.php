<?php

use App\Models\Division;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'view users']);
    Permission::firstOrCreate(['name' => 'create users']);
    Permission::firstOrCreate(['name' => 'edit users']);
    Permission::firstOrCreate(['name' => 'delete users']);
    Permission::firstOrCreate(['name' => 'view roles']);
    Permission::firstOrCreate(['name' => 'view permissions']);
    Permission::firstOrCreate(['name' => 'view managers']);
    Permission::firstOrCreate(['name' => 'delete managers']);

    $role = Role::create(['name' => 'super-admin']);
    $role->givePermissionTo(['view users', 'create users', 'edit users', 'delete users', 'view roles', 'view permissions', 'view managers', 'delete managers']);

    $this->user = User::factory()->create();
    $this->user->assignRole($role);
});

test('user module users index uses shared page pattern', function () {
    User::factory()->create(['name' => 'Delete Candidate']);

    $response = $this->actingAs($this->user)->get(route('users.index'));

    $response->assertSuccessful();
    $response->assertViewIs('users.index');
    $response->assertSeeText('Settings User-Module Users');
    $response->assertSee('id="filters"', false);
    $response->assertSee('select2/select2.min.css', false);
    $response->assertSee('select2/select2.min.js', false);
    $response->assertSee('initializeSelect2', false);
    $response->assertSee('lg:grid-cols-4', false);
    $response->assertSee('id="filter_branch_id"', false);
    $response->assertSee('class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"', false);
    $response->assertSee('data-placeholder="All Branches"', false);
    $response->assertSee('data-placeholder="All Roles"', false);
    $response->assertSee('data-placeholder="All Statuses"', false);
    $response->assertSee('open-delete-user-modal', false);
    $response->assertSee('Delete User', false);
    $response->assertSee('This action cannot be undone.', false);
    $response->assertSee('inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150', false);
    $response->assertSee('inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150', false);
    $response->assertDontSee('onsubmit="return confirm', false);
});

test('user module roles index uses shared page pattern and filters', function () {
    Role::create(['name' => 'branch-manager']);

    $response = $this->actingAs($this->user)
        ->get(route('roles.index', ['filter' => ['name' => 'branch']]));

    $response->assertSuccessful();
    $response->assertViewIs('roles.index');
    $response->assertSeeText('Settings User-Module Roles');
    $response->assertSeeText('Add Role');
    $response->assertSeeText('branch-manager');
    $response->assertDontSeeText('super-admin');
    $response->assertSee('id="filters"', false);
});

test('user module permissions index uses shared page pattern and filters', function () {
    Permission::create(['name' => 'approve reports']);

    $response = $this->actingAs($this->user)
        ->get(route('permissions.index', ['filter' => ['name' => 'approve']]));

    $response->assertSuccessful();
    $response->assertViewIs('permissions.index');
    $response->assertSeeText('Settings User-Module Permissions');
    $response->assertSeeText('Add Permission');
    $response->assertSeeText('approve reports');
    $response->assertDontSeeText('view users');
    $response->assertSee('id="filters"', false);
});

test('user module managers index uses shared page pattern and functional filters', function () {
    $division = Division::factory()->create(['name' => 'Operations']);
    $otherDivision = Division::factory()->create(['name' => 'Finance']);

    Manager::factory()->create([
        'division_id' => $division->id,
        'title' => 'Operations Lead',
        'created_by_user_id' => $this->user->id,
    ]);

    Manager::factory()->create([
        'division_id' => $otherDivision->id,
        'title' => 'Finance Lead',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('managers.index', ['filter' => ['division_id' => $division->id]]));

    $response->assertSuccessful();
    $response->assertViewIs('managers.index');
    $response->assertSeeText('Settings User-Module Managers');
    $response->assertSeeText('Operations Lead');
    $response->assertDontSeeText('Finance Lead');
    $response->assertSee('id="filters"', false);
    $response->assertSee('class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"', false);
    $response->assertSee('open-delete-manager-modal', false);
    $response->assertSee('Delete Manager', false);
    $response->assertDontSee('onsubmit="return confirm', false);
});
