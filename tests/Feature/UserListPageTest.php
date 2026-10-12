<?php

use App\Models\Branch;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view users', 'create users', 'edit users', 'delete users', 'view roles', 'view file management systems', 'create boxes'] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }

    $this->superRole = Role::firstOrCreate(['name' => 'super-admin']);
    $this->superRole->givePermissionTo(Permission::all());
    $this->branchRole = Role::firstOrCreate(['name' => 'branch']);
    $this->branchRole->givePermissionTo(['view file management systems']);

    $this->admin = User::factory()->create(['name' => 'Admin Person']);
    $this->admin->assignRole($this->superRole);
    $this->branch = Branch::factory()->create();
});

test('the list shows user, active and super admin counts and the no-access card', function () {
    $worker = User::factory()->create(['name' => 'Branch Worker', 'branch_id' => $this->branch->id]);
    $worker->assignRole($this->branchRole);
    User::factory()->create(['name' => 'Suspended Sam', 'is_active' => 'No']);
    User::factory()->create(['name' => 'Nobody Noor']); // active, no role and no permission

    $response = $this->actingAs($this->admin)->get(route('users.index'))->assertSuccessful();

    expect($response->viewData('stats'))->toBe(['total' => 4, 'active' => 3, 'inactive' => 1, 'super_admins' => 1, 'no_access' => 1]);
    $response->assertSee('No access yet')->assertSee('Super admins')->assertSee('Suspended');
});

test('status tabs filter the list while the counts ignore the tab', function () {
    User::factory()->create(['name' => 'Active Amna']);
    User::factory()->create(['name' => 'Suspended Sam', 'is_active' => 'No']);

    $response = $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['is_active' => 'No']]))->assertSuccessful();

    $response->assertSee('Suspended Sam')->assertDontSee('Active Amna');
    expect($response->viewData('stats')['total'])->toBe(3);
});

test('one search box finds users by name or email', function () {
    User::factory()->create(['name' => 'Zainab Khan', 'email' => 'zk@example.test']);
    User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.test']);

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['search' => 'Zainab']]))
        ->assertSee('Zainab Khan')->assertDontSee('Other Person');

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['search' => 'other@']]))
        ->assertSee('Other Person')->assertDontSee('Zainab Khan');
});

test('role, branch, super admin and access filters narrow the list and show removable chips', function () {
    $worker = User::factory()->create(['name' => 'Branch Worker', 'branch_id' => $this->branch->id]);
    $worker->assignRole($this->branchRole);
    User::factory()->create(['name' => 'Nobody Noor']);

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['role' => 'branch']]))
        ->assertSee('Branch Worker')->assertDontSee('Nobody Noor')->assertSee('Remove filter Role', false);

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['branch_id' => $this->branch->id]]))
        ->assertSee('Branch Worker')->assertDontSee('Nobody Noor')->assertSee('Remove filter Branch', false);

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['access' => 'none']]))
        ->assertSee('Nobody Noor')->assertDontSee('Branch Worker');

    $this->actingAs($this->admin)->get(route('users.index', ['filter' => ['is_super_admin' => 'Yes']]))
        ->assertSee('Admin Person')->assertDontSee('Nobody Noor')->assertSee('Clear all');
});

test('rows per page falls back to 25 for an unknown value', function () {
    User::factory()->count(12)->create();

    expect($this->actingAs($this->admin)->get(route('users.index', ['per_page' => 10]))->viewData('users')->perPage())->toBe(10)
        ->and($this->actingAs($this->admin)->get(route('users.index', ['per_page' => 7]))->viewData('perPage'))->toBe(25);
});

test('bulk actions suspend and activate selected users but never yourself', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    $this->actingAs($this->admin)->post(route('users.bulk-update'), ['ids' => [$a->id, $b->id, $this->admin->id], 'action' => 'deactivate'])
        ->assertRedirect(route('users.index'));

    expect($a->fresh()->is_active)->toBe('No')->and($b->fresh()->is_active)->toBe('No')->and($this->admin->fresh()->is_active)->toBe('Yes');

    $this->actingAs($this->admin)->post(route('users.bulk-update'), ['ids' => [$a->id], 'action' => 'activate']);

    expect($a->fresh()->is_active)->toBe('Yes');
});

test('bulk delete needs the delete permission and always keeps the last super admin', function () {
    $victim = User::factory()->create();
    $editor = User::factory()->create();
    $editor->givePermissionTo('edit users');

    $this->actingAs($editor)->post(route('users.bulk-update'), ['ids' => [$victim->id], 'action' => 'delete'])->assertForbidden();
    expect(User::whereKey($victim->id)->exists())->toBeTrue();

    $deleter = User::factory()->create();
    $deleter->givePermissionTo(['edit users', 'delete users']);

    $this->actingAs($deleter)->post(route('users.bulk-update'), ['ids' => [$victim->id, $this->admin->id], 'action' => 'delete']);

    expect(User::whereKey($victim->id)->exists())->toBeFalse()->and(User::whereKey($this->admin->id)->exists())->toBeTrue();
});

test('bulk update is refused without the edit users permission', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view users');
    $target = User::factory()->create();

    $this->actingAs($viewer)->post(route('users.bulk-update'), ['ids' => [$target->id], 'action' => 'deactivate'])->assertForbidden();

    expect($target->fresh()->is_active)->toBe('Yes');
});

test('row actions follow the permissions', function () {
    $target = User::factory()->create(['name' => 'Target Tariq']);
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view users');

    $this->actingAs($viewer)->get(route('users.index'))
        ->assertSee(route('users.show', $target), false)
        ->assertDontSee(route('users.edit', $target), false)
        ->assertDontSee('Delete Target Tariq');

    $this->actingAs($this->admin)->get(route('users.index'))
        ->assertSee(route('users.edit', $target), false)
        ->assertSee('Delete Target Tariq');
});

test('the user page lists the access by module with where each permission comes from', function () {
    $worker = User::factory()->create(['name' => 'Branch Worker', 'branch_id' => $this->branch->id]);
    $worker->assignRole($this->branchRole);
    $worker->givePermissionTo('create boxes');

    $response = $this->actingAs($this->admin)->get(route('users.show', $worker))
        ->assertSuccessful()
        ->assertSee('What this user can open')
        ->assertSee('data-module="file-management"', false)
        ->assertSee('view file management systems')
        ->assertSee('create boxes')
        ->assertSee($this->branch->name)
        ->assertSee('Recent activity');

    expect($response->viewData('grants'))->toHaveCount(2);
});

test('the user page needs the view users permission', function () {
    $target = User::factory()->create();
    $nobody = User::factory()->create();

    $this->actingAs($nobody)->get(route('users.show', $target))->assertForbidden();
});

test('a super admin user page says access is unlimited', function () {
    $this->actingAs($this->admin)->get(route('users.show', $this->admin))
        ->assertSuccessful()
        ->assertSee('Everything')
        ->assertSee('Super admins can open everything');
});
