<?php

use App\Models\AksicApplication;
use App\Models\User;
use App\Support\PermissionCatalog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    config(['app.aksic_api_token' => 'test-token']);
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    $this->application = AksicApplication::factory()->create();
});

function aksicUser(array $permissions): User
{
    $role = Role::firstOrCreate(['name' => 'aksic-test-'.md5(implode(',', $permissions)), 'guard_name' => 'web']);
    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function statusPayload(): array
{
    return ['old_status' => 'Pending', 'new_status' => 'Approved', 'applicant_id' => 77];
}

test('a user with only file management access cannot open, sync or update AKSIC applications', function () {
    $user = aksicUser(['view file management systems']);

    $this->actingAs($user)->get(route('aksic-applications.index'))->assertForbidden();
    $this->actingAs($user)->get(route('aksic-applications.show', $this->application))->assertForbidden();
    $this->actingAs($user)->get(route('aksic-applications.pdf', $this->application))->assertForbidden();
    $this->actingAs($user)->post(route('aksic-applications.sync'))->assertForbidden();
    $this->actingAs($user)->postJson(route('aksic-applications.update-status', $this->application), statusPayload())->assertForbidden();

    Http::assertNothingSent();
});

test('a viewer can open the applications but cannot sync or change a status', function () {
    $user = aksicUser(['view aksic applications']);

    $this->actingAs($user)->get(route('aksic-applications.index'))->assertSuccessful();
    $this->actingAs($user)->get(route('aksic-applications.show', $this->application))->assertSuccessful();
    $this->actingAs($user)->post(route('aksic-applications.sync'))->assertForbidden();
    $this->actingAs($user)->postJson(route('aksic-applications.update-status', $this->application), statusPayload())->assertForbidden();

    Http::assertNothingSent();
});

test('the sync permission allows a sync and the status permission allows a status update', function () {
    $this->actingAs(aksicUser(['sync aksic applications']))->post(route('aksic-applications.sync'))->assertSuccessful();
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/applications'));

    $this->actingAs(aksicUser(['update aksic application status']))
        ->postJson(route('aksic-applications.update-status', $this->application), statusPayload())->assertSuccessful();
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/applications/77/status-update'));
});

test('the buttons are hidden for users without the permission and shown to those with it', function () {
    $viewer = aksicUser(['view aksic applications']);
    expect($this->actingAs($viewer)->get(route('aksic-applications.index'))->getContent())->toMatch('/id="syncBtn"\s+style="display:none"/');
    $this->actingAs($viewer)->get(route('aksic-applications.show', $this->application))->assertSee('style="display:none"', false);

    $operator = aksicUser(['view aksic applications', 'sync aksic applications', 'update aksic application status']);
    expect($this->actingAs($operator)->get(route('aksic-applications.index'))->getContent())->not->toMatch('/id="syncBtn"\s+style="display:none"/');
    $this->actingAs($operator)->get(route('aksic-applications.show', $this->application))->assertDontSee('style="display:none"', false);
});

test('the seeder gives the new permissions to the AKSIC roles and the super admin, under the AKSIC module', function () {
    foreach (Role::all() as $role) {
        if ($role->name === 'super-admin') {
            continue;
        }

        expect($role->hasPermissionTo('view aksic applications'))->toBe($role->hasPermissionTo('view aksics'), $role->name)
            ->and($role->hasPermissionTo('sync aksic applications'))->toBe($role->hasPermissionTo('approve aksics'), $role->name)
            ->and($role->hasPermissionTo('update aksic application status'))->toBe($role->hasPermissionTo('approve aksics'), $role->name);
    }

    expect(PermissionCatalog::moduleKey('sync aksic applications'))->toBe('aksic')
        ->and(PermissionCatalog::moduleKey('update aksic application status'))->toBe('aksic');
});

test('the migration grants the new permissions to existing roles by what they could already do', function () {
    $viewer = Role::create(['name' => 'old-viewer', 'guard_name' => 'web'])->givePermissionTo('view aksics');
    $approver = Role::create(['name' => 'old-approver', 'guard_name' => 'web'])->givePermissionTo(['view aksics', 'approve aksics']);
    Permission::whereIn('name', ['view aksic applications', 'sync aksic applications', 'update aksic application status'])->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $migration = require database_path('migrations/'.collect(scandir(database_path('migrations')))->first(fn ($f) => str_contains($f, 'add_aksic_application_permissions')));
    $migration->up();

    expect($viewer->fresh()->hasPermissionTo('view aksic applications'))->toBeTrue()
        ->and($viewer->fresh()->hasPermissionTo('sync aksic applications'))->toBeFalse()
        ->and($approver->fresh()->hasPermissionTo('sync aksic applications'))->toBeTrue()
        ->and($approver->fresh()->hasPermissionTo('update aksic application status'))->toBeTrue();
});
