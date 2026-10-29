<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * A permission a role grants can be switched off for one user: the card and the
 * module disappear for them, other users of the same role are unaffected.
 */
beforeEach(function (): void {
    foreach (['view aksics', 'view file management systems', 'view account openings', 'view users', 'edit users', 'assign permissions'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
    $this->role = Role::firstOrCreate(['name' => 'division']);
    $this->role->givePermissionTo(['view aksics', 'view file management systems', 'view account openings']);

    $this->fileManagement = Permission::findByName('view file management systems');
    $this->adal = User::factory()->create();
    $this->adal->assignRole('division');
    $this->colleague = User::factory()->create();
    $this->colleague->assignRole('division');
});

test('a role permission revoked for one user is denied to them only', function (): void {
    $this->adal->deniedPermissions()->attach($this->fileManagement->id);

    $adal = $this->adal->fresh();
    expect($adal->can('view file management systems'))->toBeFalse()
        ->and($adal->hasPermissionTo('view file management systems'))->toBeFalse()
        ->and($adal->can('view aksics'))->toBeTrue()
        ->and($this->colleague->fresh()->can('view file management systems'))->toBeTrue();
});

test('the product page hides the card and the module returns 403 for the user it is revoked for', function (): void {
    $this->adal->deniedPermissions()->attach($this->fileManagement->id);

    $this->actingAs($this->adal->fresh())->get(route('product.index'))
        ->assertOk()
        ->assertDontSee('href="'.route('file-management-systems.index').'"', false)
        ->assertSee('href="'.route('aksic.index').'"', false);

    $this->actingAs($this->adal->fresh())->get(route('file-management-systems.index'))->assertForbidden();
    $this->actingAs($this->colleague->fresh())->get(route('product.index'))
        ->assertSee('href="'.route('file-management-systems.index').'"', false);
});

test('saving the user form stores only revoked permissions that a role grants', function (): void {
    $editor = User::factory()->create();
    $editor->givePermissionTo(['view users', 'edit users', 'assign permissions']);
    $extra = Permission::firstOrCreate(['name' => 'view dashboard']);

    $this->actingAs($editor)->put(route('users.update', $this->adal), [
        'name' => $this->adal->name,
        'email' => $this->adal->email,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [(string) $this->role->id],
        'permissions' => [],
        'denied_permissions' => [(string) $this->fileManagement->id, (string) $extra->id],
    ])->assertRedirect(route('users.index'));

    expect($this->adal->deniedPermissions()->pluck('name')->all())->toBe(['view file management systems']);

    // Un-ticking "revoke" gives the role permission back.
    $this->actingAs($editor)->put(route('users.update', $this->adal), [
        'name' => $this->adal->name,
        'email' => $this->adal->email,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [(string) $this->role->id],
    ])->assertRedirect(route('users.index'));

    expect($this->adal->deniedPermissions()->count())->toBe(0)
        ->and($this->adal->fresh()->can('view file management systems'))->toBeTrue();
});

test('the edit form offers a revoke checkbox for role permissions and pre-ticks revoked ones', function (): void {
    $editor = User::factory()->create();
    $editor->givePermissionTo(['view users', 'edit users', 'assign permissions']);
    $this->adal->deniedPermissions()->attach($this->fileManagement->id);

    $page = $this->actingAs($editor)->get(route('users.edit', $this->adal))
        ->assertOk()
        ->assertSee('name="denied_permissions[]"', false);

    expect($page->getContent())->toMatch('/denied: .*'.$this->fileManagement->id.'/');
});
