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

class ComplaintMetric extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'complaint_id',
        'time_to_first_response',
        'time_to_resolution',
        'reopened_count',
        'escalation_count',
        'assignment_count',
        'customer_satisfaction_score',
    ];

    protected $casts = [
        'time_to_first_response' => 'integer',
        'time_to_resolution' => 'integer',
        'reopened_count' => 'integer',
        'escalation_count' => 'integer',
        'assignment_count' => 'integer',
        'customer_satisfaction_score' => 'decimal:2',
    ];

    // Relationships
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('complaint_id'),
            AllowedFilter::scope('response_time_between'),
            AllowedFilter::scope('resolution_time_between'),
            AllowedFilter::scope('satisfaction_score_between'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('time_to_first_response'),
            AllowedSort::field('time_to_resolution'),
            AllowedSort::field('reopened_count'),
            AllowedSort::field('escalation_count'),
            AllowedSort::field('assignment_count'),
            AllowedSort::field('customer_satisfaction_score'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('complaint'),
            AllowedInclude::relationship('creator'),
            AllowedInclude::relationship('updater'),
        ];
    }

    // Scopes
    public function scopeResponseTimeBetween($query, $range)
    {
        return $query->whereBetween('time_to_first_response', $range);
    }

    public function scopeResolutionTimeBetween($query, $range)
    {
        return $query->whereBetween('time_to_resolution', $range);
    }

    public function scopeSatisfactionScoreBetween($query, $range)
    {
        return $query->whereBetween('customer_satisfaction_score', $range);
    }

    // Helper methods
    public function getFormattedResponseTimeAttribute(): string
    {
        return $this->formatMinutes($this->time_to_first_response);
    }

    public function getFormattedResolutionTimeAttribute(): string
    {
        return $this->formatMinutes($this->time_to_resolution);
    }

    private function formatMinutes(?int $minutes): string
    {
        if (!$minutes)
            return 'N/A';

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return "{$days}d {$remainingHours}h {$remainingMinutes}m";
        }

        return "{$hours}h {$remainingMinutes}m";
    }
}