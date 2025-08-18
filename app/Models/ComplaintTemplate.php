<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;

class ComplaintTemplate extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'template_name',
        'category_id',
        'template_subject',
        'template_body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('template_name'),
            AllowedFilter::exact('category_id'),
            AllowedFilter::exact('is_active'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('template_name'),
            AllowedSort::field('created_at'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('creator'),
            AllowedInclude::relationship('updater'),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function renderTemplate(array $variables = []): string
    {
        $body = $this->template_body;

        foreach ($variables as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }
}