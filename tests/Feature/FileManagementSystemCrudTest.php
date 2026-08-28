<?php

use App\Models\Branch;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Permissions already exist from the file_management_systems migration; firstOrCreate keeps this idempotent.
    Permission::firstOrCreate(['name' => 'view file management systems']);
    Permission::firstOrCreate(['name' => 'create file management systems']);
    Permission::firstOrCreate(['name' => 'edit file management systems']);
    Permission::firstOrCreate(['name' => 'delete file management systems']);

    $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
    $adminRole->givePermissionTo([
        'view file management systems',
        'create file management systems',
        'edit file management systems',
        'delete file management systems',
    ]);

    $branchRole = Role::firstOrCreate(['name' => 'branch']);
    $branchRole->givePermissionTo(['view file management systems', 'create file management systems', 'edit file management systems']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->branch = Branch::factory()->create();
    $this->branchUser = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->branchUser->assignRole('branch');

    $this->fileCategory = FileCategory::factory()->create();
});

test('authorized user can view file management systems index', function () {
    $response = $this->actingAs($this->admin)->get(route('file-management-systems.index'));

    $response->assertSuccessful();
    $response->assertViewIs('file-management-systems.index');
});

test('user without permission cannot view file management systems index', function () {
    $unauthorizedUser = User::factory()->create();

    $this->actingAs($unauthorizedUser)->get(route('file-management-systems.index'))->assertForbidden();
});

test('authorized user can create a document record with a scanned page', function () {
    Storage::fake('public');

    $response = $this->actingAs($this->admin)->post(route('file-management-systems.store'), [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'title' => 'Test Document',
        'pages' => [UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf')],
    ]);

    $response->assertRedirect(route('file-management-systems.index'));
    $this->assertDatabaseHas('file_management_systems', [
        'title' => 'Test Document',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
    ]);

    $fms = FileManagementSystem::where('title', 'Test Document')->first();
    expect($fms->media)->toHaveCount(1);
});

test('branch role can only manage their own branch scoped documents', function () {
    $ownDocument = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $otherBranch = Branch::factory()->create();
    $otherDocument = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $otherBranch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->branchUser)->get(route('file-management-systems.index'));

    $response->assertSuccessful();
    $response->assertSee($ownDocument->digital_id);
    $response->assertDontSee($otherDocument->digital_id);
});

test('authorized user can create a document record with a manual file no and filter by it', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post(route('file-management-systems.store'), [
        'file_category_id' => $this->fileCategory->id,
        'file_no' => 'HRMS/12/ABC',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'title' => 'Test Document',
        'pages' => [UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf')],
    ]);

    $this->assertDatabaseHas('file_management_systems', ['file_no' => 'HRMS/12/ABC']);

    $response = $this->actingAs($this->admin)->get(route('file-management-systems.index', ['filter' => ['file_no' => 'HRMS/12']]));

    $response->assertSuccessful();
    $response->assertSee('HRMS/12/ABC');
});

test('non super admin cannot manually assign a document to a branch', function () {
    $regionRole = Role::firstOrCreate(['name' => 'region']);
    $regionRole->givePermissionTo(['view file management systems', 'create file management systems']);
    $regionUser = User::factory()->create(['branch_id' => null]);
    $regionUser->assignRole('region');

    $region = Region::factory()->create();

    $response = $this->actingAs($regionUser)->post(route('file-management-systems.store'), [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'pages' => [UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors('fileable_type');
});

test('user without delete permission cannot delete a document record', function () {
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->branchUser)->delete(route('file-management-systems.destroy', $document));

    $response->assertForbidden();
    $this->assertDatabaseHas('file_management_systems', ['id' => $document->id, 'deleted_at' => null]);
});

test('authorized user can delete a document record', function () {
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('file-management-systems.destroy', $document));

    $response->assertRedirect(route('file-management-systems.index'));
    $this->assertSoftDeleted('file_management_systems', ['id' => $document->id]);
});

test('authorized user can view a single document record', function () {
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('file-management-systems.show', $document));

    $response->assertSuccessful();
    $response->assertViewIs('file-management-systems.show');
    $response->assertSee($document->digital_id);
});

test('branch role cannot view another branch document by guessing the url', function () {
    $otherBranch = Branch::factory()->create();
    $otherDocument = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $otherBranch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.show', $otherDocument))->assertNotFound();
});

test('uploaded pages are stored under the branch/digital-id folder', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post(route('file-management-systems.store'), [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'title' => 'Folder Test',
        'pages' => [UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf')],
    ]);

    $fms = FileManagementSystem::where('title', 'Folder Test')->first();
    $page = $fms->media->first();

    expect($page->getPath())->toContain("Branch/{$this->branch->id}/{$fms->digital_id}");
});
