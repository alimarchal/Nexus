<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view roles', 'create roles', 'edit roles', 'delete roles', 'view users', 'view file management systems', 'view account openings', 'create boxes'] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }

    $this->superRole = Role::firstOrCreate(['name' => 'super-admin']);
    $this->superRole->givePermissionTo(Permission::all());
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->superRole);
});

test('the roles list counts roles, permissions, users with roles and unused roles', function () {
    $used = Role::create(['name' => 'branch-checker']);
    $used->givePermissionTo('view file management systems');
    Role::create(['name' => 'spare-role']);
    User::factory()->create()->assignRole($used);

    $response = $this->actingAs($this->admin)->get(route('roles.index'))->assertSuccessful();

    expect($response->viewData('stats'))->toBe(['roles' => 3, 'permissions' => Permission::count(), 'users_with_roles' => 2, 'unused' => 1]);
    $response->assertSee('Unused roles')->assertSee('Branch Checker');
});

test('the unused roles card filters to roles nobody has', function () {
    $used = Role::create(['name' => 'branch-checker']);
    Role::create(['name' => 'spare-role']);
    User::factory()->create()->assignRole($used);

    $this->actingAs($this->admin)->get(route('roles.index', ['filter' => ['users' => 'none']]))
        ->assertSee('Spare Role')->assertDontSee('Branch Checker')->assertSee('Remove filter Users', false);
});

test('the search box finds a role and shows a removable chip', function () {
    Role::create(['name' => 'branch-checker']);
    Role::create(['name' => 'region-reviewer']);

    $this->actingAs($this->admin)->get(route('roles.index', ['filter' => ['search' => 'region']]))
        ->assertSee('Region Reviewer')->assertDontSee('Branch Checker')
        ->assertSee('Remove filter Search', false)->assertSee('Clear all');

    $this->actingAs($this->admin)->get(route('roles.index', ['filter' => ['search' => 'zzz']]))->assertSee('No roles match these filters');
});

test('roles can be sorted by user count and rows per page falls back to 25', function () {
    $busy = Role::create(['name' => 'busy-role']);
    Role::create(['name' => 'quiet-role']);
    User::factory()->count(2)->create()->each->assignRole($busy);

    $response = $this->actingAs($this->admin)->get(route('roles.index', ['sort' => '-users_count']))->assertSuccessful();
    expect($response->viewData('roles')->first()->name)->toBe('busy-role');

    expect($this->actingAs($this->admin)->get(route('roles.index', ['per_page' => 7]))->viewData('perPage'))->toBe(25)
        ->and($this->actingAs($this->admin)->get(route('roles.index', ['sort' => 'evil; drop table']))->viewData('sort'))->toBe('name');
});

test('row actions follow the permissions and super admin cannot be deleted from the list', function () {
    $role = Role::create(['name' => 'branch-checker']);
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view roles');

    $this->actingAs($viewer)->get(route('roles.index'))
        ->assertSee(route('roles.show', $role), false)
        ->assertDontSee(route('roles.edit', $role), false)
        ->assertDontSee('Delete branch-checker');

    $response = $this->actingAs($this->admin)->get(route('roles.index'))
        ->assertSee(route('roles.edit', $role), false)
        ->assertSee('Delete branch-checker')
        ->assertDontSee('Delete super-admin');
});

test('the role page shows what the role grants by module and who has it', function () {
    $role = Role::create(['name' => 'branch-checker']);
    $role->givePermissionTo(['view file management systems', 'create boxes', 'view account openings']);
    $member = User::factory()->create(['name' => 'Role Holder']);
    $member->assignRole($role);

    $response = $this->actingAs($this->admin)->get(route('roles.show', $role))
        ->assertSuccessful()
        ->assertSee('What this role grants')
        ->assertSee('data-module="file-management"', false)
        ->assertSee('data-module="account-opening"', false)
        ->assertSee('create boxes')
        ->assertSee('Role Holder')
        ->assertSee('Users with this role');

    expect($response->viewData('usersCount'))->toBe(1)->and(collect($response->viewData('modules'))->pluck('key')->all())->toBe(['account-opening', 'file-management']);
});

test('the role page needs the view roles permission', function () {
    $role = Role::create(['name' => 'branch-checker']);

    $this->actingAs(User::factory()->create())->get(route('roles.show', $role))->assertForbidden();
});

test('a role with no permissions says so', function () {
    $role = Role::create(['name' => 'empty-role']);

    $this->actingAs($this->admin)->get(route('roles.show', $role))->assertSee('This role has no permissions yet.');
});

test('deleting a role in use is refused from the list action too', function () {
    $role = Role::create(['name' => 'branch-checker']);
    User::factory()->create()->assignRole($role);

    $this->actingAs($this->admin)->delete(route('roles.destroy', $role))->assertSessionHas('error');

    expect(Role::where('name', 'branch-checker')->exists())->toBeTrue();
});
