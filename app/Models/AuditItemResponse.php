<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditItemResponse extends Model
{
    /** @use HasFactory<\Database\Factories\AuditItemResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'audit_checklist_item_id',
        'responded_by',
        'response_value',
        'comment',
        'score',
        'responded_at',
        'evidence'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'score' => 'float',
        'evidence' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function checklistItem()
    {
        return $this->belongsTo(AuditChecklistItem::class, 'audit_checklist_item_id');
    }
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
    public function finding()
    {
        return $this->hasOne(AuditFinding::class);
    }
}
