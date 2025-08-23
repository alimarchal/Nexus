<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFinding extends Model
{
    /** @use HasFactory<\Database\Factories\AuditFindingFactory> */
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'audit_item_response_id',
        'reference_no',
        'category',
        'severity',
        'status',
        'title',
        'description',
        'risk_description',
        'root_cause',
        'recommendation',
        'target_closure_date',
        'actual_closure_date',
        'owner_user_id',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
        'metadata' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function response()
    {
        return $this->belongsTo(AuditItemResponse::class, 'audit_item_response_id');
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function actions()
    {
        return $this->hasMany(AuditAction::class);
    }
    public function attachments()
    {
        return $this->hasMany(AuditFindingAttachment::class);
    }
    public function risks()
    {
        return $this->hasMany(AuditRisk::class);
    }
    public function documents()
    {
        return $this->hasMany(AuditDocument::class);
    }
    public function statusHistories()
    {
        return $this->morphMany(AuditStatusHistory::class, 'auditable');
    }
}
