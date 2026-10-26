<?php

use App\Models\Branch;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->branch = Branch::factory()->create();
    $this->maker = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->maker->assignRole('branch');
    $this->checker = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->checker->assignRole('branch');
    $this->outsider = User::factory()->create(['branch_id' => Branch::factory()->create()->id]);
    $this->outsider->assignRole('branch');
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

/**
 * Act as another user with a clean session: the session password hash of the previous user would log this one out.
 */
function actAsFmsUser(mixed $test, User $user): mixed
{
    $test->flushSession();
    app('auth')->forgetGuards();

    return $test->actingAs($user);
}

/**
 * A file of the test branch made by the maker, with one scanned page.
 */
function makerFile(mixed $test, string $status = 'draft'): FileManagementSystem
{
    $file = FileManagementSystem::factory()->create([
        'fileable_type' => 'branch', 'fileable_id' => $test->branch->id,
        'created_by' => $test->maker->id, 'status' => $status, 'title' => 'Loan file',
    ]);
    $file->addMedia(UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf'))->toMediaCollection('pages');

    return $file;
}

function activityEvents(FileManagementSystem $file): array
{
    return Activity::where('subject_type', $file->getMorphClass())->where('subject_id', $file->id)->oldest('id')->pluck('event')->all();
}

test('new files start as drafts and the office roles hold the maker-checker permissions', function () {
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.store'), [
        'file_category_id' => FileCategory::factory()->create()->id,
        'document_date' => now()->toDateString(), 'title' => 'New file', 'fileable_type' => 'branch', 'fileable_id' => $this->branch->id,
        'pages' => [UploadedFile::fake()->create('a.pdf', 20, 'application/pdf')],
    ])->assertSessionHasNoErrors();

    expect(FileManagementSystem::where('title', 'New file')->value('status'))->toBe('draft')
        ->and($this->maker->can('submit file management systems'))->toBeTrue()
        ->and($this->checker->can('approve file management systems'))->toBeTrue()
        ->and($this->maker->can('unlock file management systems'))->toBeFalse()
        ->and($this->superAdmin->can('unlock file management systems'))->toBeTrue();
});

test('the maker submits, a different user in the same branch approves, and the file is locked', function () {
    $file = makerFile($this);

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file))->assertSessionHas('success');
    expect($file->fresh())->status->toBe('submitted')->submitted_by->toBe($this->maker->id);

    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file))->assertSuccessful()->assertSee('Approve');
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file))->assertSessionHas('success');

    $file = $file->fresh();
    expect($file->status)->toBe('approved')->and($file->approved_by)->toBe($this->checker->id)->and($file->isLocked())->toBeTrue();
    expect(activityEvents($file))->toContain('submitted', 'approved');
});

test('the maker cannot approve or reject their own file', function () {
    $file = makerFile($this, 'submitted');
    $file->forceFill(['submitted_by' => $this->maker->id])->save();

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.approve', $file))->assertSessionHas('error');
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.reject', $file), ['reason' => 'x'])->assertSessionHas('error');
    actAsFmsUser($this, $this->superAdmin)->post(route('file-management-systems.approve', $file))->assertSessionHas('success');

    expect($file->fresh()->status)->toBe('approved');
});

test('a checker who edited the details or the pages of a draft is a maker and cannot approve or reject it', function () {
    $edit = fn (FileManagementSystem $file, User $user) => actAsFmsUser($this, $user)->put(route('file-management-systems.update', $file), ['title' => 'Edited by checker', 'file_category_id' => $file->file_category_id, 'document_date' => '2026-01-01']);

    $edited = makerFile($this);
    $edit($edited, $this->checker);
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $edited));

    $pagesChanged = makerFile($this);
    actAsFmsUser($this, $this->checker)->put(route('file-management-systems.update', $pagesChanged), ['title' => 'Loan file', 'file_category_id' => $pagesChanged->file_category_id, 'document_date' => '2026-01-01', 'pages' => [UploadedFile::fake()->create('extra.pdf', 20, 'application/pdf')]]);
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $pagesChanged));

    foreach ([$edited, $pagesChanged] as $file) {
        expect($file->fresh()->status)->toBe('submitted');

        actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file))
            ->assertSee('You created, edited or submitted this record, so a different user has to approve or reject it.');
        actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'a different user must approve or reject it'));
        actAsFmsUser($this, $this->checker)->post(route('file-management-systems.reject', $file), ['reason' => 'No'])->assertSessionHas('error');

        expect($file->fresh()->status)->toBe('submitted');
    }
});

