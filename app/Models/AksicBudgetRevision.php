<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only ledger row for an AKSIC budget change.
 */
class AksicBudgetRevision extends Model
{
    use UserTracking;

    public const ACTIONS = [
        'initial' => 'Initial allocation',
        'enhancement' => 'Enhancement',
        'reduction' => 'Reduction',
        'redistribution' => 'Redistribution',
        'total_change' => 'Total budget changed',
        'settings_change' => 'Settings changed',
        'activation' => 'Activated',
    ];

    protected $fillable = [
        'aksic_budget_id', 'district_id', 'action', 'previous_amount', 'new_amount',
        'change_amount', 'reference_no', 'reference_date', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'previous_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'reference_date' => 'date',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(AksicBudget::class, 'aksic_budget_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
