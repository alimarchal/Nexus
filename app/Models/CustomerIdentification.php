<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** AOF #07 -- Identification block (CNIC/NICOP/POC/ARC/PoR/Passport/B-Form). */
class CustomerIdentification extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'renewal_due_date' => 'date',
            'verisys_date' => 'date',
            'is_primary' => 'boolean',
            'is_expired_accepted' => 'boolean',
            'verisys_verified' => 'boolean',
            'is_attested' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(IdentificationDocumentType::class, 'identification_document_type_id');
    }

    /** T&C 5: expired CNIC par account NADRA token ke bagair allowed nahi. */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
