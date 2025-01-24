<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchTarget extends Model
{
    use HasFactory, SoftDeletes;
    use CausesActivity, LogsActivity;

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
            $branchTarget->created_by_user_id = auth()->check() ? auth()->id() : null;
        });

        static::updating(function ($branchTarget) {
            $branchTarget->updated_by_user_id = auth()->check() ? auth()->id() : null;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Branch Target has been {$eventName}");
    }
}
