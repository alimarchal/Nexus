<?php

use App\Models\Box;
use App\Models\Branch;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\FileManagementTransfer;
use App\Models\HeadOffice;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\HeadOfficeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Permissions already exist from the file_management_systems migration; firstOrCreate keeps this idempotent.
    Permission::firstOrCreate(['name' => 'view file management systems']);
    Permission::firstOrCreate(['name' => 'create file management systems']);
    Permission::firstOrCreate(['name' => 'edit file management systems']);
    Permission::firstOrCreate(['name' => 'delete file management systems']);
    Permission::firstOrCreate(['name' => 'transfer file management systems']);
    Permission::firstOrCreate(['name' => 'approve file management transfers']);
    Permission::firstOrCreate(['name' => 'archive file management systems']);
    Permission::firstOrCreate(['name' => 'create boxes']);
    Permission::firstOrCreate(['name' => 'manage boxes']);

    $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
    $adminRole->givePermissionTo([
        'view file management systems',
        'create file management systems',
        'edit file management systems',
        'delete file management systems',
        'transfer file management systems',
        'approve file management transfers',
        'archive file management systems',
        'create boxes',
        'manage boxes',
    ]);

    $branchRole = Role::firstOrCreate(['name' => 'branch']);
    $branchRole->givePermissionTo(['view file management systems', 'create file management systems', 'edit file management systems', 'transfer file management systems', 'archive file management systems', 'create boxes', 'manage boxes']);

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
    Storage::fake('local');

    $response = $this->actingAs($this->admin)->post(route('file-management-systems.store'), [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'title' => 'Test Document',
        'pages' => [UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf')],
    ]);

    $this->assertDatabaseHas('file_management_systems', [
        'title' => 'Test Document',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
    ]);

    $fms = FileManagementSystem::where('title', 'Test Document')->first();
    // Lands on the new file's page with its digital ID.
    $response->assertRedirect(route('file-management-systems.show', $fms))->assertSessionHas('success');
    expect($fms->media)->toHaveCount(1);
    $this->assertDatabaseHas('activity_log', [
        'event' => 'page_uploaded',
        'description' => 'File management page uploaded',
    ]);
});

test('branch role can view files created by another user in their branch', function () {
    $otherBranchUser = User::factory()->create(['branch_id' => $this->branch->id]);
    $otherBranchUser->assignRole('branch');

    $ownDocument = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
        'created_by' => $otherBranchUser->id,
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

test('normal edits cannot change a document record owner', function () {
    Storage::fake('local');

    $newBranch = Branch::factory()->create();
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);
    $document->addMedia(UploadedFile::fake()->create('scan.pdf', 50, 'application/pdf'))->toMediaCollection('pages');

    $this->actingAs($this->admin)->put(route('file-management-systems.update', $document), [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $newBranch->id,
        'document_date' => $document->document_date->format('Y-m-d'),
        'title' => 'Updated document title',
    ])->assertRedirect(route('file-management-systems.index'));

    $document->refresh();

    expect($document->fileable_type)->toBe('branch');
    expect($document->fileable_id)->toBe($this->branch->id);
    expect($document->title)->toBe('Updated document title');
});

test('authorized source user can create a pending transfer request', function () {
    $newBranch = Branch::factory()->create();
    $newBranchUser = User::factory()->create(['branch_id' => $newBranch->id]);
    $newBranchUser->assignRole('branch');
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->branchUser)->post(route('file-management-systems.transfers.store', $document), [
        'destination_fileable_type' => 'branch',
        'destination_fileable_id' => $newBranch->id,
        'recipient_id' => $newBranchUser->id,
        'reason' => 'Branch responsibility changed.',
    ])->assertRedirect(route('file-management-systems.show', $document));

    $this->assertDatabaseHas('file_management_transfers', [
        'file_management_system_id' => $document->id,
        'recipient_id' => $newBranchUser->id,
        'requested_by' => $this->branchUser->id,
        'status' => 'pending',
    ]);
    expect($document->fresh()->fileable_id)->toBe($this->branch->id);
});

