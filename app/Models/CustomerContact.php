<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AOF #09 -- "Contact Detail": Mobile No., Telephone No. Residential/Office,
 * Personal/Office E-Mail ID, Personal/Office Fax.
 */
class CustomerContact extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_MOBILE = 'mobile';

    public const TYPE_TELEPHONE = 'telephone_residence_office';

    public const TYPE_FAX = 'fax_personal_office';

    public const TYPE_EMAIL = 'email_personal_office';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_registered_for_mobile_banking' => 'boolean',
            'is_registered_for_estatement' => 'boolean',
            'is_contact_center_registered' => 'boolean',
            'sequence' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('contact_type', $type);
    }

    /** Form: country code mobile/telephone ke sath mandatory hai. */
    public function fullNumber(): string
    {
        return trim(($this->country_code ?? '').' '.$this->value);
    }
}
