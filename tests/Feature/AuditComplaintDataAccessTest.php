<?php

use App\Models\Audit;
use App\Models\Complaint;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

/**
 * Act as another user with a clean session (the previous user's session password hash would log this one out).
 */
function actAs(mixed $test, User $user): mixed
{
    $test->flushSession();
    app('auth')->forgetGuards();

    return $test->actingAs($user);
}

function userWith(array $permissions): User
{
    $role = Role::firstOrCreate(['name' => 'data-test-'.md5(implode(',', $permissions)), 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('the audit full data needs the view audits permission', function () {
    $audit = Audit::factory()->create();

    actAs($this, userWith(['view file management systems']))->get(route('audits.full', $audit))->assertForbidden();
    actAs($this, userWith(['view audits']))->get(route('audits.full', $audit))->assertSuccessful();
    actAs($this, $this->superAdmin)->get(route('audits.full', $audit))->assertSuccessful();
});

test('the audit page and its full data are guarded the same way', function () {
    $audit = Audit::factory()->create();
    $blocked = userWith(['view file management systems']);

    actAs($this, $blocked)->get(route('audits.show', $audit))->assertForbidden();
    actAs($this, $blocked)->get(route('audits.full', $audit))->assertForbidden();
});

test('the complaint full data and the analytics data need the view complaints permission', function () {
    $complaint = Complaint::factory()->create();
    $blocked = userWith(['view file management systems']);
    $viewer = userWith(['view complaints']);

    foreach ([route('complaints.full', $complaint), route('complaints.analytics-data'), route('complaints.analytics'), route('complaints.export')] as $url) {
        actAs($this, $blocked)->get($url)->assertForbidden();
    }

    actAs($this, $viewer)->get(route('complaints.full', $complaint))->assertSuccessful();
    // The analytics query uses MySQL date functions, so under the SQLite test database only the permission is checked.
    expect(actAs($this, $viewer)->get(route('complaints.analytics-data'))->status())->not->toBe(403);
});
