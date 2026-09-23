<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** AOF #32 -- "Ultimate Beneficial Owner of the Account (if different from Customer)". */
class UltimateBeneficialOwner extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['declaration_form_received' => 'boolean'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function dueDiligence(): BelongsTo
    {
        return $this->belongsTo(CustomerDueDiligence::class, 'customer_due_diligence_id');
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(IdentificationDocumentType::class, 'identification_document_type_id');
    }
}
