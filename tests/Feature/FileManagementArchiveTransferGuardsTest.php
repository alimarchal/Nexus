<?php

use App\Models\Box;
use App\Models\Branch;
use App\Models\FileManagementSystem;
use App\Models\FileManagementTransfer;
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
    $file = FileManagementSystem::factory()->create($attributes + ['fileable_type' => 'branch', 'fileable_id' => $branch->id, 'status' => 'approved']);
    $file->addMedia(UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf'))->toMediaCollection('pages');

    return $file;
}

function guardBox(Branch $branch, array $attributes = []): Box
{
    return Box::factory()->create($attributes + ['boxable_type' => 'branch', 'boxable_id' => $branch->id, 'status' => 'open']);
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

function guardTransfer(FileManagementSystem $file, User $requester, Branch $destination, User $recipient): FileManagementTransfer
{
    return FileManagementTransfer::create([
        'file_management_system_id' => $file->id,
        'source_fileable_type' => 'branch', 'source_fileable_id' => $file->fileable_id,
        'destination_fileable_type' => 'branch', 'destination_fileable_id' => $destination->id,
        'recipient_id' => $recipient->id, 'requested_by' => $requester->id, 'reason' => 'Office change',
    ]);
}

test('the user who requested a transfer cannot approve or reject it, a different user can', function () {
    $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    $recipient = User::factory()->create(['branch_id' => $this->otherBranch->id]);
    $file = guardFile($this->branch);
    $transfer = guardTransfer($file, $superAdmin, $this->otherBranch, $recipient);
    $decide = route('file-management-systems.transfers.decide', [$file, $transfer]);

    $this->actingAs($superAdmin)->patch($decide, ['decision' => 'approved'])
        ->assertRedirect(route('file-management-systems.show', $file))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'a different user must approve or reject it'));
    $this->actingAs($superAdmin)->patch($decide, ['decision' => 'rejected', 'decision_note' => 'No'])->assertSessionHas('error');
    expect($transfer->fresh()->status)->toBe('pending')->and($file->fresh()->fileable_id)->toBe($this->branch->id);

    $otherAdmin = User::factory()->create(['is_super_admin' => 'Yes']);
    $otherAdmin->assignRole('super-admin');
    $this->flushSession();
    app('auth')->forgetGuards();
    $this->actingAs($otherAdmin)->patch($decide, ['decision' => 'approved'])->assertSessionHas('success');
    expect($transfer->fresh()->status)->toBe('approved')->and($file->fresh()->fileable_id)->toBe($this->otherBranch->id);
});

test('the requester does not see the approve and reject buttons and is told why', function () {
    $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    $file = guardFile($this->branch);
    guardTransfer($file, $superAdmin, $this->otherBranch, User::factory()->create(['branch_id' => $this->otherBranch->id]));

    $this->actingAs($superAdmin)->get(route('file-management-systems.show', $file))
        ->assertSee('You requested this transfer, so a different user has to approve or reject it.')
        ->assertDontSee('Confirm reject');
});

test('self approval of a transfer follows the file self approval setting', function () {
    config(['file_management.maker_checker.allow_self_approval' => true]);
    $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    $file = guardFile($this->branch);
    $transfer = guardTransfer($file, $superAdmin, $this->otherBranch, User::factory()->create(['branch_id' => $this->otherBranch->id]));

    $this->actingAs($superAdmin)->patch(route('file-management-systems.transfers.decide', [$file, $transfer]), ['decision' => 'approved'])->assertSessionHas('success');
    expect($transfer->fresh()->status)->toBe('approved');
});

test('a file that is not approved cannot be transferred or archived and the screens say why', function (string $status, string $expected) {
    $file = guardFile($this->branch, ['status' => $status]);
    $box = guardBox($this->branch);
    $recipient = User::factory()->create(['branch_id' => $this->otherBranch->id]);
    $show = route('file-management-systems.show', $file);

    $this->actingAs($this->user)->get($show)
        ->assertSee($expected)
        ->assertDontSee(route('file-management-systems.transfer', $file), false)
        ->assertDontSee(route('file-management-systems.archive-form', $file), false);

    $this->actingAs($this->user)->get(route('file-management-systems.transfer', $file))->assertRedirect($show)->assertSessionHas('error', fn (string $message) => str_contains($message, $expected));
    $this->actingAs($this->user)->post(route('file-management-systems.transfers.store', $file), [
        'destination_fileable_type' => 'branch', 'destination_fileable_id' => $this->otherBranch->id,
        'recipient_id' => $recipient->id, 'reason' => 'Move',
    ])->assertRedirect($show)->assertSessionHas('error', fn (string $message) => str_contains($message, $expected));
    $this->actingAs($this->user)->get(route('file-management-systems.archive-form', $file))->assertRedirect($show)->assertSessionHas('error', fn (string $message) => str_contains($message, $expected));
    $this->actingAs($this->user)->post(route('file-management-systems.archive', $file), ['box_id' => $box->id])->assertRedirect($show)->assertSessionHas('error', fn (string $message) => str_contains($message, $expected));

    expect($file->fresh()->is_archived)->toBeFalsy()->and($box->fresh()->file_count)->toBe(0)->and($file->transfers()->count())->toBe(0);
})->with([
    'draft' => ['draft', 'has not been approved yet, so it cannot be'],
    'rejected' => ['rejected', 'has not been approved yet, so it cannot be'],
    'waiting for the checker' => ['submitted', 'is waiting for the checker, so it cannot be'],
]);

test('an approved file can be transferred and archived, also when it is locked', function () {
    $file = guardFile($this->branch, ['locked_at' => now()]);
    $box = guardBox($this->branch);

    $this->actingAs($this->user)->get(route('file-management-systems.transfer', $file))->assertSuccessful();
    $this->actingAs($this->user)->post(route('file-management-systems.archive', $file), ['box_id' => $box->id])->assertSessionHas('success');

    expect($file->fresh()->is_archived)->toBeTrue();
});

test('with maker-checker switched off every file can be transferred and archived', function () {
    config(['file_management.maker_checker.enabled' => false]);
    $file = guardFile($this->branch, ['status' => 'draft']);

    $this->actingAs($this->user)->get(route('file-management-systems.transfer', $file))->assertSuccessful();
});
