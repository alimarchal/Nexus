<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

it('renders a print trigger wired to window.print on :route', function (string $route) {
    $html = $this->actingAs($this->superAdmin)
        ->get(route($route))
        ->assertSuccessful()
        ->getContent();

    expect($html)->toContain('window.print()');
})->with([
    'account-openings.index',
    'aksic-budgets.index',
    'aksic-claims.index',
    'aksic-rules.index',
    'branches.index',
    'regions.index',
    'districts.index',
    'file-categories.index',
    'permissions.index',
    'roles.index',
    'users.index',
    'managers.index',
    'report.printed-stationeries',
]);

it('keeps the shared page header print button a plain button wired to window.print', function () {
    $html = Blade::render('<x-page-header title="Anything" />');

    expect($html)->toMatch('/<button type="button" onclick="window\.print\(\);?"/');
});
