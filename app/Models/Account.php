<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * AOF #01/#02 + #16..#23 -- Particulars of Account aur Bank Use Only boxes.
 *
 * Ek account ke ek se zyada holders ho sakte hain (Individual form 4 tak,
 * Entity form 12 tak) -- isi liye account_holders pivot hai.
 */
class Account extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'initial_deposit' => 'decimal:2',
            'is_foreign_currency' => 'boolean',
            'sms_alerts_subscribed' => 'boolean',
            'digital_channels_opted' => 'boolean',
            'digital_channels_biometric_verified' => 'boolean',
            'zakat_applicable' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AccountProduct::class, 'account_product_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function operatingInstruction(): BelongsTo
    {
        return $this->belongsTo(OperatingInstruction::class);
    }

    public function statementDeliveryMode(): BelongsTo
    {
        return $this->belongsTo(StatementDeliveryMode::class);
    }

    public function statementFrequency(): BelongsTo
    {
        return $this->belongsTo(StatementFrequency::class);
    }

    public function mailingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'mailing_address_id');
    }

    public function holders(): HasMany
    {
        return $this->hasMany(AccountHolder::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'account_holders')
            ->withPivot(['holder_role', 'applicant_number', 'is_signatory', 'signing_order', 'designation', 'is_active'])
            ->withTimestamps();
    }

    public function chequeBooks(): HasMany
    {
        return $this->hasMany(AccountChequeBook::class);
    }

    public function debitCards(): HasMany
    {
        return $this->hasMany(AccountDebitCard::class);
    }

    public function dueDiligences(): HasMany
    {
        return $this->hasMany(CustomerDueDiligence::class);
    }

    public function ultimateBeneficialOwners(): HasMany
    {
        return $this->hasMany(UltimateBeneficialOwner::class);
    }

    public function openingRequest(): HasOne
    {
        return $this->hasOne(AccountOpeningRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    /** AOF: primary/pehla account holder. */
    public function primaryHolder(): ?AccountHolder
    {
        return $this->holders->firstWhere('holder_role', 'primary');
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Account has been {$eventName}");
    }
}
