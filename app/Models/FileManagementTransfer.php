<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileManagementTransfer extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'file_management_system_id',
        'source_fileable_type',
        'source_fileable_id',
        'destination_fileable_type',
        'destination_fileable_id',
        'recipient_id',
        'requested_by',
        'reason',
        'status',
        'decided_by',
        'decision_note',
    ];

    public function fileManagementSystem(): BelongsTo
    {
        return $this->belongsTo(FileManagementSystem::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
