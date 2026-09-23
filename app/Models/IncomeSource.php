<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * AOF #31/#33 -- Source of Income/Occupation/Profession.
 *
 * Individual CDD : Salaried, Pensioner, Student, House Wife, Unemployed,
 *                  Self Employed, Labor/Daily Wages, Agriculturist,
 *                  Stock/Investment, Rented Property, Home Remittance, Others
 * Business CDD   : Export Proceeds, Property/Real Estate, FDI, Local Trading,
 *                  Equity/FX Trading, Charity & Funds Donations, Others
 */
class IncomeSource extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'requires_employer_details' => 'boolean',
            'requires_fund_provider_details' => 'boolean',
            'requires_country' => 'boolean',
            'is_other' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
