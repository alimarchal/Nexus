<?php

use App\Models\Box;
use App\Models\Branch;
use App\Models\FileManagementSystem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->branch = Branch::factory()->create();
    $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->user->assignRole('branch');
    $this->otherBranch = Branch::factory()->create();
});

function guardFile(Branch $branch, array $attributes = []): FileManagementSystem
{
    $file = FileManagementSystem::factory()->create(['fileable_type' => 'branch', 'fileable_id' => $branch->id, 'status' => 'approved'] + $attributes);
    $file->addMedia(UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf'))->toMediaCollection('pages');

    return $file;
}

function guardBox(Branch $branch, array $attributes = []): Box
{
    return Box::factory()->create(['boxable_type' => 'branch', 'boxable_id' => $branch->id, 'status' => 'open'] + $attributes);
}

test('a user cannot archive a file of another office by sending the request directly', function () {
    $foreignFile = guardFile($this->otherBranch);
    $foreignBox = guardBox($this->otherBranch);

    $this->actingAs($this->user)->post(route('file-management-systems.archive', $foreignFile), ['box_id' => $foreignBox->id])
        ->assertNotFound();

    expect($foreignFile->fresh()->is_archived)->toBeFalsy()
        ->and($foreignBox->fresh()->file_count)->toBe(0);
});

test('a user can still archive a file of their own office', function () {
    $file = guardFile($this->branch);
    $box = guardBox($this->branch);

    $this->actingAs($this->user)->post(route('file-management-systems.archive', $file), ['box_id' => $box->id])
        ->assertRedirect(route('file-management-systems.show', $file));

    expect($file->fresh()->is_archived)->toBeTrue()->and($box->fresh()->file_count)->toBe(1);
});
