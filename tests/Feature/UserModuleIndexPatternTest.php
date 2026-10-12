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
    $response->assertSeeText('Users');
    // Same list pattern as the Account Opening list: KPI cards, status tabs, one search box, chips, dense table.
    $response->assertSee('class="ak-kpis"', false);
    $response->assertSee('class="ak-tabs"', false);
    $response->assertSee('name="filter[search]"', false);
    $response->assertSee('id="f_role"', false);
    $response->assertSee('id="f_branch"', false);
    $response->assertSee('class="ak-dt"', false);
    $response->assertSee('Rows per page');
    $response->assertSee('Delete Candidate');
    // Delete uses the in-page confirmation (no browser confirm()).
    $response->assertSee('Delete user', false);
    $response->assertSee('This cannot be undone.', false);
    $response->assertDontSee('onsubmit="return confirm', false);
});

test('user module roles index uses shared page pattern and filters', function () {
    Role::create(['name' => 'branch-manager']);

    $response = $this->actingAs($this->user)
        ->get(route('roles.index', ['filter' => ['name' => 'branch']]));

    $response->assertSuccessful();
    $response->assertViewIs('roles.index');
    $response->assertSeeText('Roles');
    $response->assertSeeText('Add role');
    $response->assertSeeText('branch-manager');
    $response->assertDontSeeText('super-admin');
    // Same list pattern as the Users list: KPI cards, one search box, chips and a dense table.
    $response->assertSee('class="ak-kpis"', false);
    $response->assertSee('name="filter[search]"', false);
    $response->assertSee('class="ak-dt"', false);
});

test('user module permissions index uses shared page pattern and filters', function () {
    Permission::create(['name' => 'approve reports']);

    $response = $this->actingAs($this->user)
        ->get(route('permissions.index', ['filter' => ['name' => 'approve']]));

    $response->assertSuccessful();
    $response->assertViewIs('permissions.index');
    $response->assertSeeText('Permissions');
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
    $response->assertSeeText('Division Managers');
    $response->assertSeeText('Operations Lead');
    $response->assertDontSeeText('Finance Lead');
    $response->assertSee('id="filters"', false);
    $response->assertSee('class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"', false);
    $response->assertSee('open-delete-manager-modal', false);
    $response->assertSee('Delete Manager', false);
    $response->assertDontSee('onsubmit="return confirm', false);
});
