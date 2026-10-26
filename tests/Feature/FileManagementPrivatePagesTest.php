<?php

use App\Models\Branch;
use App\Models\FileManagementSystem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->branch = Branch::factory()->create();
    $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->user->assignRole('branch');

    $this->file = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'created_by' => $this->user->id,
    ]);
    $this->page = $this->file->addMedia(UploadedFile::fake()->image('scan.jpg'))->toMediaCollection('pages');
});

function pageViewedCount(FileManagementSystem $file): int
{
    return Activity::where('subject_id', $file->id)->where('event', 'page_viewed')->count();
}

test('scanned pages are stored on the private disk, not the public one', function () {
    $path = $this->page->getPathRelativeToRoot();

    expect($this->page->disk)->toBe('local');
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
});

test('the record and edit screens link pages only through the protected route', function () {
    $open = route('file-management-systems.pages.show', [$this->file, $this->page->id]);

    foreach (['show', 'edit'] as $screen) {
        $this->actingAs($this->user)->get(route("file-management-systems.{$screen}", $this->file))
            ->assertSuccessful()
            ->assertSee($open, false)
            ->assertDontSee('/storage/', false);
    }
});

test('opening a page is recorded, a thumbnail preview is not', function () {
    $this->actingAs($this->user)->get(route('file-management-systems.pages.show', [$this->file, $this->page->id, 'preview' => 1]))->assertSuccessful();
    expect(pageViewedCount($this->file))->toBe(0);

    $this->actingAs($this->user)->get(route('file-management-systems.pages.show', [$this->file, $this->page->id]))->assertSuccessful();
    expect(pageViewedCount($this->file))->toBe(1);
});

test('a guest and a user of another office cannot read a page', function () {
    $url = route('file-management-systems.pages.show', [$this->file, $this->page->id, 'preview' => 1]);

    $this->get($url)->assertRedirect(route('login'));

    $outsider = User::factory()->create(['branch_id' => Branch::factory()->create()->id]);
    $outsider->assignRole('branch');
    $this->actingAs($outsider)->get($url)->assertNotFound();
});

test('the migration moves existing public pages to the private disk and keeps a missing file untouched', function () {
    $moved = $this->file->addMedia(UploadedFile::fake()->create('old.pdf', 10, 'application/pdf'))->toMediaCollection('pages', 'public');
    $missing = $this->file->addMedia(UploadedFile::fake()->create('gone.pdf', 10, 'application/pdf'))->toMediaCollection('pages', 'public');
    $path = $moved->getPathRelativeToRoot();
    Storage::disk('public')->delete($missing->getPathRelativeToRoot());

    (require database_path('migrations/2026_10_26_100000_move_file_management_pages_to_private_disk.php'))->up();

    expect($moved->refresh()->disk)->toBe('local')->and($moved->conversions_disk)->toBe('local');
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
    expect($missing->refresh()->disk)->toBe('public')
        ->and(Media::where('disk', 'public')->count())->toBe(1);
});
