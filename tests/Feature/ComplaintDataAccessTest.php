<?php

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
function actAsComplaintTester(mixed $test, User $user): mixed
{
    $test->flushSession();
    app('auth')->forgetGuards();

    return $test->actingAs($user);
}

function complaintTester(array $permissions): User
{
    $role = Role::firstOrCreate(['name' => 'data-test-'.md5(implode(',', $permissions)), 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('the complaint full data and the analytics data need the view complaints permission', function () {
    $complaint = Complaint::factory()->create();
    $blocked = complaintTester(['view file management systems']);
    $viewer = complaintTester(['view complaints']);

    foreach ([route('complaints.full', $complaint), route('complaints.analytics-data'), route('complaints.analytics'), route('complaints.export')] as $url) {
        actAsComplaintTester($this, $blocked)->get($url)->assertForbidden();
    }

    actAsComplaintTester($this, $viewer)->get(route('complaints.full', $complaint))->assertSuccessful();
    // The analytics query uses MySQL date functions, so under the SQLite test database only the permission is checked.
    expect(actAsComplaintTester($this, $viewer)->get(route('complaints.analytics-data'))->status())->not->toBe(403);
});
