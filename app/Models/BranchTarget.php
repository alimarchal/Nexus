<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTarget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'created_by_user_id',
        'updated_by_user_id',
        'annual_target_amount',
        'target_start_date',
        'fiscal_year'
    ];

    protected $casts = [
        'target_start_date' => 'date',
        'fiscal_year' => 'integer',
        'annual_target_amount' => 'decimal:3'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branchTarget) {
            $branchTarget->created_by_user_id = auth()->id();
        });

        static::updating(function ($branchTarget) {
            $branchTarget->updated_by_user_id = auth()->id();
        });
    }
}
