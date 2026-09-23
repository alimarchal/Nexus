<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AKSIC markup budget version. One budget is active at a time.
 */
class AksicBudget extends Model
{
    use SoftDeletes, UserTracking;

    public const ENFORCEMENTS = [
        'report' => 'Report only',
        'warn' => 'Warn, allow approval',
        'block' => 'Block approval',
    ];

    protected $fillable = [
        'title',
        'reference_no',
        'reference_date',
        'total_amount',
        'existing_business_percentage',
        'new_business_percentage',
        'enforcement',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reference_date' => 'date',
            'total_amount' => 'decimal:2',
            'existing_business_percentage' => 'decimal:2',
            'new_business_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(AksicBudgetAllocation::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(AksicBudgetRevision::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest('id')->first();
    }
}
