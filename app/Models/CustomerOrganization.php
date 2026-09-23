<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #06 -- "Customer Details For Business".
 * Sole Proprietor ke liye yeh record CustomerIndividual ke SAATH banta hai.
 */
class CustomerOrganization extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expiry_date' => 'date',
            'business_commencement_date' => 'date',
            'business_incorporation_date' => 'date',
            'tax_exempt_on_cash_withdrawal' => 'boolean',
            'tax_exempt_on_profit' => 'boolean',
            'is_dnfbp' => 'boolean',
            'years_in_business' => 'integer',
            'number_of_employees' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function businessNature(): BelongsTo
    {
        return $this->belongsTo(BusinessNature::class);
    }

    public function incorporationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'incorporation_country_id');
    }
}
