<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AccountOpeningWorkflowException;
use App\Models\AccountOpeningHistory;
use App\Models\AccountOpeningRequest;
use App\Models\User;
use App\Support\AccountOpeningSnapshot;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Maker-checker for Account Opening requests.
 *
 *   draft / rejected --submit (maker)--> submitted --approve (checker)--> approved + LOCKED
 *                                            |
 *                                            +--reject (checker, reason)--> rejected (editable, resubmit)
 *   approved + locked --unlock (super admin, reason, one request)--> draft
 *
 * The checker must be a different person from the maker (the creator and the submitter)
 * unless maker-checker is switched off or self-approval is allowed (config/account_opening.php).
 * Every step save and every action is written to the request's history with old -> new values.
 */
final class AccountOpeningWorkflow
{
    /** Statuses a checker can still decide on. */
    private const REVIEWABLE = ['submitted', 'under_review'];

    public function makerCheckerEnabled(): bool
    {
        return (bool) config('account_opening.maker_checker.enabled', true);
    }

    public function selfApprovalAllowed(): bool
    {
        return ! $this->makerCheckerEnabled() || (bool) config('account_opening.maker_checker.allow_self_approval', false);
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }

    /** The people who made the request: whoever started it and whoever submitted it. */
    public function isMaker(AccountOpeningRequest $request, User $user): bool
    {
        return (int) $request->created_by === $user->id || (int) $request->submitted_by === $user->id;
    }

    /** Whether this user may approve or reject the request right now. */
    public function canDecide(AccountOpeningRequest $request, User $user): bool
    {
        return in_array($request->status, self::REVIEWABLE, true)
            && ! $request->isLocked()
            && $user->can('approve account openings')
            && ($this->selfApprovalAllowed() || ! $this->isMaker($request, $user));
    }

    /**
     * Stop an edit of a request that is locked or already waiting for a decision.
     */
    public function assertEditable(AccountOpeningRequest $request): void
    {
        if ($request->isLocked()) {
            throw new AccountOpeningWorkflowException('This request is approved and locked. Only a super admin can unlock it for changes.');
        }

        if (! in_array($request->status, ['draft', 'rejected'], true)) {
            throw new AccountOpeningWorkflowException('This request has been submitted and is waiting for the checker. It cannot be edited until it is rejected back to the maker.');
        }
    }

    public function submit(AccountOpeningRequest $request, User $user): void
    {
        if (! in_array($request->status, ['draft', 'rejected'], true) || $request->isLocked()) {
            throw new AccountOpeningWorkflowException('Only a draft or a rejected request can be submitted.');
        }

        DB::transaction(function () use ($request, $user): void {
            $request->update([
                'status' => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
            ]);
            $request->customer?->update(['status' => 'pending_verification']);

            $this->record($request, 'submitted', $user, note: $request->rejection_reason ? 'Resubmitted after the rejection.' : null);
        });
    }

    public function approve(AccountOpeningRequest $request, User $user): void
    {
        $this->assertCanDecide($request, $user);

        DB::transaction(function () use ($request, $user): void {
            $request->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'authorized_by_name' => $user->name,
                'authorized_on' => now()->toDateString(),
                'rejection_reason' => null,
                'locked_at' => config('account_opening.lock_after_approval', true) ? now() : null,
            ]);

            $request->customer?->update(['status' => 'active', 'approved_by' => $user->id, 'approved_at' => now()]);
            $request->account?->update(['status' => 'active', 'approved_by' => $user->id, 'approved_at' => now()]);

            $this->record($request, 'approved', $user, note: $request->isLocked() ? 'Approved and locked.' : 'Approved.');
        });
    }

    public function reject(AccountOpeningRequest $request, User $user, string $reason): void
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new AccountOpeningWorkflowException('Please give a reason so the maker knows what to correct.');
        }

        $this->assertCanDecide($request, $user);

        DB::transaction(function () use ($request, $user, $reason): void {
            $request->update([
                'status' => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);
            $request->customer?->update(['status' => 'draft']);

            $this->record($request, 'rejected', $user, note: $reason);
        });
    }

    /**
     * Re-open one locked, approved request for changes. It returns to draft and has to be
     * submitted and approved again, which locks it once more.
     */
    public function unlock(AccountOpeningRequest $request, User $user, string $reason): void
    {
        if (! $this->isSuperAdmin($user)) {
            throw new AccountOpeningWorkflowException('Only a super admin can unlock an approved request.');
        }

        if (! $request->isLocked()) {
            throw new AccountOpeningWorkflowException('This request is not locked.');
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw new AccountOpeningWorkflowException('Please give the reason for unlocking this request.');
        }

        DB::transaction(function () use ($request, $user, $reason): void {
            $request->update([
                'status' => 'draft',
                'locked_at' => null,
                'approved_by' => null,
                'approved_at' => null,
                'unlocked_by' => $user->id,
                'unlocked_at' => now(),
                'unlock_reason' => $reason,
            ]);
            $request->customer?->update(['status' => 'draft']);
            $request->account?->update(['status' => 'pending']);

            $this->record($request, 'unlocked', $user, note: $reason);
        });
    }

    /**
     * Run a step save and write what it changed (old -> new) to the history.
     */
    public function trackStep(AccountOpeningRequest $request, string $step, User $user, Closure $save): void
    {
        $before = AccountOpeningSnapshot::capture($request);

        $save();

        $changes = AccountOpeningSnapshot::diff($before, AccountOpeningSnapshot::capture($request));

        if ($changes !== []) {
            $this->record($request, 'step_saved', $user, $step, $changes);
        }
    }

    /**
     * @param  array<string, array{old: string|null, new: string|null}>|null  $changes
     */
    public function record(AccountOpeningRequest $request, string $event, ?User $user, ?string $step = null, ?array $changes = null, ?string $note = null): AccountOpeningHistory
    {
        return $request->histories()->create([
            'event' => $event,
            'step' => $step,
            'user_id' => $user?->id,
            'changes' => $changes,
            'note' => $note,
        ]);
    }

    private function assertCanDecide(AccountOpeningRequest $request, User $user): void
    {
        if (! in_array($request->status, self::REVIEWABLE, true) || $request->isLocked()) {
            throw new AccountOpeningWorkflowException('Only a submitted request can be approved or rejected.');
        }

        if (! $user->can('approve account openings')) {
            throw new AccountOpeningWorkflowException('You do not have permission to approve account openings.');
        }

        if (! $this->selfApprovalAllowed() && $this->isMaker($request, $user)) {
            throw new AccountOpeningWorkflowException('Maker-checker: you created or submitted this request, so a different user must approve or reject it.');
        }
    }
}
