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

class ComplaintComment extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'complaint_id',
        'comment_text',
        'comment_type',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
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
            AllowedFilter::exact('comment_type'),
            AllowedFilter::exact('is_private'),
            AllowedFilter::exact('created_by'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('id'),
            AllowedSort::field('created_at'),
            AllowedSort::field('updated_at'),
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
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }
}