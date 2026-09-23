<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\IsReferenceData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * AOF #23 -- Frequency for BAJK E-Statement.
 */
class StatementFrequency extends Model
{
    use HasUuids, IsReferenceData;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'statement_frequencies';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
