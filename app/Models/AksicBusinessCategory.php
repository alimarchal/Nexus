<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AksicBusinessCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AksicBusinessCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(AksicBusinessCategory::class, 'parent_id');
    }

    /**
     * Check if this is a parent category (has no parent).
     */
    public function isParent(): bool
    {
        return $this->parent_id === 0;
    }

    /**
     * Get the full category name including parent if it's a sub-category.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->isParent()) {
            return $this->name;
        }

        return $this->parent->name . ' > ' . $this->name;
    }
}