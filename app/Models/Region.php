<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Region
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Collection|District[] $districts
 * @property-read Collection|Branch[] $branches
 *
 * @package App\Models
 */
class Region extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $with = ['districts', 'branches'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function getNameAttribute(?string $value): string
    {
        return $value ?? '';
    }
}

