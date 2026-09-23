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
 * AOF #03 -- Customer Information Form (CIF).
 *
 * Dono PDFs ka core table. `customer_type` decide karta hai kaunsa detail
 * record banega:
 *   individual      -> customerIndividual()
 *   sole_proprietor -> customerIndividual() + customerOrganization()  << bridge
 *   entity          -> customerOrganization()
 */
class Customer extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_SOLE_PROPRIETOR = 'sole_proprietor';

    public const TYPE_ENTITY = 'entity';

    public const FORM_INDIVIDUAL = 'individual_joint_sole';

    public const FORM_ENTITY = 'entity';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cif_date' => 'date',
            'cz50_submitted_on' => 'date',
            'is_zakat_exempt' => 'boolean',
            'is_pep' => 'boolean',
            'pep_form_attached' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Classification */
    /* ------------------------------------------------------------------ */

    public function branch(): BelongsTo
    {
        // Aapki mojooda branches table -- yahan koi nayi table nahi banayi gayi.
        return $this->belongsTo(Branch::class);
    }

    public function customerCategory(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class);
    }

    public function economicSector(): BelongsTo
    {
        return $this->belongsTo(EconomicSector::class);
    }

    public function zakatExemptionReason(): BelongsTo
    {
        return $this->belongsTo(ZakatExemptionReason::class);
    }

    public function specialCategories(): BelongsToMany
    {
        return $this->belongsToMany(SpecialCategory::class, 'customer_special_category')
            ->withPivot(['extra_value', 'other_description'])
            ->withTimestamps();
    }

    /* ------------------------------------------------------------------ */
    /* Detail profiles */
    /* ------------------------------------------------------------------ */

    public function individual(): HasOne
    {
        return $this->hasOne(CustomerIndividual::class);
    }

    public function organization(): HasOne
    {
        return $this->hasOne(CustomerOrganization::class);
    }

    public function nationalities(): HasMany
    {
        return $this->hasMany(CustomerNationality::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function identifications(): HasMany
    {
        return $this->hasMany(CustomerIdentification::class);
    }

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(CustomerNextOfKin::class);
    }

    public function taxResidencies(): HasMany
    {
        return $this->hasMany(CustomerTaxResidency::class);
    }

    public function fatcaDetail(): HasOne
    {
        return $this->hasOne(CustomerFatcaDetail::class);
    }

    public function controllingPersons(): HasMany
    {
        return $this->hasMany(CustomerControllingPerson::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    /* ------------------------------------------------------------------ */
    /* Accounts */
    /* ------------------------------------------------------------------ */

    public function accountHolders(): HasMany
    {
        return $this->hasMany(AccountHolder::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_holders')
            ->withPivot(['holder_role', 'applicant_number', 'is_signatory', 'signing_order', 'designation', 'is_active'])
            ->withTimestamps();
    }

    public function dueDiligences(): HasMany
    {
        return $this->hasMany(CustomerDueDiligence::class);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers */
    /* ------------------------------------------------------------------ */

    public function scopeIndividuals(Builder $query): Builder
    {
        return $query->whereIn('customer_type', [self::TYPE_INDIVIDUAL, self::TYPE_SOLE_PROPRIETOR]);
    }

    public function scopeEntities(Builder $query): Builder
    {
        return $query->whereIn('customer_type', [self::TYPE_ENTITY, self::TYPE_SOLE_PROPRIETOR]);
    }

    public function needsIndividualProfile(): bool
    {
        return in_array($this->customer_type, [self::TYPE_INDIVIDUAL, self::TYPE_SOLE_PROPRIETOR], true);
    }

    public function needsOrganizationProfile(): bool
    {
        return in_array($this->customer_type, [self::TYPE_ENTITY, self::TYPE_SOLE_PROPRIETOR], true);
    }

    /** CIF par chhapnay wala naam -- individual ho ya business. */
    public function displayName(): ?string
    {
        return $this->individual?->full_name ?? $this->organization?->business_name;
    }

    /** Primary CNIC/Passport number. */
    public function primaryIdentificationNumber(): ?string
    {
        return $this->identifications->firstWhere('is_primary', true)?->document_number;
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

    public function scopeCifDateFrom(Builder $query, string $date): Builder
    {
        return $query->whereDate('cif_date', '>=', $date);
    }

    public function scopeCifDateTo(Builder $query, string $date): Builder
    {
        return $query->whereDate('cif_date', '<=', $date);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Customer (CIF) has been {$eventName}");
    }
}
