<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditNotification extends Model
{
    /** @use HasFactory<\Database\Factories\AuditNotificationFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'audit_id',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'template',
        'subject',
        'body',
        'status',
        'sent_at',
        'failure_reason',
        'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function notifiable()
    {
        return $this->morphTo();
    }
}