test('a checker who only opened the record can still approve it, and an editor can approve when self approval is allowed', function () {
    $file = makerFile($this, 'submitted');
    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file));
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file))->assertSessionHas('success');

    config(['file_management.maker_checker.allow_self_approval' => true]);
    $editedFile = makerFile($this);
    actAsFmsUser($this, $this->checker)->put(route('file-management-systems.update', $editedFile), ['title' => 'Edited', 'file_category_id' => $editedFile->file_category_id, 'document_date' => '2026-01-01']);
    $editedFile->forceFill(['status' => 'submitted'])->save();
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $editedFile))->assertSessionHas('success');
});

test('self approval can be allowed by configuration', function () {
    config(['file_management.maker_checker.allow_self_approval' => true]);
    $file = makerFile($this, 'submitted');

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.approve', $file))->assertSessionHas('success');
});

test('a user from another branch cannot see or act on the file', function () {
    $file = makerFile($this, 'submitted');

    actAsFmsUser($this, $this->outsider)->get(route('file-management-systems.show', $file))->assertNotFound();
    actAsFmsUser($this, $this->outsider)->post(route('file-management-systems.approve', $file))->assertNotFound();
});

test('a rejection needs a reason, returns the file to the maker and it can be fixed and resubmitted', function () {
    $file = makerFile($this, 'submitted');

    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.reject', $file), ['reason' => ''])->assertSessionHasErrors('reason');
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.reject', $file), ['reason' => 'Page 2 is missing'])->assertSessionHas('success');

    expect($file->fresh())->status->toBe('rejected')->rejection_reason->toBe('Page 2 is missing');
    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.show', $file))->assertSee('Page 2 is missing');
    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.edit', $file))->assertSuccessful();

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file))->assertSessionHas('success');
    expect($file->fresh()->status)->toBe('submitted');
});

test('a file without a scanned page cannot be submitted', function () {
    $file = FileManagementSystem::factory()->create(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'created_by' => $this->maker->id, 'status' => 'draft']);

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file))->assertSessionHas('error');
    expect($file->fresh()->status)->toBe('draft');
});

test('nobody can edit, add pages to, remove pages from or delete a submitted or approved file', function () {
    foreach (['submitted', 'approved'] as $status) {
        $file = makerFile($this, $status);
        if ($status === 'approved') {
            $file->forceFill(['locked_at' => now()])->save();
        }
        $page = $file->media()->first();

        foreach ([$this->maker, $this->checker, $this->superAdmin] as $user) {
            actAsFmsUser($this, $user)->get(route('file-management-systems.edit', $file))->assertRedirect(route('file-management-systems.show', $file));
            actAsFmsUser($this, $user)->put(route('file-management-systems.update', $file), ['title' => 'Changed', 'file_category_id' => $file->file_category_id, 'document_date' => '2026-01-01'])
                ->assertRedirect(route('file-management-systems.show', $file));
            actAsFmsUser($this, $user)->delete(route('file-management-systems.media.destroy', [$file, $page->id]))->assertRedirect(route('file-management-systems.show', $file));
        }

        actAsFmsUser($this, $this->superAdmin)->delete(route('file-management-systems.destroy', $file))->assertRedirect(route('file-management-systems.show', $file));

        expect($file->fresh()->title)->toBe('Loan file')->and($file->fresh()->media()->count())->toBe(1)->and(FileManagementSystem::find($file->id))->not->toBeNull();
    }

    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.show', $file))->assertDontSee('Add or remove pages');
});

test('only a super admin can unlock one approved file, with a reason, and it needs approval again', function () {
    $file = makerFile($this, 'approved');
    $file->forceFill(['locked_at' => now(), 'approved_by' => $this->checker->id])->save();
    $other = makerFile($this, 'approved');
    $other->forceFill(['locked_at' => now()])->save();

    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.unlock', $file), ['reason' => 'Wrong date'])->assertForbidden();
    actAsFmsUser($this, $this->superAdmin)->post(route('file-management-systems.unlock', $file), ['reason' => ''])->assertSessionHasErrors('reason');
    actAsFmsUser($this, $this->superAdmin)->post(route('file-management-systems.unlock', $file), ['reason' => 'Wrong date on the file'])->assertSessionHas('success');

    $file = $file->fresh();
    expect($file)->status->toBe('draft')->unlock_reason->toBe('Wrong date on the file')->unlocked_by->toBe($this->superAdmin->id)
        ->and($file->isLocked())->toBeFalse()->and($file->isEditable())->toBeTrue()
        ->and($other->fresh()->isLocked())->toBeTrue();

    actAsFmsUser($this, $this->maker)->put(route('file-management-systems.update', $file), ['title' => 'Fixed title', 'file_category_id' => $file->file_category_id, 'document_date' => '2026-01-01'])
        ->assertRedirect(route('file-management-systems.index'));
    expect($file->fresh()->title)->toBe('Fixed title');

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file))->assertSessionHas('success');
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file))->assertSessionHas('success');
    expect($file->fresh()->isLocked())->toBeTrue();

    $unlock = Activity::where('event', 'unlocked')->firstOrFail();
    expect((int) $unlock->causer_id)->toBe($this->superAdmin->id)->and($unlock->properties['note'])->toBe('Wrong date on the file');
});

