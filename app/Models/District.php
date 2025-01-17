<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'region_id',
        'name',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['region', 'branches'];

    /**
     * Get the region that owns the district
     * Returns null if no region found
     *
     * @return Region|null
     */
    public function getRegionAttribute(): ?Region
    {
        return $this->region()->withTrashed()->first();
    }

    /**
     * Get all branches belonging to this district
     * Returns empty collection if no branches found
     *
     * @return Collection<Branch>
     */
    public function getBranchesAttribute(): Collection
    {
        return $this->branches()->withTrashed()->get() ?? collect();
    }

    /**
     * Region relationship
     *
     * @return BelongsTo<Region, District>
     */
    protected function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Branch relationship
     *
     * @return HasMany<Branch>
     */
    protected function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the district's name with null safe check
     *
     * @return string
     */
    public function getNameAttribute(?string $value): string
    {
        return $value ?? '';
    }
}
