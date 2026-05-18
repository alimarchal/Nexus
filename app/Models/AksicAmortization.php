<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\AksicAmortizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AksicAmortization extends Model
{
    /** @use HasFactory<AksicAmortizationFactory> */
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'aksic_id',
        'installment_no',
        'period_start_date',
        'due_date',
        'days',
        'principal_amount_os',
        'installment_per_month',
        'product',
        'interest_rate_per_month',
        'total_interest',
        'total_rate',
        'total_installment',
        'principal_balance_after_installment',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start_date' => 'date',
            'due_date' => 'date',
            'days' => 'integer',
            'principal_amount_os' => 'decimal:6',
            'installment_per_month' => 'decimal:6',
            'product' => 'decimal:6',
            'interest_rate_per_month' => 'decimal:6',
            'total_interest' => 'decimal:6',
            'total_rate' => 'decimal:6',
            'total_installment' => 'decimal:6',
            'principal_balance_after_installment' => 'decimal:6',
        ];
    }

    public function aksic(): BelongsTo
    {
        return $this->belongsTo(Aksic::class);
    }
}
