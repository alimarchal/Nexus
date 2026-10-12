<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of an Account Opening request's history: a step save with the exact
 * old -> new values, or a workflow action (submitted, approved, rejected, unlocked).
 */
class AccountOpeningHistory extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $keyType = 'string';

    public $incrementing = false;

    /** Microsecond precision keeps entries made within the same second in order. */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function accountOpeningRequest(): BelongsTo
    {
        return $this->belongsTo(AccountOpeningRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
