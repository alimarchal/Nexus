<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AOF #31/#32/#33 -- "For Bank Use Only" ke teeno CDD boxes:
 *   Customer Due Diligence Individual Account         (AOF-Ind p.14)
 *   Customer Due Diligence Business Account (Sole Prop) (AOF-Ind p.15)
 *   Customer Due Diligence Business Account            (AOF-Ent p.17)
 *
 * Teeno ek hi table mein hain; `cdd_type` batata hai kaunsa box bhara gaya.
 */
class CustomerDueDiligence extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_SOLE_PROPRIETOR = 'sole_proprietor';

    public const TYPE_BUSINESS = 'business';

    public const SOURCE_WALK_IN = 'walk_in';

    public const SOURCE_MARKETED = 'marketed';

    public const SOURCE_REFERRED = 'referred';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'physical_verification_conducted' => 'boolean',
            'proscribed_list_cleared' => 'boolean',
            'is_dnfbp' => 'boolean',
            'monthly_income' => 'decimal:2',
            'monthly_net_income' => 'decimal:2',
            'initial_deposit' => 'decimal:2',
            'expected_monthly_credit_amount' => 'decimal:2',
            'expected_monthly_debit_amount' => 'decimal:2',
            'expected_iftt_amount' => 'decimal:2',
            'expected_oftt_amount' => 'decimal:2',
            'expected_monthly_credit_count' => 'integer',
            'expected_monthly_debit_count' => 'integer',
            'number_of_employees' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function fundProviderRelationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class, 'fund_provider_relationship_id');
    }

    public function homeRemittanceCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'home_remittance_country_id');
    }

    /* ---- Multi-select checkbox groups ---- */

    public function incomeSources(): BelongsToMany
    {
        return $this->belongsToMany(IncomeSource::class, 'cdd_income_source')
            ->withPivot('other_description')->withTimestamps();
    }

    public function wealthSources(): BelongsToMany
    {
        return $this->belongsToMany(WealthSource::class, 'cdd_wealth_source')
            ->withPivot('other_description')->withTimestamps();
    }

    public function transactionModes(): BelongsToMany
    {
        return $this->belongsToMany(TransactionMode::class, 'cdd_transaction_mode')
            ->withPivot(['direction', 'other_description'])->withTimestamps();
    }

    public function creditModes(): BelongsToMany
    {
        return $this->transactionModes()->wherePivot('direction', 'credit');
    }

    public function debitModes(): BelongsToMany
    {
        return $this->transactionModes()->wherePivot('direction', 'debit');
    }

    public function accountPurposes(): BelongsToMany
    {
        return $this->belongsToMany(AccountPurpose::class, 'cdd_account_purpose')
            ->withPivot('other_description')->withTimestamps();
    }

    public function counterPartyTypes(): BelongsToMany
    {
        return $this->belongsToMany(CounterPartyType::class, 'cdd_counter_party_type')
            ->withPivot('other_description')->withTimestamps();
    }

    public function ultimateBeneficialOwners(): HasMany
    {
        return $this->hasMany(UltimateBeneficialOwner::class);
    }
}