test('user without transfer permission cannot request a transfer', function () {
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs(User::factory()->create())->post(route('file-management-systems.transfers.store', $document), [
        'destination_fileable_type' => 'branch',
        'destination_fileable_id' => $this->branch->id,
        'recipient_id' => $this->branchUser->id,
        'reason' => 'Unauthorized attempt.',
    ])->assertForbidden();
});

test('approving a transfer moves ownership and media and records custodian activity', function () {
    Storage::fake('local');

    $newBranch = Branch::factory()->create();
    $newBranchUser = User::factory()->create(['branch_id' => $newBranch->id]);
    $newBranchUser->assignRole('branch');
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);
    $page = $document->addMedia(UploadedFile::fake()->create('transfer-page.pdf', 100, 'application/pdf'))
        ->usingFileName('transfer-page.pdf')
        ->toMediaCollection('pages');
    $previousPath = $page->getPath();

    $this->actingAs($this->branchUser)->post(route('file-management-systems.transfers.store', $document), [
        'destination_fileable_type' => 'branch',
        'destination_fileable_id' => $newBranch->id,
        'recipient_id' => $newBranchUser->id,
        'reason' => 'Branch responsibility changed.',
    ]);
    $transfer = FileManagementTransfer::where('file_management_system_id', $document->id)->sole();

    $this->actingAs($this->admin)->patch(route('file-management-systems.transfers.decide', [$document, $transfer]), [
        'decision' => 'approved',
    ])->assertRedirect(route('file-management-systems.show', $document));

    $page->refresh();
    $document->refresh();

    expect($document->fileable_id)->toBe($newBranch->id);
    expect($document->current_custodian_id)->toBe($newBranchUser->id);
    expect($transfer->fresh()->status)->toBe('approved');
    expect($page->getPath())->toContain("Branch/{$newBranch->id}/{$document->digital_id}");
    expect($page->getPath())->toEndWith('transfer-page.pdf');
    expect(file_exists($previousPath))->toBeFalse();
    expect(file_exists($page->getPath()))->toBeTrue();
    $this->assertDatabaseHas('activity_log', ['event' => 'transferred', 'subject_id' => $document->id]);
});

test('authorized user can create a document record with a manual file no and filter by it', function () {
    Storage::fake('local');

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
    $owner = User::factory()->create();
    $this->actingAs($owner);

    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('file-management-systems.show', $document));

    $response->assertSuccessful();
    $response->assertViewIs('file-management-systems.show');
    $response->assertSee($document->digital_id);
    $response->assertViewHas('fileManagementSystem', fn (FileManagementSystem $fileManagementSystem): bool => $fileManagementSystem->creator?->is($owner) ?? false);
    $this->actingAs($this->admin)->get(route('file-management-systems.edit', $document))
        ->assertSee('Record Owner (Org Unit)')
        ->assertSee($owner->name);
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
    Storage::fake('local');

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

test('archiving a document to a box does not require selecting files', function () {
    Storage::fake('local');

    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);
    $document->addMedia(UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf'))
        ->usingFileName('scan.pdf')
        ->toMediaCollection('pages');

    $box = Box::factory()->create([
        'boxable_type' => 'branch',
        'boxable_id' => $this->branch->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->branchUser)->post(route('file-management-systems.archive', $document), [
        'box_id' => $box->id,
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('file-management-systems.show', $document));

    $document->refresh();
    expect($document->is_archived)->toBeTrue();
    expect($document->box_id)->toBe($box->id);
    expect($box->refresh()->file_count)->toBe(1);

    $page = $document->media->first()->fresh();
    expect($page->getPath())->toContain("Branch/{$this->branch->id}/Box-{$box->box_number}/{$document->digital_id}");
});

test('boxes list page loads for a branch user without errors', function () {
    Box::factory()->create([
        'boxable_type' => 'branch',
        'boxable_id' => $this->branch->id,
    ]);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.boxes'))
        ->assertSuccessful()
        ->assertViewIs('file-management-systems.boxes-list');
});

test('boxes create page back button points to the boxes list', function () {
    $this->actingAs($this->branchUser)->get(route('file-management-systems.boxes.create'))
        ->assertSuccessful()
        ->assertSee(route('file-management-systems.boxes'), false);
});

test('file management systems index can be filtered by box number', function () {
    $box = Box::factory()->create([
        'boxable_type' => 'branch',
        'boxable_id' => $this->branch->id,
        'box_number' => 'BOX-2026-001',
    ]);
    $archived = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
        'box_id' => $box->id,
    ]);
    FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('file-management-systems.index', ['filter' => ['box_number' => 'BOX-2026-001']]));

    $response->assertSuccessful();
    $response->assertViewHas('fileManagementSystems', fn ($items) => $items->pluck('id')->all() === [$archived->id]);
});

