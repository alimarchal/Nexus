<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AOF #04 -- Customer Category.
 *
 * Yeh table dono PDFs ke category boxes ko jorti hai:
 *  - applies_to = individual : Individual, Sole Proprietorship, Joint, Others
 *  - applies_to = entity     : Partnership, Limited Companies, Public Listed/
 *                              Unlisted, Private, Govt Institution, Societies,
 *                              Club, Trust, Associations, Local Zakat Committee,
 *                              Money Exchange, Branch/Liaison Office of Foreign
 *                              Companies, NGO/NPO's Charities, Agents Accounts,
 *                              Executors & Administrators, Foreign Missions, Others
 */
class CustomerCategory extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'requires_individual_profile' => 'boolean',
            'requires_organization_profile' => 'boolean',
            'is_other' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class);
    }

    /**
     * Is category ke liye customers.customer_type kya hona chahiye.
     * Sole Proprietorship ke liye dono profiles chahiye -- yahi wo case hai
     * jo Individual AOF aur Entity AOF ko ek design mein jorta hai.
     */
    public function resolvedCustomerType(): string
    {
        return match (true) {
            $this->requires_individual_profile && $this->requires_organization_profile => Customer::TYPE_SOLE_PROPRIETOR,
            $this->requires_organization_profile => Customer::TYPE_ENTITY,
            default => Customer::TYPE_INDIVIDUAL,
        };
    }
}
