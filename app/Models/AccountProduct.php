<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AOF #16/#17 -- Type of Account (Rupee) aur Type of Account (Foreign Currency).
 * Current Products : BAJK Current Account, BAJK Current Account-FCY
 * Saving Products  : PLS, SDA, BMBA, PPRSA, BAJK Saving Account-FCY
 */
class AccountProduct extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_zakat_applicable' => 'boolean',
            'allows_debit_card' => 'boolean',
            'allowed_cheque_leaves' => 'array',
            'is_other' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /** Cheque book requisition ke leaves ki validation (Ind 10/25/50, Ent 25/50/100). */
    public function allowsChequeLeaves(int $leaves): bool
    {
        return in_array($leaves, $this->allowed_cheque_leaves ?? [], true);
    }
}
