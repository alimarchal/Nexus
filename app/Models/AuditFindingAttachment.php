<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFindingAttachment extends Model
{
    /** @use HasFactory<\Database\Factories\AuditFindingAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'audit_finding_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size_bytes',
        'uploaded_by',
        'uploaded_at',
        'metadata'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function finding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
