<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Bhara hua Account Opening Form khud -- T&C acceptance, Indemnity &
 * Undertaking, aur "I/We recommend to open the Account..." wala maker-checker
 * block (AOF #26, #28, #34).
 */
class AccountOpeningRequest extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    public const FORM_INDIVIDUAL = 'individual_joint_sole';

    public const FORM_ENTITY = 'entity';

    /** The current Account Opening Form. */
    public const EDITION_NEW = 'new';

    /** The old BAJK paper Account Opening Form. */
    public const EDITION_OLD = 'old';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'authorized_on' => 'date',
            'checked_on' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'locked_at' => 'datetime',
            'unlocked_at' => 'datetime',
            'terms_and_conditions_accepted' => 'boolean',
            'indemnity_undertaking_accepted' => 'boolean',
            'aof_copy_received_by_customer' => 'boolean',
            'shariah_compliant_investment_authorized' => 'boolean',
            'vernacular_form_attached' => 'boolean',
            'qa22_form_attached' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return HasOne<AccountOpeningPaperForm, $this>
     */
    public function paperForm(): HasOne
    {
        return $this->hasOne(AccountOpeningPaperForm::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AccountOpeningHistory::class)->oldest('created_at')->orderBy('id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    public function isOldEdition(): bool
    {
        return $this->form_edition === self::EDITION_OLD;
    }

    /** "Old" or "New": which Account Opening Form the request was made with. */
    public function editionLabel(): string
    {
        return $this->isOldEdition() ? 'Old' : 'New';
    }

    /** Approved requests are locked for everyone until a super admin unlocks them. */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /** Only a draft or a rejected request that is not locked can be edited. */
    public function isEditable(): bool
    {
        return ! $this->isLocked() && in_array($this->status, ['draft', 'rejected'], true);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    /**
     * Restrict the query to the org unit the given user belongs to.
     *
     * Mirrors FileManagementSystem::scopeVisibleTo(): super-admin and head-office
     * see every branch, region users see the branches of their region, and branch
     * users only see their own branch.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->is_super_admin === 'Yes' || $user->hasRole(['super-admin', 'head-office'])) {
            return $query;
        }

        if ($user->hasRole('branch') && $user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        if ($user->hasRole('region') && $user->region_id) {
            return $query->whereIn('branch_id', Branch::where('region_id', $user->region_id)->select('id'));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeBranchId(Builder $query, string $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRequestDateFrom(Builder $query, string $date): Builder
    {
        return $query->whereDate('request_date', '>=', $date);
    }

    public function scopeRequestDateTo(Builder $query, string $date): Builder
    {
        return $query->whereDate('request_date', '<=', $date);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Account opening request has been {$eventName}");
    }
}
