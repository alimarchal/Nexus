<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => 'view users']);
    Permission::create(['name' => 'create users']);
    Permission::create(['name' => 'edit users']);
    Permission::create(['name' => 'delete users']);

    // Create roles
    $this->superAdminRole = Role::create(['name' => 'super-admin']);
    $this->managerRole = Role::create(['name' => 'manager']);
    $this->userRole = Role::create(['name' => 'user']);

    // Assign permissions
    $this->superAdminRole->givePermissionTo(['view users', 'create users', 'edit users', 'delete users']);
    $this->managerRole->givePermissionTo(['view users', 'edit users']);
    $this->userRole->givePermissionTo(['view users']);

    // Create test branch and division
    $this->branch = Branch::factory()->create();
    $this->division = Division::factory()->create();

    // Create users
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->regularUser = User::factory()->create();
    $this->regularUser->assignRole('user');
});

test('super admin can view users index', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('users.index'));
    
    $response->assertSuccessful();
    $response->assertViewIs('users.index');
});

test('user without permission cannot view users index', function () {
    $unauthorizedUser = User::factory()->create();
    
    $response = $this->actingAs($unauthorizedUser)->get(route('users.index'));
    
    $response->assertForbidden();
});

test('super admin can create user', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('users.create'));
    
    $response->assertSuccessful();
    $response->assertViewIs('users.create');
});

test('user without create permission cannot access create form', function () {
    $response = $this->actingAs($this->regularUser)->get(route('users.create'));
    
    $response->assertForbidden();
});

test('super admin can store new user with roles', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'branch_id' => $this->branch->id,
        'division_id' => $this->division->id,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [$this->userRole->id],
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $userData);

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success', 'User created successfully with assigned roles and permissions.');

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'branch_id' => $this->branch->id,
        'division_id' => $this->division->id,
    ]);

    $user = User::where('email', 'test@example.com')->first();
    $this->assertTrue($user->hasRole('user'));
});

test('user creation validates required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('users.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'is_super_admin', 'is_active']);
});

test('user creation validates unique email', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $userData = [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $userData);

    $response->assertSessionHasErrors(['email']);
});

test('super admin can edit user', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($this->superAdmin)->get(route('users.edit', $user));
    
    $response->assertSuccessful();
    $response->assertViewIs('users.edit');
});

test('user without edit permission cannot edit users', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($this->regularUser)->get(route('users.edit', $user));
    
    $response->assertForbidden();
});

test('super admin can update user with role changes', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $updateData = [
        'name' => 'Updated Name',
        'email' => $user->email,
        'branch_id' => $this->branch->id,
        'division_id' => $this->division->id,
        'is_super_admin' => 'No',
        'is_active' => 'Yes',
        'roles' => [$this->managerRole->id],
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('users.update', $user), $updateData);

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success', 'User updated successfully with assigned roles and permissions.');

    $user->refresh();
    $this->assertEquals('Updated Name', $user->name);
    $this->assertTrue($user->hasRole('manager'));
    $this->assertFalse($user->hasRole('user'));
});

test('user cannot deactivate their own account', function () {
    $updateData = [
        'name' => $this->superAdmin->name,
        'email' => $this->superAdmin->email,
        'is_super_admin' => 'Yes',
        'is_active' => 'No', // Trying to deactivate own account
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('users.update', $this->superAdmin), $updateData);

    $response->assertSessionHasErrors(['is_active']);
});

test('super admin can delete user', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($this->superAdmin)->delete(route('users.destroy', $user));
    
    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success', 'User deleted successfully.');
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('user cannot delete their own account', function () {
    $response = $this->actingAs($this->superAdmin)->delete(route('users.destroy', $this->superAdmin));
    
    $response->assertSessionHasErrors(['user']);
});

test('cannot delete last super admin', function () {
    // Make sure there's only one super admin
    User::role('super-admin')->where('id', '!=', $this->superAdmin->id)->delete();
    
    $response = $this->actingAs($this->superAdmin)->delete(route('users.destroy', $this->superAdmin));
    
    $response->assertSessionHasErrors(['user']);
});

test('user without delete permission cannot delete users', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($this->regularUser)->delete(route('users.destroy', $user));
    
    $response->assertForbidden();
});