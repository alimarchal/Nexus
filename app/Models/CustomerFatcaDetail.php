<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #12/#13 -- FATCA (Individual Q1-Q4 aur Entity NFE) + CRS Entity
 * Classification. Dono forms ka data ek hi row mein.
 */
class CustomerFatcaDetail extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'q1_is_us_person' => 'boolean',
            'q2_country_of_birth_us' => 'boolean',
            'q3_has_us_address_or_phone' => 'boolean',
            'q4_has_us_mandate_or_links' => 'boolean',
            'form_w9_signed' => 'boolean',
            'form_w8_ben_signed' => 'boolean',
            'us_nationality_revoked' => 'boolean',
            'entity_incorporated_in_us' => 'boolean',
            'form_w8_ben_e_signed' => 'boolean',
            'is_tax_resident_other_country' => 'boolean',
            'self_certification_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function incorporationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'incorporation_country_id');
    }

    public function crsClassification(): BelongsTo
    {
        return $this->belongsTo(CrsEntityClassification::class, 'crs_entity_classification_id');
    }

    /** Koi bhi FATCA indicator "Yes" => W9 / W-8 BEN chahiye. */
    public function hasUsIndicia(): bool
    {
        return (bool) ($this->q1_is_us_person
            || $this->q2_country_of_birth_us
            || $this->q3_has_us_address_or_phone
            || $this->q4_has_us_mandate_or_links
            || $this->entity_incorporated_in_us);
    }
}
