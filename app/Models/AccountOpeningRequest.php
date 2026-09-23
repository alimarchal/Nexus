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

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'authorized_on' => 'date',
            'checked_on' => 'date',
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
