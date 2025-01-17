<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Contact
 *
 * @property int $id
 * @property int $branch_id
 * @property string|null $contact
 * @property string $status
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Branch|null $branch
 *
 * @package App\Models
 */

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'branch_id',
        'contact',
        'status',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['branch'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Valid status values for the contact
     *
     * @var array<string>
     */
    public const STATUSES = [
        'email',
        'fax',
        'telephone_no',
        'mobile_no',
        'whatsapp'
    ];

    /**
     * Get the branch that owns the contact
     * Returns null if no branch found
     *
     * @return Branch|null
     */
    public function getBranchAttribute(): ?Branch
    {
        return $this->branch()->withTrashed()->first();
    }

    /**
     * Branch relationship
     *
     * @return BelongsTo<Branch, Contact>
     */
    protected function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the contact value with null safe check
     *
     * @return string
     */
    public function getContactAttribute(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Get the status with null safe check
     *
     * @return string
     */
    public function getStatusAttribute(?string $value): string
    {
        return in_array($value, self::STATUSES) ? $value : self::STATUSES[0];
    }
}
