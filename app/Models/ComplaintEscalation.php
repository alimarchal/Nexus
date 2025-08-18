<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;

class ComplaintEscalation extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'complaint_id',
        'escalated_from',
        'escalated_to',
        'escalation_level',
        'escalated_at',
        'resolved_at',
        'escalation_reason',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'escalation_level' => 'integer',
    ];

    // Relationships
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function escalatedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_from');
    }

    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('complaint_id'),
            AllowedFilter::exact('escalated_from'),
            AllowedFilter::exact('escalated_to'),
            AllowedFilter::exact('escalation_level'),
            AllowedFilter::scope('escalated_between'),
            AllowedFilter::scope('resolved_between'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('escalated_at'),
            AllowedSort::field('resolved_at'),
            AllowedSort::field('escalation_level'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('complaint'),
            AllowedInclude::relationship('escalatedFrom'),
            AllowedInclude::relationship('escalatedTo'),
            AllowedInclude::relationship('creator'),
            AllowedInclude::relationship('updater'),
        ];
    }

    // Scopes
    public function scopeEscalatedBetween($query, $dates)
    {
        return $query->whereBetween('escalated_at', $dates);
    }

    public function scopeResolvedBetween($query, $dates)
    {
        return $query->whereBetween('resolved_at', $dates);
    }

    public function scopePending($query)
    {
        return $query->whereNull('resolved_at');
    }
}