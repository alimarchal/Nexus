<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * AOF #06/#08/#14/#17 -- Country of Birth, Nationality, Country of Residence, Country of Incorporation, Address Country, Tax Residence.
 */
class Country extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'countries';

    protected $guarded = ['id'];

    /**
     * Countries are keyed by their ISO alpha-2 code rather than a generic
     * `code` column, so the shared reference scope is overridden here.
     */
    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where('iso2', $code);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
