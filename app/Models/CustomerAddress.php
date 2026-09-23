<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #08 -- Contact Details ke address boxes.
 * Individual form ka "Permanent Residential" aur Entity form ka "Registered
 * Business" ek hi structure hai, is liye ek table + address_type.
 */
class CustomerAddress extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_PERMANENT_RESIDENTIAL = 'permanent_residential';

    public const TYPE_REGISTERED_BUSINESS = 'registered_business';

    public const TYPE_CURRENT_RESIDENTIAL = 'current_residential';

    public const TYPE_CURRENT_BUSINESS = 'current_business';

    public const TYPE_MAILING = 'mailing';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('address_type', $type);
    }

    /** Form ke boxes ko ek line mein jorta hai (statement/courier ke liye). */
    public function toSingleLine(): string
    {
        return collect([
            $this->house_office_no,
            $this->street_area,
            $this->nearest_landmark,
            $this->tehsil_district,
            $this->city,
            $this->postal_code,
            $this->country?->name,
        ])->filter()->implode(', ');
    }
}
