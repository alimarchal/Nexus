<?php

use App\Models\FileCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Permissions already exist from the file_categories migration; firstOrCreate keeps this idempotent.
    Permission::firstOrCreate(['name' => 'view file categories']);
    Permission::firstOrCreate(['name' => 'create file categories']);
    Permission::firstOrCreate(['name' => 'edit file categories']);
    Permission::firstOrCreate(['name' => 'delete file categories']);

    $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
    $adminRole->givePermissionTo(['view file categories', 'create file categories', 'edit file categories', 'delete file categories']);

    $viewerRole = Role::firstOrCreate(['name' => 'branch']);
    $viewerRole->givePermissionTo(['view file categories']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('branch');
});

test('the file categories migration seeds default categories', function () {
    expect(FileCategory::count())->toBeGreaterThanOrEqual(10);
    $this->assertDatabaseHas('file_categories', ['category_code' => 'FC-001']);
});

test('authorized user can view file categories index', function () {
    $response = $this->actingAs($this->admin)->get(route('file-categories.index'));

    $response->assertSuccessful();
    $response->assertViewIs('file-categories.index');
    $response->assertSeeText('Circulars & Notifications');
});

test('user without permission cannot view file categories index', function () {
    $unauthorizedUser = User::factory()->create();

    $response = $this->actingAs($unauthorizedUser)->get(route('file-categories.index'));

    $response->assertForbidden();
});

test('viewer role can view but not access create form', function () {
    $this->actingAs($this->viewer)->get(route('file-categories.index'))->assertSuccessful();
    $this->actingAs($this->viewer)->get(route('file-categories.create'))->assertForbidden();
});

test('authorized user can create a file category', function () {
    $response = $this->actingAs($this->admin)->post(route('file-categories.store'), [
        'category_code' => 'FC-999',
        'category_name' => 'Test Category',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('file-categories.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('file_categories', [
        'category_code' => 'FC-999',
        'category_name' => 'Test Category',
    ]);
});

test('file category creation validates required and unique fields', function () {
    $response = $this->actingAs($this->admin)->post(route('file-categories.store'), []);
    $response->assertSessionHasErrors(['category_code', 'category_name', 'is_active']);

    $response = $this->actingAs($this->admin)->post(route('file-categories.store'), [
        'category_code' => 'FC-001',
        'category_name' => 'Duplicate Code',
        'is_active' => '1',
    ]);
    $response->assertSessionHasErrors(['category_code']);
});

test('authorized user can edit and update a file category', function () {
    $fileCategory = FileCategory::factory()->create();

    $this->actingAs($this->admin)->get(route('file-categories.edit', $fileCategory))->assertSuccessful();

    $response = $this->actingAs($this->admin)->put(route('file-categories.update', $fileCategory), [
        'category_code' => $fileCategory->category_code,
        'category_name' => 'Updated Name',
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('file-categories.index'));
    $this->assertDatabaseHas('file_categories', [
        'id' => $fileCategory->id,
        'category_name' => 'Updated Name',
        'is_active' => '0',
    ]);
});

test('authorized user can delete a file category', function () {
    $fileCategory = FileCategory::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('file-categories.destroy', $fileCategory));

    $response->assertRedirect(route('file-categories.index'));
    $this->assertSoftDeleted('file_categories', ['id' => $fileCategory->id]);
});

test('user without delete permission cannot delete a file category', function () {
    $fileCategory = FileCategory::factory()->create();

    $response = $this->actingAs($this->viewer)->delete(route('file-categories.destroy', $fileCategory));

    $response->assertForbidden();
    $this->assertDatabaseHas('file_categories', ['id' => $fileCategory->id, 'deleted_at' => null]);
});
