<?php

use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

test('the file categories migrations seed the 20 official ORU categories with descriptions', function () {
    expect(FileCategory::count())->toBe(20)
        ->and(FileCategory::whereNull('description')->count())->toBe(0);

    $this->assertDatabaseHas('file_categories', ['category_code' => 'FC-001', 'category_name' => 'Account Opening Records']);
    $this->assertDatabaseHas('file_categories', ['category_code' => 'FC-020', 'category_name' => 'Fixed Assets and Other Registers']);
    $this->assertDatabaseMissing('file_categories', ['category_name' => 'Circulars & Notifications']);
});

test('files in an old default category move to the matching ORU category', function () {
    $migration = require database_path('migrations/'.collect(scandir(database_path('migrations')))->first(fn ($f) => str_contains($f, 'add_description_and_oru_categories')));
    $migration->down();
    DB::table('file_categories')->delete();

    $oldAudit = FileCategory::create(['category_code' => 'FC-007', 'category_name' => 'Audit Reports', 'is_active' => '1']);
    $oldLegal = FileCategory::create(['category_code' => 'FC-008', 'category_name' => 'Legal & Litigation Documents', 'is_active' => '1']);
    $custom = FileCategory::create(['category_code' => 'FC-011', 'category_name' => 'My Own Category', 'is_active' => '1']);
    $auditFile = FileManagementSystem::factory()->create(['file_category_id' => $oldAudit->id]);
    $legalFile = FileManagementSystem::factory()->create(['file_category_id' => $oldLegal->id]);

    $migration->up();

    expect($auditFile->fresh()->fileCategory->category_code)->toBe('FC-012')
        ->and($legalFile->fresh()->fileCategory->category_code)->toBe('FC-013')
        ->and(FileCategory::where('category_name', 'Audit Reports')->exists())->toBeFalse()
        ->and(FileCategory::find($custom->id)->category_name)->toBe('My Own Category')
        ->and(FileCategory::where('category_code', 'FC-011')->value('category_name'))->toBe('AML/CFT and Compliance Records');
});

test('authorized user can view file categories index', function () {
    $response = $this->actingAs($this->admin)->get(route('file-categories.index'));

    $response->assertSuccessful();
    $response->assertViewIs('file-categories.index');
    $response->assertSeeText('Account Opening Records');
    $response->assertSeeText('Account Opening Forms (AOFs), specimen signature cards');
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
        'description' => 'Records used in the test.',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('file-categories.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('file_categories', [
        'category_code' => 'FC-999',
        'category_name' => 'Test Category',
        'description' => 'Records used in the test.',
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

test('the category description is offered when choosing a category for a file', function () {
    $this->admin->givePermissionTo(Permission::firstOrCreate(['name' => 'create file management systems']));

    $this->actingAs($this->admin)
        ->get(route('file-management-systems.create'))
        ->assertSuccessful()
        ->assertSee('data-description="Account Opening Forms (AOFs), specimen signature cards', false);
});
