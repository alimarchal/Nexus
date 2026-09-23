<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One loan inside a lodged AKSIC claim, with its reporting snapshot.
 */
class AksicClaimItem extends Model
{
    protected $fillable = [
        'aksic_claim_id',
        'aksic_id',
        'district_id',
        'region_id',
        'branch_id',
        'gender',
        'installments_count',
        'principal_outstanding',
        'markup_amount',
    ];

    protected function casts(): array
    {
        return [
            'installments_count' => 'integer',
            'principal_outstanding' => 'decimal:2',
            'markup_amount' => 'decimal:2',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(AksicClaim::class, 'aksic_claim_id');
    }

    public function aksic(): BelongsTo
    {
        return $this->belongsTo(Aksic::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
