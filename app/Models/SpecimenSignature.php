<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** AOF #30 -- Applicant's Signature/Thumb Impression + Organisation's Stamp (SS Card). */
class SpecimenSignature extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'specimen_number' => 'integer',
        ];
    }

    public function accountHolder(): BelongsTo
    {
        return $this->belongsTo(AccountHolder::class);
    }
}