test('archive and new box links show only for the office that holds the file', function () {
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    expect($document->isHeldBy($this->branchUser))->toBeTrue()
        ->and($document->isHeldBy($this->admin))->toBeFalse();

    $this->actingAs($this->branchUser)->get(route('file-management-systems.show', $document))
        ->assertOk()->assertSee(route('file-management-systems.archive-form', $document), false);
    $this->actingAs($this->admin)->get(route('file-management-systems.show', $document))
        ->assertOk()->assertDontSee(route('file-management-systems.archive-form', $document), false);
    // A super admin has no office but may pick one, so the New Box button is offered (see FileManagementBoxTest).
    $this->actingAs($this->admin)->get(route('file-management-systems.boxes'))
        ->assertOk()->assertSee(route('file-management-systems.boxes.create'), false);
});

test('file list searches, filters by status tab and counts the tabs', function () {
    $active = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch', 'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id, 'digital_id' => 'FMS-SEARCH-1', 'title' => 'Loan agreement Ali',
    ]);
    $archived = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch', 'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id, 'is_archived' => true, 'title' => 'Old ledger',
    ]);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['search' => 'agreement']]))
        ->assertOk()
        ->assertViewHas('fileManagementSystems', fn ($items) => $items->pluck('id')->all() === [$active->id]);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['search' => 'FMS-SEARCH']]))
        ->assertViewHas('fileManagementSystems', fn ($items) => $items->pluck('id')->all() === [$active->id]);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['status' => 'archived'], 'per_page' => 50]))
        ->assertOk()
        ->assertViewHas('perPage', 50)
        ->assertViewHas('fileManagementSystems', fn ($items) => $items->pluck('id')->all() === [$archived->id])
        // Tab counts ignore the status tab itself.
        ->assertViewHas('stats', fn ($stats) => $stats['files'] === 2 && $stats['archived'] === 1 && $stats['active'] === 1)
        ->assertSee('Viewing: Branch:', false);
});

test('file page shows the snapshot, pages and a pending transfer banner', function () {
    Storage::fake('local');
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch', 'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id, 'title' => 'Board minutes',
    ]);
    $document->addMedia(UploadedFile::fake()->create('minutes.pdf', 120, 'application/pdf'))
        ->withCustomProperties(['original_filename' => 'minutes.pdf'])->toMediaCollection('pages');
    $other = Branch::factory()->create();
    FileManagementTransfer::query()->create([
        'file_management_system_id' => $document->id,
        'source_fileable_type' => 'branch', 'source_fileable_id' => $this->branch->id,
        'destination_fileable_type' => 'branch', 'destination_fileable_id' => $other->id,
        'recipient_id' => $this->admin->id, 'requested_by' => $this->branchUser->id, 'reason' => 'Audit', 'status' => 'pending',
    ]);

    $this->actingAs($this->admin)->get(route('file-management-systems.show', $document))
        ->assertOk()
        ->assertSee('Board minutes')
        ->assertSee('Scanned pages (1)')
        ->assertSee('minutes.pdf')
        ->assertSee('Transfer pending')
        ->assertSee('Awaiting your decision');
});

test('new file page shows the office picker to super admin and the fixed office to a branch user', function () {
    $this->actingAs($this->admin)->get(route('file-management-systems.create'))
        ->assertOk()->assertSee('Office type')->assertSee('Review &amp; save', false);

    $this->actingAs($this->branchUser)->get(route('file-management-systems.create'))
        ->assertOk()->assertDontSee('Office type')->assertSee('Set automatically from your posting.')
        ->assertSee('name="fileable_id" value="'.$this->branch->id.'"', false);
});

