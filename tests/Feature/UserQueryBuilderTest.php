<?php

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions and roles
    Permission::create(['name' => 'view users']);
    $this->superAdminRole = Role::create(['name' => 'super-admin']);
    $this->managerRole = Role::create(['name' => 'manager']);
    $this->userRole = Role::create(['name' => 'user']);

    $this->superAdminRole->givePermissionTo(['view users']);

    // Create test data
    $this->branch1 = Branch::factory()->create(['name' => 'Branch One']);
    $this->branch2 = Branch::factory()->create(['name' => 'Branch Two']);

    // Create test users with different attributes
    $this->activeUser = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => 'Yes',
        'branch_id' => $this->branch1->id,
    ]);
    $this->activeUser->assignRole('manager');

    $this->inactiveUser = User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'is_active' => 'No',
        'branch_id' => $this->branch2->id,
    ]);
    $this->inactiveUser->assignRole('user');

    $this->testUser = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@example.com',
        'is_active' => 'Yes',
        'branch_id' => $this->branch1->id,
    ]);
    $this->testUser->assignRole('super-admin');

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');
});

test('can filter users by name', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['name' => 'John']]));

    $response->assertSuccessful();
    $response->assertSeeText('Settings User-Module Users');
    $response->assertSeeText('Add User');
    $response->assertSeeText('John Doe');
    $response->assertDontSeeText('Jane Smith');
});

test('can filter users by email', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['email' => 'john@']]));

    $response->assertSuccessful();
    $response->assertSeeText('john@example.com');
    $response->assertDontSeeText('jane@example.com');
});

test('can filter users by status', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['is_active' => 'Yes']]));

    $response->assertSuccessful();
    $response->assertSeeText('John Doe');
    $response->assertSeeText('Test Admin');
    $response->assertDontSeeText('Jane Smith');
});

test('can filter users by branch', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['branch_id' => $this->branch1->id]]));

    $response->assertSuccessful();
    $response->assertSeeText('John Doe');
    $response->assertSeeText('Test Admin');
    $response->assertDontSeeText('Jane Smith');
});

test('can filter users by role', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['role' => 'manager']]));

    $response->assertSuccessful();
    $response->assertSeeText('John Doe');
    $response->assertDontSeeText('Jane Smith');
    $response->assertDontSeeText('Test Admin');
});

test('can sort users by name ascending', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['sort' => 'name']));

    $response->assertSuccessful();
    $content = $response->getContent();
    $johnPosition = strpos($content, 'John Doe');
    $janePosition = strpos($content, 'Jane Smith');
    $testPosition = strpos($content, 'Test Admin');

    $this->assertLessThan($johnPosition, $janePosition);
    $this->assertLessThan($testPosition, $johnPosition);
});

test('can sort users by name descending', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['sort' => '-name']));

    $response->assertSuccessful();
    $content = $response->getContent();
    $johnPosition = strpos($content, 'John Doe');
    $testPosition = strpos($content, 'Test Admin');

    $this->assertLessThan($johnPosition, $testPosition);
});

test('can sort users by email', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['sort' => 'email']));

    $response->assertSuccessful();
    $content = $response->getContent();
    $adminPosition = strpos($content, 'admin@example.com');
    $johnPosition = strpos($content, 'john@example.com');

    $this->assertLessThan($johnPosition, $adminPosition);
});

test('can sort users by creation date', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['sort' => 'created_at']));

    $response->assertSuccessful();
});

test('can combine multiple filters', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', [
            'filter' => [
                'is_active' => 'Yes',
                'branch_id' => $this->branch1->id,
                'name' => 'John',
            ],
        ]));

    $response->assertSuccessful();
    $response->assertSeeText('John Doe');
    $response->assertDontSeeText('Jane Smith');
    $response->assertDontSeeText('Test Admin');
});

test('can paginate results', function () {
    // Create more users to test pagination
    User::factory()->count(15)->create();

    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['per_page' => 5]));

    $response->assertSuccessful();
    $response->assertSeeText('Showing');
});

test('can include relationships', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['include' => 'branch,roles']));

    $response->assertSuccessful();
});

test('default sort is by creation date descending', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index'));

    $response->assertSuccessful();

    // The last created user should appear first (default sort -created_at)
    $content = $response->getContent();
    $superAdminPosition = strpos($content, $this->superAdmin->email);
    $johnPosition = strpos($content, 'john@example.com');

    $this->assertLessThan($johnPosition, $superAdminPosition);
});

test('query parameters persist across pagination', function () {
    User::factory()->count(15)->create(['is_active' => 'Yes']);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', [
            'filter' => ['is_active' => 'Yes'],
            'sort' => 'name',
            'per_page' => 5,
        ]));

    $response->assertSuccessful();
    $content = $response->getContent();

    $this->assertStringContainsString('filter%5Bis_active%5D=Yes', $content);
    $this->assertStringContainsString('sort=name', $content);
});

test('invalid filters are ignored', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['filter' => ['invalid_field' => 'value']]));

    $response->assertBadRequest();
});

test('invalid sorts are ignored', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('users.index', ['sort' => 'invalid_field']));

    $response->assertBadRequest();
});
