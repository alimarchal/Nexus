<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The filled-in old BAJK paper Account Opening Form of one "old" request.
 */
class AccountOpeningPaperForm extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'key_word' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<AccountOpeningRequest, $this>
     */
    public function accountOpeningRequest(): BelongsTo
    {
        return $this->belongsTo(AccountOpeningRequest::class);
    }

    /**
     * Every field of the form in one array, the Key/Secret Word included.
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return ($this->data ?? []) + ['key_word' => $this->key_word];
    }
}