/**
 * @return array<string, string> ZIP entry name => contents
 */
function fmsZipEntries(string $path): array
{
    $zip = new ZipArchive;
    $zip->open($path);
    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        $entries[$name] = (string) $zip->getFromIndex($i);
    }
    $zip->close();

    return $entries;
}

function fmsDocumentWithPages(array $attributes, array $pages): FileManagementSystem
{
    $document = FileManagementSystem::factory()->create($attributes);
    foreach ($pages as $name => $content) {
        $document->addMediaFromString($content)->usingFileName(Str::uuid().'_'.$name)
            ->withCustomProperties(['original_filename' => $name])->toMediaCollection('pages');
    }

    return $document;
}

test('filtered files download as a zip with one folder per file and an index', function () {
    Storage::fake('local');
    Permission::firstOrCreate(['name' => 'export file management systems']);
    Role::findByName('branch', 'web')->givePermissionTo('export file management systems');

    fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'file_category_id' => $this->fileCategory->id,
        'digital_id' => 'FMS-ZIP-1', 'title' => 'Loan file: Ali'], ['scan.pdf' => '%PDF-page-one', 'photo.jpg' => 'jpeg-bytes']);
    fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'file_category_id' => $this->fileCategory->id,
        'digital_id' => 'FMS-ZIP-2', 'title' => 'Other', 'is_archived' => true], ['a.pdf' => '%PDF-other']);
    // Another branch's file never leaves the office.
    fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => Branch::factory()->create()->id, 'file_category_id' => $this->fileCategory->id,
        'digital_id' => 'FMS-ZIP-X', 'title' => 'Not mine'], ['x.pdf' => '%PDF-x']);

    $response = $this->actingAs($this->branchUser)->get(route('file-management-systems.export-zip', ['filter' => ['status' => 'active']]));

    $response->assertOk()->assertDownload();
    $entries = fmsZipEntries($response->baseResponse->getFile()->getPathname());
    $names = array_keys($entries);

    expect(collect($names)->first(fn ($n) => str_ends_with($n, 'FMS-ZIP-1 - Loan file- Ali/01 - scan.pdf')))->not->toBeNull()
        ->and(collect($names)->first(fn ($n) => str_ends_with($n, 'FMS-ZIP-1 - Loan file- Ali/02 - photo.jpg')))->not->toBeNull()
        ->and(collect($names)->contains(fn ($n) => str_contains($n, 'FMS-ZIP-2')))->toBeFalse()
        ->and(collect($names)->contains(fn ($n) => str_contains($n, 'FMS-ZIP-X')))->toBeFalse();

    $index = $entries[collect($names)->first(fn ($n) => str_ends_with($n, '00-index.csv'))];
    expect($index)->toContain('FMS-ZIP-1')->toContain('01 - scan.pdf | 02 - photo.jpg')->not->toContain('FMS-ZIP-X');
    expect($entries[collect($names)->first(fn ($n) => str_ends_with($n, '01 - scan.pdf'))])->toBe('%PDF-page-one');
    $this->assertDatabaseHas('activity_log', ['event' => 'exported', 'description' => 'File list exported as ZIP']);
});

test('ticked rows download as a zip and csv, limited to the user office', function () {
    Storage::fake('local');
    Permission::firstOrCreate(['name' => 'export file management systems']);
    Role::findByName('branch', 'web')->givePermissionTo('export file management systems');

    $mine = fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'file_category_id' => $this->fileCategory->id, 'digital_id' => 'FMS-SEL-1'], ['p.pdf' => 'p']);
    fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'file_category_id' => $this->fileCategory->id, 'digital_id' => 'FMS-SEL-2'], ['q.pdf' => 'q']);
    $other = fmsDocumentWithPages(['fileable_type' => 'branch', 'fileable_id' => Branch::factory()->create()->id, 'file_category_id' => $this->fileCategory->id, 'digital_id' => 'FMS-SEL-X'], ['x.pdf' => 'x']);

    $zip = $this->actingAs($this->branchUser)->post(route('file-management-systems.export-zip'), ['ids' => [$mine->id, $other->id]]);
    $names = array_keys(fmsZipEntries($zip->baseResponse->getFile()->getPathname()));
    expect(collect($names)->contains(fn ($n) => str_contains($n, 'FMS-SEL-1')))->toBeTrue()
        ->and(collect($names)->contains(fn ($n) => str_contains($n, 'FMS-SEL-2')))->toBeFalse()
        ->and(collect($names)->contains(fn ($n) => str_contains($n, 'FMS-SEL-X')))->toBeFalse();

    $csv = $this->actingAs($this->branchUser)->post(route('file-management-systems.export-csv'), ['ids' => [$mine->id]])->streamedContent();
    expect($csv)->toContain('Digital ID')->toContain('FMS-SEL-1')->not->toContain('FMS-SEL-2')->not->toContain('FMS-SEL-X');
});

