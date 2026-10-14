<?php

use App\Support\PermissionCatalog;
use Spatie\Permission\Models\Permission;

it('places each permission in the module its subject belongs to', function (string $name, string $module) {
    expect(PermissionCatalog::moduleKey($name))->toBe($module);
})->with([
    ['view users', 'access'],
    ['assign roles', 'access'],
    ['revoke permissions', 'access'],
    ['view managers', 'access'],
    ['approve account openings', 'account-opening'],
    ['view account opening dashboard', 'account-opening'],
    ['manage account opening references', 'account-opening'],
    ['view aksics', 'aksic'],
    ['view all aksic cases', 'aksic'],
    ['lodge aksic claims', 'aksic'],
    ['manage aksic budget', 'aksic'],
    ['view file management systems', 'file-management'],
    ['view file management dashboard', 'file-management'],
    ['approve file management transfers', 'file-management'],
    ['create boxes', 'file-management'],
    ['edit file categories', 'file-management'],
    ['assign complaints', 'complaints'],
    ['edit branches', 'organisation'],
    ['create categories', 'organisation'],
    ['view dashboard', 'general'],
    ['export reports', 'general'],
    ['system settings', 'general'],
    ['frobnicate widgets', 'other'],
]);

it('never mixes modules: file management and account opening stay apart', function () {
    $permissions = collect([
        'view file management systems', 'create boxes', 'view account openings', 'approve account openings',
    ])->map(fn ($name, $i) => tap(new Permission(['name' => $name]), fn ($p) => $p->id = $i + 1));

    $modules = collect(PermissionCatalog::build($permissions))->keyBy('key');

    expect($modules->keys()->all())->toBe(['account-opening', 'file-management'])
        ->and(collect($modules['file-management']['groups'])->flatMap(fn ($g) => $g['permissions']->pluck('name'))->all())
        ->toBe(['create boxes', 'view file management systems'])
        ->and(collect($modules['account-opening']['groups'])->flatMap(fn ($g) => $g['permissions']->pluck('name'))->all())
        ->toBe(['approve account openings', 'view account openings']);
});

it('lists every permission exactly once, even unmapped ones', function () {
    $names = ['view users', 'frobnicate widgets', 'view aksics', 'create boxes'];
    $permissions = collect($names)->map(fn ($name, $i) => tap(new Permission(['name' => $name]), fn ($p) => $p->id = $i + 1));

    $listed = collect(PermissionCatalog::build($permissions))
        ->flatMap(fn ($m) => collect($m['groups'])->flatMap(fn ($g) => $g['permissions']->pluck('name')))
        ->sort()->values()->all();

    expect($listed)->toBe(collect($names)->sort()->values()->all());
});
