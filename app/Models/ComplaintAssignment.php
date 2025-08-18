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

class ComplaintAssignment extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'complaint_id',
        'assigned_to',
        'assigned_by',
        'assignment_type',
        'assigned_at',
        'unassigned_at',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('complaint_id'),
            AllowedFilter::exact('assigned_to'),
            AllowedFilter::exact('assigned_by'),
            AllowedFilter::exact('assignment_type'),
            AllowedFilter::exact('is_active'),
            AllowedFilter::scope('assigned_between'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('assigned_at'),
            AllowedSort::field('unassigned_at'),
            AllowedSort::field('assignment_type'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('complaint'),
            AllowedInclude::relationship('assignedTo'),
            AllowedInclude::relationship('assignedBy'),
            AllowedInclude::relationship('creator'),
            AllowedInclude::relationship('updater'),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAssignedBetween($query, $dates)
    {
        return $query->whereBetween('assigned_at', $dates);
    }
}