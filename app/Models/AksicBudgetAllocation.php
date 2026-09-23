<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AksicBudgetAllocation extends Model
{
    use UserTracking;

    protected $fillable = ['aksic_budget_id', 'district_id', 'aksic_rule_id', 'allocated_amount'];

    protected function casts(): array
    {
        return ['allocated_amount' => 'decimal:2'];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(AksicBudget::class, 'aksic_budget_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AksicRule::class, 'aksic_rule_id');
    }
}
