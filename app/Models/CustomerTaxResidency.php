<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #14 -- Individual/Entity Tax Residency Status (CRS self-certification).
 * Reason A/B/C form ke exact alfaaz ke mutabiq.
 */
class CustomerTaxResidency extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'customer_tax_residencies';

    public const REASON_NO_TIN_ISSUED = 'A';

    public const REASON_UNABLE_TO_OBTAIN = 'B';

    public const REASON_NOT_REQUIRED = 'C';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['sequence' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** Reason B choose kiya ho to explanation lazmi hai. */
    public function needsExplanation(): bool
    {
        return $this->no_tin_reason === self::REASON_UNABLE_TO_OBTAIN;
    }
}
