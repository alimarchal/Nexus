<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Har reference/lookup model ke liye common behaviour.
 *
 * NOTE: BAJK AOF ke saare dropdown/checkbox lists ka shape same hai
 * (code, name, name_ur, sort_order, is_active) -- is liye code duplicate karne
 * ke bajaye ek trait bana diya hai.
 */
trait IsReferenceData
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Options belonging to one AOF form, for tables that carry `applies_to`
     * (customer categories, income sources, account purposes).
     */
    public function scopeForForm(Builder $query, string $formType): Builder
    {
        return $query->whereIn('applies_to', [$formType, 'both']);
    }

    /**
     * Options belonging to one AOF form, for tables that carry `available_for`
     * (products, card types, statement delivery modes and frequencies).
     */
    public function scopeAvailableFor(Builder $query, string $formType): Builder
    {
        return $query->whereIn('available_for', [$formType, 'both']);
    }

    /** Urdu label agar mojood ho, warna English. */
    public function label(string $locale = 'en'): string
    {
        return $locale === 'ur' && ! empty($this->name_ur) ? $this->name_ur : $this->name;
    }
}
