<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #06 -- "Customer Details For Individuals" (+ #09 MNP, #11 witness block).
 * Entity AOF ke authorized signatories ka CIF bhi yahi table use karta hai.
 */
class CustomerIndividual extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_minor' => 'boolean',
            'has_availed_mnp' => 'boolean',
            'requires_witness' => 'boolean',
            'thumb_impression_taken' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function parentageRelationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class, 'parentage_relationship_id');
    }

    public function guardianRelationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class, 'guardian_relationship_id');
    }

    public function guardianCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'guardian_customer_id');
    }

    public function birthCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'birth_country_id');
    }

    public function residenceCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'residence_country_id');
    }

    /** AOF: 18 saal se kam umar par minor account rules lagte hain. */
    public function ageInYears(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
