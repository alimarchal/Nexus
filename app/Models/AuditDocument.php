<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditDocument extends Model
{
    /** @use HasFactory<\Database\Factories\AuditDocumentFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'audit_id',
        'audit_finding_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size_bytes',
        'category',
        'uploaded_by',
        'uploaded_at',
        'metadata'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function finding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
