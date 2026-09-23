<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A lodged AKSIC claim (Portal Change #5).
 */
class AksicClaim extends Model
{
    use HasUuids, SoftDeletes, UserTracking;

    public const STATUSES = ['Lodged', 'Settled', 'Rejected'];

    public const GENDERS = ['Male', 'Female', 'Transgender'];

    protected $fillable = [
        'claim_no',
        'claim_date',
        'period_from',
        'period_to',
        'district_id',
        'region_id',
        'branch_id',
        'gender',
        'total_loans',
        'total_principal_outstanding',
        'total_markup',
        'status',
        'status_date',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'period_from' => 'date',
            'period_to' => 'date',
            'status_date' => 'date',
            'total_loans' => 'integer',
            'total_principal_outstanding' => 'decimal:2',
            'total_markup' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(AksicClaimItem::class);
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

    /**
     * Human summary of the filters the claim was lodged for.
     */
    public function filterLabel(): string
    {
        $parts = array_filter([
            $this->district?->name ? 'District: '.$this->district->name : null,
            $this->region?->name ? 'Region: '.$this->region->name : null,
            $this->branch ? 'Branch: '.$this->branch->code.' - '.$this->branch->name : null,
            $this->gender ? 'Gender: '.$this->gender : null,
        ]);

        return $parts ? implode(' · ', $parts) : 'All districts, regions, branches and genders';
    }
}
