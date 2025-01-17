<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Branch
 *
 * @property int $id
 * @property int $region_id
 * @property int $district_id
 * @property string $code
 * @property string $name
 * @property string $address
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Region|null $region
 * @property-read District|null $district
 * @property-read Collection|Contact[] $contacts
 * @property-read Collection|BranchTarget[] $branchTargets
 *
 * @package App\Models
 */

class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'region_id',
        'district_id',
        'code',
        'name',
        'address',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['region', 'district', 'contacts', 'branchTargets'];

    /**
     * Get the region that owns the branch
     * Returns null if no region found
     *
     * @return Region|null
     */
    public function getRegionAttribute(): ?Region
    {
        return $this->region()->withTrashed()->first();
    }

    /**
     * Get the district that owns the branch
     * Returns null if no district found
     *
     * @return District|null
     */
    public function getDistrictAttribute(): ?District
    {
        return $this->district()->withTrashed()->first();
    }

    /**
     * Get all contacts belonging to this branch
     * Returns empty collection if no contacts found
     *
     * @return Collection<Contact>
     */
    public function getContactsAttribute(): Collection
    {
        return $this->contacts()->withTrashed()->get() ?? collect();
    }

    /**
     * Get all branch targets belonging to this branch
     * Returns empty collection if no targets found
     *
     * @return Collection<BranchTarget>
     */
    public function getBranchTargetsAttribute(): Collection
    {
        return $this->branchTargets()->withTrashed()->get() ?? collect();
    }

    /**
     * Region relationship
     *
     * @return BelongsTo<Region, Branch>
     */
    protected function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * District relationship
     *
     * @return BelongsTo<District, Branch>
     */
    protected function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Contact relationship
     *
     * @return HasMany<Contact>
     */
    protected function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * BranchTarget relationship
     *
     * @return HasMany<BranchTarget>
     */
    protected function branchTargets(): HasMany
    {
        return $this->hasMany(BranchTarget::class);
    }

    /**
     * Get the branch's code with null safe check
     *
     * @return string
     */
    public function getCodeAttribute(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Get the branch's name with null safe check
     *
     * @return string
     */
    public function getNameAttribute(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Get the branch's address with null safe check
     *
     * @return string
     */
    public function getAddressAttribute(?string $value): string
    {
        return $value ?? '';
    }
}
