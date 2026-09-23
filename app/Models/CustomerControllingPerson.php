<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #12/#13 -- FATCA "10% or more shares/voting rights" wale afraad aur
 * CRS "20% or more shareholding & Voting rights" wale Controlling Persons.
 */
class CustomerControllingPerson extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Laravel would pluralise "Person" to "people"; the table is named after the
     * form's own wording ("Controlling Persons"), so it is set explicitly.
     */
    protected $table = 'customer_controlling_persons';

    public const BASIS_FATCA = 'fatca_10_percent';

    public const BASIS_CRS = 'crs_20_percent';

    public const BASIS_BOTH = 'both';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'shareholding_percentage' => 'decimal:2',
            'sequence' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function personCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'person_customer_id');
    }

    public function controllingPersonType(): BelongsTo
    {
        return $this->belongsTo(ControllingPersonType::class);
    }
}