test('export needs its permission and refuses an empty zip', function () {
    $this->actingAs($this->branchUser)->get(route('file-management-systems.export-zip'))->assertForbidden();
    $this->actingAs($this->branchUser)->get(route('file-management-systems.index'))->assertDontSee('Download scanned files (ZIP)');

    Permission::firstOrCreate(['name' => 'export file management systems']);
    Role::findByName('branch', 'web')->givePermissionTo('export file management systems');

    $this->actingAs($this->branchUser)->get(route('file-management-systems.export-zip'))
        ->assertRedirect()->assertSessionHas('error');
});

test('updating a record that has no scanned page requires a page upload', function () {
    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->admin)
        ->from(route('file-management-systems.edit', $document))
        ->put(route('file-management-systems.update', $document), [
            'file_category_id' => $this->fileCategory->id,
            'document_date' => '2026-08-01',
            'title' => 'Edited without a file',
        ])
        ->assertRedirect(route('file-management-systems.edit', $document))
        ->assertSessionHasErrors('pages');

    expect($document->fresh()->title)->not->toBe('Edited without a file');
});

test('updating a record that already has a scanned page does not require a new upload', function () {
    Storage::fake('local');

    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);
    $document->addMedia(UploadedFile::fake()->create('scan.pdf', 50, 'application/pdf'))->toMediaCollection('pages');

    $this->actingAs($this->admin)
        ->put(route('file-management-systems.update', $document), [
            'file_category_id' => $this->fileCategory->id,
            'document_date' => '2026-08-01',
            'title' => 'Edited title',
        ])
        ->assertRedirect(route('file-management-systems.index'))
        ->assertSessionHasNoErrors();

    expect($document->fresh()->title)->toBe('Edited title');
});

test('the last scanned page of a record cannot be removed', function () {
    Storage::fake('local');

    $document = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);
    $page = $document->addMedia(UploadedFile::fake()->create('scan.pdf', 50, 'application/pdf'))->toMediaCollection('pages');

    $this->actingAs($this->admin)
        ->delete(route('file-management-systems.media.destroy', [$document, $page->id]))
        ->assertSessionHasErrors('pages');

    expect($document->fresh()->media)->toHaveCount(1);
});

test('head office seeder creates the head office once so transfers have a destination', function () {
    $this->seed(HeadOfficeSeeder::class);
    $this->seed(HeadOfficeSeeder::class);

    expect(HeadOffice::where('name', 'Head Office')->count())->toBe(1);
});

test('transfer form lists the head office as a destination option', function () {
    $this->seed(HeadOfficeSeeder::class);
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('file-management-systems.transfer', $document))
        ->assertSuccessful()
        ->assertSee('<option value="'.HeadOffice::first()->id.'">Head Office</option>', false)
        ->assertDontSee('No head office has been set up');
});

test('transfer form explains when no head office exists yet', function () {
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('file-management-systems.transfer', $document))
        ->assertSuccessful()
        ->assertSee('No head office has been set up');
});

