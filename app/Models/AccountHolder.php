<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AOF #02/#30 -- Client/Relationship ID (1)..(4) aur Applicant's (1)..(12).
 *
 * Yeh pivot dono PDFs ko jorta hai: Individual form ke joint holders aur
 * Entity form ke authorized signatories, dono yahin aate hain.
 */
class AccountHolder extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    public const ROLE_PRIMARY = 'primary';

    public const ROLE_JOINT = 'joint';

    public const ROLE_SOLE_PROPRIETOR = 'sole_proprietor';

    public const ROLE_MINOR = 'minor';

    public const ROLE_GUARDIAN = 'guardian';

    public const ROLE_AUTHORIZED_SIGNATORY = 'authorized_signatory';

    public const ROLE_MANDATE_HOLDER = 'mandate_holder';

    public const ROLE_AGENT = 'agent';

    public const ROLE_EXECUTOR = 'executor';

    public const ROLE_TRUSTEE = 'trustee';

    public const ROLE_OFFICE_BEARER = 'office_bearer';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_signatory' => 'boolean',
            'is_active' => 'boolean',
            'applicant_number' => 'integer',
            'signing_order' => 'integer',
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

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class);
    }

    public function specimenSignatures(): HasMany
    {
        return $this->hasMany(SpecimenSignature::class);
    }

    public function activeSpecimenSignatures(): HasMany
    {
        return $this->specimenSignatures()->where('is_active', true);
    }
}
