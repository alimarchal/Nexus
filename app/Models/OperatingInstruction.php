<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * AOF #18 -- Operating Instructions (Singly, Either or Survivor, Jointly, Mandate, Signing Authority, Other).
 */
class OperatingInstruction extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'operating_instructions';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