test('a head office user can be chosen as the receiving custodian of a transfer', function () {
    $headOffice = HeadOffice::create(['name' => 'Head Office']);
    $custodian = User::factory()->create(['head_office_id' => $headOffice->id]);
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->branchUser)
        ->get(route('file-management-systems.transfer', $document))
        ->assertSuccessful()
        ->assertSee('"head_office_id":'.$headOffice->id, false);

    $this->actingAs($this->branchUser)->post(route('file-management-systems.transfers.store', $document), [
        'destination_fileable_type' => 'head-office',
        'destination_fileable_id' => $headOffice->id,
        'recipient_id' => $custodian->id,
        'reason' => 'Send to head office records.',
    ])->assertRedirect(route('file-management-systems.show', $document));

    $this->assertDatabaseHas('file_management_transfers', ['recipient_id' => $custodian->id, 'status' => 'pending']);
});

test('a custodian from a different office is rejected with a field error', function () {
    $otherBranch = Branch::factory()->create();
    $destination = Branch::factory()->create();
    $wrongCustodian = User::factory()->create(['branch_id' => $otherBranch->id]);
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->branchUser)
        ->from(route('file-management-systems.transfer', $document))
        ->post(route('file-management-systems.transfers.store', $document), [
            'destination_fileable_type' => 'branch',
            'destination_fileable_id' => $destination->id,
            'recipient_id' => $wrongCustodian->id,
            'reason' => 'Wrong person picked.',
        ])
        ->assertRedirect(route('file-management-systems.transfer', $document))
        ->assertSessionHasErrors('recipient_id');

    $this->assertDatabaseCount('file_management_transfers', 0);
});

test('transfer form tells the user when the chosen office has no active custodian', function () {
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'file_category_id' => $this->fileCategory->id,
    ]);

    $this->actingAs($this->branchUser)
        ->get(route('file-management-systems.transfer', $document))
        ->assertSuccessful()
        ->assertSee('No active user is assigned to this office yet');
});

test('file list shows a visible new box button to users who can create boxes', function () {
    $this->actingAs($this->branchUser)
        ->get(route('file-management-systems.index'))
        ->assertSuccessful()
        ->assertSee('href="'.route('file-management-systems.boxes.create').'"', false)
        ->assertSee('New box');
});

test('file list hides the new box button from users who cannot create boxes', function () {
    Permission::firstOrCreate(['name' => 'view file management systems']);
    $viewer = User::factory()->create(['branch_id' => $this->branch->id]);
    $viewer->givePermissionTo('view file management systems');

    $this->actingAs($viewer)
        ->get(route('file-management-systems.index'))
        ->assertSuccessful()
        ->assertDontSee(route('file-management-systems.boxes.create'), false);
});

test('boxes page tells an office-less user why they cannot create a box', function () {
    // A super admin may pick the office (see FileManagementBoxTest); anyone else without an office is told why not.
    Permission::firstOrCreate(['name' => 'create boxes']);
    Permission::firstOrCreate(['name' => 'manage boxes']);
    $officeLess = User::factory()->create();
    $officeLess->givePermissionTo(['view file management systems', 'create boxes', 'manage boxes']);

    $this->actingAs($officeLess)
        ->get(route('file-management-systems.boxes'))
        ->assertSuccessful()
        ->assertSee('Boxes belong to an office')
        ->assertDontSee('href="'.route('file-management-systems.boxes.create').'"', false);
});

test('a branch user can open the create box form and create a box', function () {
    $this->actingAs($this->branchUser)
        ->get(route('file-management-systems.boxes.create'))
        ->assertSuccessful();

    $this->actingAs($this->branchUser)
        ->post(route('file-management-systems.boxes.store'), ['location' => 'Shelf A', 'capacity' => 50])
        ->assertRedirect(route('file-management-systems.boxes'));

    $this->assertDatabaseHas('boxes', ['location' => 'Shelf A', 'boxable_id' => $this->branch->id, 'created_by' => $this->branchUser->id]);
});

test('the seeded branch role can create and manage archive boxes', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    foreach (['branch', 'region', 'division', 'head-office'] as $roleName) {
        $role = Role::findByName($roleName);

        expect($role->hasPermissionTo('create boxes'))->toBeTrue("{$roleName} should create boxes")
            ->and($role->hasPermissionTo('manage boxes'))->toBeTrue("{$roleName} should manage boxes");
    }
});

