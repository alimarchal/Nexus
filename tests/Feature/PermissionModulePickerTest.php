<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

/**
 * Names of the permission checkboxes rendered inside fieldsets of one module.
 *
 * @return array<int, string>
 */
function permissionsInModule(string $html, string $module): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($dom);

    $names = [];
    foreach ($xpath->query("//fieldset[@data-module='{$module}']//label") as $label) {
        $names[] = trim(preg_replace('/\s+/', ' ', $label->textContent));
    }

    return array_map(fn ($name) => trim(str_replace('via role', '', $name)), $names);
}

it('shows a module tab for every module on the user permissions screen', function () {
    $html = $this->actingAs($this->superAdmin)
        ->get(route('users.edit', $this->superAdmin))
        ->assertSuccessful()
        ->assertSee('data-module-tab="file-management"', false)
        ->assertSee('data-module-tab="account-opening"', false)
        ->assertSee('data-module-tab="aksic"', false)
        ->getContent();

    expect($html)->toContain('role="tablist"');
});

it('keeps file management permissions out of the account opening module and vice versa', function () {
    $html = $this->actingAs($this->superAdmin)
        ->get(route('users.edit', $this->superAdmin))
        ->getContent();

    $fileManagement = permissionsInModule($html, 'file-management');
    $accountOpening = permissionsInModule($html, 'account-opening');

    expect($fileManagement)->toContain('view file management systems', 'create boxes', 'archive file management systems')
        ->and(implode(' ', $fileManagement))->not->toContain('account opening')
        ->and($accountOpening)->toContain('view account openings', 'approve account openings')
        ->and(implode(' ', $accountOpening))->not->toContain('file management');
});

it('applies the same module tabs to the role permission form', function () {
    $role = Role::findByName('branch');

    $html = $this->actingAs($this->superAdmin)
        ->get(route('roles.edit', $role))
        ->assertSuccessful()
        ->assertSee('data-module-tab="file-management"', false)
        ->getContent();

    expect(implode(' ', permissionsInModule($html, 'file-management')))->not->toContain('account opening');
});
