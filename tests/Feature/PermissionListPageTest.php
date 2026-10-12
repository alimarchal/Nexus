<?php

use App\Models\User;
use App\Support\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view permissions', 'create permissions', 'edit permissions', 'delete permissions', 'view users', 'view file management systems', 'create boxes', 'view account openings', 'approve account openings'] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }

    $this->superRole = Role::firstOrCreate(['name' => 'super-admin']);
    $this->superRole->givePermissionTo(Permission::all());
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->superRole);
});

test('the list counts permissions, modules, those in a role and those given to no one', function () {
    Permission::create(['name' => 'frobnicate widgets']); // in no role, no user

    $response = $this->actingAs($this->admin)->get(route('permissions.index'))->assertSuccessful();

    $stats = $response->viewData('stats');
    expect($stats['permissions'])->toBe(Permission::count())
        ->and($stats['in_roles'])->toBe(Permission::count() - 1)
        ->and($stats['orphans'])->toBe(1)
        ->and($stats['modules'])->toBe(count(collect(PermissionCatalog::build(Permission::all()))));
    $response->assertSee('Not given to anyone');
});

test('the not-given card filters to permissions nobody has', function () {
    Permission::create(['name' => 'frobnicate widgets']);

    $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['usage' => 'orphan']]))
        ->assertSee('frobnicate widgets')->assertDontSee('view file management systems')->assertSee('Remove filter Usage', false);
});

test('a module filter lists only that module and never another', function () {
    $response = $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['module' => 'file-management']]))->assertSuccessful();

    $names = $response->viewData('permissions')->pluck('name');
    expect($names->all())->toContain('view file management systems', 'create boxes')
        ->and($names->implode(' '))->not->toContain('account opening');
    $response->assertSee('Remove filter Module', false);
});

test('the module list in the filter shows every module with its count', function () {
    $response = $this->actingAs($this->admin)->get(route('permissions.index'))->assertSuccessful();

    $keys = collect($response->viewData('modules'))->pluck('key')->all();
    expect($keys)->toContain('file-management', 'account-opening', 'access');
    $response->assertSee('File management')->assertSee('Account opening (AOF)');
});

test('search and the role filter narrow the list', function () {
    $role = Role::create(['name' => 'branch-checker']);
    $role->givePermissionTo('approve account openings');

    $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['search' => 'boxes']]))
        ->assertSee('create boxes')->assertDontSee('approve account openings');

    $response = $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['role_id' => $role->id]]));
    expect($response->viewData('permissions')->pluck('name')->all())->toBe(['approve account openings']);
    $response->assertSee('Branch Checker');
});

test('sorting and rows per page are applied and unknown values fall back', function () {
    $response = $this->actingAs($this->admin)->get(route('permissions.index', ['sort' => '-name', 'per_page' => 10]))->assertSuccessful();

    expect($response->viewData('permissions')->perPage())->toBe(10)
        ->and($response->viewData('permissions')->first()->name)->toBe(Permission::orderByDesc('name')->first()->name);

    expect($this->actingAs($this->admin)->get(route('permissions.index', ['per_page' => 7]))->viewData('perPage'))->toBe(25)
        ->and($this->actingAs($this->admin)->get(route('permissions.index', ['sort' => 'evil']))->viewData('sort'))->toBe('name');
});

test('row actions follow the permissions', function () {
    $permission = Permission::where('name', 'create boxes')->first();
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view permissions');

    $this->actingAs($viewer)->get(route('permissions.index'))
        ->assertDontSee(route('permissions.edit', $permission), false)
        ->assertDontSee('Delete create boxes');

    $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['search' => 'create boxes']]))
        ->assertSee(route('permissions.edit', $permission), false)
        ->assertSee('Delete create boxes');
});

test('a critical permission cannot be deleted and an ordinary one can', function () {
    Permission::firstOrCreate(['name' => 'view roles']);
    Permission::firstOrCreate(['name' => 'edit roles']);
    $critical = Permission::where('name', 'view roles')->first();
    $ordinary = Permission::create(['name' => 'frobnicate widgets']);

    $this->actingAs($this->admin)->delete(route('permissions.destroy', $critical))->assertSessionHas('error');
    expect(Permission::whereKey($critical->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)->delete(route('permissions.destroy', $ordinary))->assertSessionHas('success');
    expect(Permission::whereKey($ordinary->id)->exists())->toBeFalse();
});

test('the empty state offers to add a permission when no filter matches', function () {
    $this->actingAs($this->admin)->get(route('permissions.index', ['filter' => ['search' => 'zzzz-nothing']]))
        ->assertSee('No permissions match these filters')->assertSee('Clear all filters');
});