test('a seeded branch manager sees the new box button on the file list', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $manager = User::whereNotNull('branch_id')->whereHas('roles', fn ($q) => $q->where('name', 'branch'))->first();

    $this->actingAs($manager)
        ->get(route('file-management-systems.index'))
        ->assertSuccessful()
        ->assertSee('New box');
});

test('the seeded roles can archive files into boxes', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    foreach (['branch', 'region', 'division', 'head-office'] as $roleName) {
        expect(Role::findByName($roleName)->hasPermissionTo('archive file management systems'))
            ->toBeTrue("{$roleName} should archive files");
    }
});

test('a seeded branch manager can archive an own file into a box from the file page', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('local');
    $manager = User::whereNotNull('branch_id')->whereHas('roles', fn ($q) => $q->where('name', 'branch'))->first();
    $document = FileManagementSystem::factory()->create([
        'status' => 'approved',
        'fileable_type' => 'branch',
        'fileable_id' => $manager->branch_id,
        'file_category_id' => $this->fileCategory->id,
        'created_by' => $manager->id,
    ]);
    $document->addMedia(UploadedFile::fake()->create('scan.pdf', 50, 'application/pdf'))->toMediaCollection('pages');

    $this->actingAs($manager)
        ->get(route('file-management-systems.show', $document))
        ->assertSuccessful()
        ->assertSee(route('file-management-systems.archive-form', $document), false);

    $this->actingAs($manager)->post(route('file-management-systems.boxes.store'), ['location' => 'Shelf B', 'capacity' => 10]);
    $box = Box::where('boxable_id', $manager->branch_id)->sole();

    $this->actingAs($manager)
        ->get(route('file-management-systems.archive-form', $document))
        ->assertSuccessful()
        ->assertSee($box->box_number);

    $this->actingAs($manager)
        ->post(route('file-management-systems.archive', $document), ['box_id' => $box->id])
        ->assertRedirect(route('file-management-systems.show', $document));

    expect($document->fresh()->is_archived)->toBeTrue();
});

function pendingTransferTo(FileManagementSystem $document, Branch $destination, User $requester, User $recipient): FileManagementTransfer
{
    return FileManagementTransfer::create([
        'file_management_system_id' => $document->id,
        'source_fileable_type' => $document->fileable_type,
        'source_fileable_id' => $document->fileable_id,
        'destination_fileable_type' => 'branch',
        'destination_fileable_id' => $destination->id,
        'recipient_id' => $recipient->id,
        'requested_by' => $requester->id,
        'reason' => 'Branch responsibility changed.',
        'status' => 'pending',
    ]);
}

test('incoming transfer filter lists only files with a pending transfer to my office, and matches the card', function () {
    $otherBranch = Branch::factory()->create();
    $otherUser = User::factory()->create(['branch_id' => $otherBranch->id]);
    $otherUser->assignRole('branch');

    $incoming = FileManagementSystem::factory()->create(['status' => 'approved', 'fileable_type' => 'branch', 'fileable_id' => $otherBranch->id, 'file_category_id' => $this->fileCategory->id]);
    $toSomeoneElse = FileManagementSystem::factory()->create(['status' => 'approved', 'fileable_type' => 'branch', 'fileable_id' => $otherBranch->id, 'file_category_id' => $this->fileCategory->id]);
    $own = FileManagementSystem::factory()->create(['status' => 'approved', 'fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'file_category_id' => $this->fileCategory->id]);
    $third = Branch::factory()->create();

    pendingTransferTo($incoming, $this->branch, $otherUser, $this->branchUser);
    pendingTransferTo($toSomeoneElse, $third, $otherUser, $otherUser);

    $response = $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['incoming_transfer' => 1]]))->assertSuccessful();

    $ids = $response->viewData('fileManagementSystems')->pluck('id')->all();
    expect($ids)->toBe([$incoming->id])
        ->and($response->viewData('stats')['incoming'])->toBe(count($ids));

    // The destination office can open the file to accept or reject, a bystander still cannot.
    $this->actingAs($this->branchUser)->get(route('file-management-systems.show', $incoming))->assertSuccessful();
    $this->actingAs($this->branchUser)->get(route('file-management-systems.show', $toSomeoneElse))->assertNotFound();

    // Without the filter the list is still only my own office's files.
    $plain = $this->actingAs($this->branchUser)->get(route('file-management-systems.index'))->viewData('fileManagementSystems')->pluck('id')->all();
    expect($plain)->toBe([$own->id]);
});