test('the history records who made, submitted, approved, edited, opened and unlocked the file', function () {
    $file = makerFile($this);

    actAsFmsUser($this, $this->maker)->put(route('file-management-systems.update', $file), ['title' => 'Loan file v2', 'file_category_id' => $file->file_category_id, 'document_date' => '2026-01-01']);
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file));
    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file));
    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file));
    actAsFmsUser($this, $this->superAdmin)->post(route('file-management-systems.unlock', $file), ['reason' => 'Audit correction']);

    $events = Activity::where('subject_id', $file->id)->get();

    expect($events->pluck('event')->all())->toContain('updated', 'submitted', 'viewed', 'approved', 'unlocked')
        ->and((int) $events->firstWhere('event', 'viewed')->causer_id)->toBe($this->checker->id)
        ->and((int) $events->firstWhere('event', 'updated')->causer_id)->toBe($this->maker->id)
        ->and($events->firstWhere('event', 'updated')->properties['old']['title'])->toBe('Loan file')
        ->and($events->firstWhere('event', 'updated')->properties['attributes']['title'])->toBe('Loan file v2');

    actAsFmsUser($this, $this->superAdmin)->get(route('file-management-systems.show', $file))
        ->assertSee('Loan file v2')->assertSee($this->checker->name)->assertSee('Audit correction')->assertSee('Viewed');
});

test('workflow changes do not add noise events and reloading does not flood the history with views', function () {
    $file = makerFile($this);

    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file));
    expect(activityEvents($file))->not->toContain('updated');

    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file));
    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.show', $file));
    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.show', $file));

    expect(array_count_values(activityEvents($file))['viewed'])->toBe(2);
});

test('opening a scanned page is recorded with who opened which file', function () {
    $file = makerFile($this);
    $page = $file->media()->first();

    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.pages.show', [$file, $page->id]))->assertSuccessful();
    actAsFmsUser($this, $this->outsider)->get(route('file-management-systems.pages.show', [$file, $page->id]))->assertNotFound();

    $log = Activity::where('event', 'page_viewed')->get();
    expect($log)->toHaveCount(1)->and((int) $log->first()->causer_id)->toBe($this->checker->id)->and($log->first()->properties['stored_filename'])->toBe($page->file_name);
});

test('the list shows and filters by approval status', function () {
    $draft = makerFile($this);
    $approved = makerFile($this, 'approved');
    $approved->forceFill(['locked_at' => now(), 'title' => 'Approved loan file'])->save();

    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.index'))->assertSee('data-approval="draft"', false)->assertSee('· locked');
    actAsFmsUser($this, $this->checker)->get(route('file-management-systems.index', ['filter' => ['approval' => 'approved']]))
        ->assertSee('Approved loan file')->assertDontSee($draft->digital_id);
});

test('with maker-checker switched off files need no approval and stay editable', function () {
    config(['file_management.maker_checker.enabled' => false]);
    $file = makerFile($this, 'approved');
    $file->forceFill(['locked_at' => now()])->save();

    expect($file->fresh()->isEditable())->toBeTrue();
    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.edit', $file))->assertSuccessful();
    actAsFmsUser($this, $this->maker)->get(route('file-management-systems.show', $file))->assertDontSee('Approval (maker-checker)');
    actAsFmsUser($this, $this->maker)->post(route('file-management-systems.submit', $file))->assertSessionHas('error');
});

test('records that existed before maker-checker stay approved and editable', function () {
    $legacy = FileManagementSystem::factory()->create(['fileable_type' => 'branch', 'fileable_id' => $this->branch->id, 'status' => 'approved']);

    expect($legacy->isEditable())->toBeTrue();
});

test('the permissions can be taken away from a user to remove the buttons', function () {
    $file = makerFile($this, 'submitted');
    $this->checker->revokePermissionTo('approve file management systems');
    $this->checker->roles->each->revokePermissionTo('approve file management systems');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    actAsFmsUser($this, $this->checker)->post(route('file-management-systems.approve', $file))->assertForbidden();
    expect(Permission::where('name', 'approve file management systems')->exists())->toBeTrue();
});
