<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FileManagementWorkflowException;
use App\Models\FileManagementSystem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Maker-checker for File Management records.
 *
 *   draft / rejected --submit (maker)--> submitted --approve (checker)--> approved + LOCKED
 *                                            |
 *                                            +--reject (checker, reason)--> rejected (editable, resubmit)
 *   approved + locked --unlock (super admin, reason, one record)--> draft
 *
 * The checker must be a different person from the maker (the creator and the submitter) unless maker-checker is
 * switched off or self-approval is allowed (config/file_management.php). Every action, every view of the record
 * and every page opened is written to the record's history (the activity log) with who and when.
 */
final class FileManagementWorkflow
{
    /** The same person opening a record again within this many minutes is not logged again. */
    private const VIEW_LOG_MINUTES = 5;

    public function makerCheckerEnabled(): bool
    {
        return (bool) config('file_management.maker_checker.enabled', true);
    }

    public function selfApprovalAllowed(): bool
    {
        return ! $this->makerCheckerEnabled() || (bool) config('file_management.maker_checker.allow_self_approval', false);
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }

    /** The people who made the record: whoever created it and whoever submitted it. */
    public function isMaker(FileManagementSystem $file, User $user): bool
    {
        return (int) $file->created_by === $user->id || (int) $file->submitted_by === $user->id;
    }

    public function canSubmit(FileManagementSystem $file, User $user): bool
    {
        return $this->makerCheckerEnabled()
            && in_array($file->status, ['draft', 'rejected'], true)
            && ! $file->isLocked()
            && $user->can('submit file management systems');
    }

    /** Whether this user may approve or reject the record right now. */
    public function canDecide(FileManagementSystem $file, User $user): bool
    {
        return $this->makerCheckerEnabled()
            && $file->status === 'submitted'
            && ! $file->isLocked()
            && $user->can('approve file management systems')
            && ($this->selfApprovalAllowed() || ! $this->isMaker($file, $user));
    }

    public function canUnlock(FileManagementSystem $file, User $user): bool
    {
        return $file->isLocked() && $user->can('unlock file management systems') && $this->isSuperAdmin($user);
    }

    /**
     * Stop an edit of a record that is locked or waiting for its checker.
     */
    public function assertEditable(FileManagementSystem $file): void
    {
        if ($file->isEditable()) {
            return;
        }

        throw new FileManagementWorkflowException(
            $file->isLocked()
                ? 'This record is approved and locked. Only a super admin can unlock it for changes.'
                : 'This record has been submitted and is waiting for the checker. It cannot be edited until it is rejected back to the maker.'
        );
    }

    public function submit(FileManagementSystem $file, User $user): void
    {
        if (! $this->makerCheckerEnabled()) {
            throw new FileManagementWorkflowException('Maker-checker is switched off, so records do not need approval.');
        }

        if (! in_array($file->status, ['draft', 'rejected'], true) || $file->isLocked()) {
            throw new FileManagementWorkflowException('Only a draft or a rejected record can be submitted.');
        }

        if (! $file->media()->exists()) {
            throw new FileManagementWorkflowException('Upload at least one scanned page before submitting the record.');
        }

        DB::transaction(function () use ($file, $user): void {
            $resubmitted = $file->status === 'rejected';

            $file->forceFill([
                'status' => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
            ])->save();

            $this->record($file, 'submitted', $user, $resubmitted ? 'Resubmitted after the rejection.' : null);
        });
    }

    public function approve(FileManagementSystem $file, User $user): void
    {
        $this->assertCanDecide($file, $user);

        DB::transaction(function () use ($file, $user): void {
            $lock = (bool) config('file_management.lock_after_approval', true);

            $file->forceFill([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'locked_at' => $lock ? now() : null,
            ])->save();

            $this->record($file, 'approved', $user, $lock ? 'Approved and locked.' : 'Approved.');
        });
    }

    public function reject(FileManagementSystem $file, User $user, string $reason): void
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new FileManagementWorkflowException('Please give the reason for rejecting this record.');
        }

        $this->assertCanDecide($file, $user);

        DB::transaction(function () use ($file, $user, $reason): void {
            $file->forceFill([
                'status' => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ])->save();

            $this->record($file, 'rejected', $user, $reason);
        });
    }

    /**
     * A super admin reopens one approved record for changes. It goes back to draft and must be approved again.
     */
    public function unlock(FileManagementSystem $file, User $user, string $reason): void
    {
        if (! $this->canUnlock($file, $user)) {
            throw new FileManagementWorkflowException($file->isLocked()
                ? 'Only a super admin can unlock an approved record.'
                : 'This record is not locked.');
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw new FileManagementWorkflowException('Please give the reason for unlocking this record.');
        }

        DB::transaction(function () use ($file, $user, $reason): void {
            $file->forceFill([
                'status' => 'draft',
                'locked_at' => null,
                'approved_by' => null,
                'approved_at' => null,
                'unlocked_by' => $user->id,
                'unlocked_at' => now(),
                'unlock_reason' => $reason,
            ])->save();

            $this->record($file, 'unlocked', $user, $reason);
        });
    }

    /**
     * Log that a user opened the record, at most once every few minutes per user so reloading does not flood it.
     */
    public function recordView(FileManagementSystem $file, User $user): void
    {
        $recent = Activity::query()
            ->where('subject_type', $file->getMorphClass())
            ->where('subject_id', $file->getKey())
            ->where('causer_id', $user->id)
            ->where('event', 'viewed')
            ->where('created_at', '>=', now()->subMinutes(self::VIEW_LOG_MINUTES))
            ->exists();

        if (! $recent) {
            $this->record($file, 'viewed', $user, 'Opened the record.');
        }
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(FileManagementSystem $file, string $event, ?User $user, ?string $note = null, array $properties = []): void
    {
        activity('file-management')
            ->performedOn($file)
            ->causedBy($user)
            ->event($event)
            ->withProperties($properties + array_filter(['note' => $note]))
            ->log($note ?? match ($event) {
                'submitted' => 'Submitted for approval.',
                default => 'File management '.$event,
            });
    }

    private function assertCanDecide(FileManagementSystem $file, User $user): void
    {
        if ($file->status !== 'submitted' || $file->isLocked()) {
            throw new FileManagementWorkflowException('Only a submitted record can be approved or rejected.');
        }

        if (! $user->can('approve file management systems')) {
            throw new FileManagementWorkflowException('You do not have permission to approve file management records.');
        }

        if (! $this->selfApprovalAllowed() && $this->isMaker($file, $user)) {
            throw new FileManagementWorkflowException('Maker-checker: you created or submitted this record, so a different user must approve or reject it.');
        }
    }
}