test('a user with no incoming transfers gets an empty list, not other offices files', function () {
    $otherBranch = Branch::factory()->create();
    FileManagementSystem::factory()->create(['status' => 'approved', 'fileable_type' => 'branch', 'fileable_id' => $otherBranch->id, 'file_category_id' => $this->fileCategory->id]);

    $response = $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['incoming_transfer' => 1]]))->assertSuccessful();

    expect($response->viewData('fileManagementSystems')->total())->toBe(0)
        ->and($response->viewData('stats')['incoming'])->toBe(0);
});

test('summary cards link to all files, the in circulation tab and incoming transfers', function () {
    $otherBranch = Branch::factory()->create();
    $otherUser = User::factory()->create(['branch_id' => $otherBranch->id]);
    $otherUser->assignRole('branch');
    $incoming = FileManagementSystem::factory()->create(['status' => 'approved', 'fileable_type' => 'branch', 'fileable_id' => $otherBranch->id, 'file_category_id' => $this->fileCategory->id]);
    pendingTransferTo($incoming, $this->branch, $otherUser, $this->branchUser);

    $page = $this->actingAs($this->branchUser)->get(route('file-management-systems.index'))->assertSuccessful();
    $page->assertSee('filter%5Bstatus%5D=active', false)
        ->assertSee('filter%5Bincoming_transfer%5D=1', false);

    // Already on the in-circulation tab: the card goes back to all files.
    $active = $this->actingAs($this->branchUser)->get(route('file-management-systems.index', ['filter' => ['status' => 'active']]))->getContent();
    preg_match_all('/<a href="([^"]*)" class="ak-kpi/', $active, $links);
    $circulationCard = html_entity_decode($links[1][1] ?? '');
    expect($circulationCard)->not->toContain('status');
});

test('a page just under the configured limit is accepted and one just over is rejected', function () {
    Storage::fake('local');
    config(['file_management.max_page_mb' => 50]);
    $limitKb = 50 * 1024;

    $payload = fn (int $sizeKb, string $title) => [
        'file_category_id' => $this->fileCategory->id,
        'fileable_type' => 'branch',
        'fileable_id' => $this->branch->id,
        'document_date' => '2026-08-01',
        'title' => $title,
        'pages' => [UploadedFile::fake()->create('scan.pdf', $sizeKb, 'application/pdf')],
    ];

    $this->actingAs($this->admin)->post(route('file-management-systems.store'), $payload($limitKb + 1, 'Too big'))
        ->assertSessionHasErrors('pages.0');

    $this->actingAs($this->admin)->post(route('file-management-systems.store'), $payload($limitKb, 'Just fits'))
        ->assertSessionDoesntHaveErrors();

    expect(FileManagementSystem::where('title', 'Just fits')->exists())->toBeTrue()
        ->and(FileManagementSystem::where('title', 'Too big')->exists())->toBeFalse();
});

test('create form help text shows the configured page limit', function () {
    config(['file_management.max_page_mb' => 35]);

    $this->actingAs($this->admin)->get(route('file-management-systems.create'))
        ->assertSuccessful()
        ->assertSee('up to 35 MB each')
        ->assertSee('const maxMb = 35;', false);
});

test('create form warns when an office list is empty and carries the offices when it is not', function () {
    HeadOffice::query()->delete();

    $this->actingAs($this->admin)->get(route('file-management-systems.create'))
        ->assertSuccessful()
        ->assertSee('data-empty-office-list', false)
        ->assertViewHas('headOffices', fn ($headOffices) => $headOffices->isEmpty());

    $this->seed(HeadOfficeSeeder::class);

    $this->actingAs($this->admin)->get(route('file-management-systems.create'))
        ->assertViewHas('headOffices', fn ($headOffices) => $headOffices->isNotEmpty());
});
