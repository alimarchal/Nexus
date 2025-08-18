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

class ComplaintAttachment extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'complaint_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
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
            AllowedFilter::exact('file_type'),
            AllowedFilter::partial('file_name'),
            AllowedFilter::exact('created_by'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('id'),
            AllowedSort::field('created_at'),
            AllowedSort::field('file_name'),
            AllowedSort::field('file_size'),
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

    // Helper methods
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size)
            return 'N/A';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}