<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * A module card on Product shows only when the user's role holds that module's view permission.
 */
beforeEach(function (): void {
    foreach (['view aksics', 'view aksic claims', 'view file management systems', 'view account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
    $this->role = Role::firstOrCreate(['name' => 'branch']);
    $this->user = User::factory()->create();
    $this->user->assignRole($this->role);
});

dataset('module cards', [
    'AKSIC' => ['view aksics', 'AKSIC', 'aksic.index'],
    'AKSIC Claims' => ['view aksic claims', 'AKSIC Claims', 'aksic-claims.index'],
    'File Management' => ['view file management systems', 'File Management', 'file-management-systems.index'],
    'Account Opening' => ['view account openings', 'Account Opening (AOF)', 'account-openings.index'],
]);

test('module card appears only once its permission is granted', function (string $permission, string $label, string $routeName): void {
    $url = route($routeName);

    $this->actingAs($this->user)->get(route('product.index'))
        ->assertOk()->assertDontSee('href="'.$url.'"', false);

    $this->role->givePermissionTo($permission);

    $this->actingAs($this->user->fresh())->get(route('product.index'))
        ->assertOk()->assertSee('href="'.$url.'"', false);
})->with('module cards');

test('user with no module permission sees an empty state and no Product link', function (): void {
    $this->actingAs($this->user)->get(route('product.index'))
        ->assertOk()
        ->assertSee('No module has been enabled for your role yet.')
        ->assertDontSee('href="'.route('product.index').'"', false);
});
