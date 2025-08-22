<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;

class ComplaintCategory extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'category_name',
        'parent_category_id',
        'description',
        'default_priority',
        'sla_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sla_hours' => 'integer',
    ];

    // Relationships


    public function parent(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'parent_category_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ComplaintCategory::class, 'parent_category_id');
    }

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('category_name'),
            AllowedFilter::exact('parent_category_id'),
            AllowedFilter::exact('default_priority'),
            AllowedFilter::exact('is_active'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('category_name'),
            AllowedSort::field('default_priority'),
            AllowedSort::field('sla_hours'),
            AllowedSort::field('created_at'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('parent'),
            AllowedInclude::relationship('children'),
            AllowedInclude::relationship('creator'),
            AllowedInclude::relationship('updater'),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_category_id');
    }
}