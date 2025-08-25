<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Audit extends Model
{
    /** @use HasFactory<\Database\Factories\AuditFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'audit_type_id',
        'reference_no',
        'title',
        'description',
        'scope_summary',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'status',
        'risk_overall',
        'created_by',
        'lead_auditor_id',
        'auditee_user_id',
        'score',
        'is_template',
        'parent_audit_id',
        'metadata'
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'is_template' => 'boolean',
        'metadata' => 'array',
        'score' => 'float'
    ];

    // Relationships
    public function type()
    {
        return $this->belongsTo(AuditType::class, 'audit_type_id');
    }
    public function parent()
    {
        return $this->belongsTo(Audit::class, 'parent_audit_id');
    }
    public function children()
    {
        return $this->hasMany(Audit::class, 'parent_audit_id');
    }
    public function auditors()
    {
        return $this->hasMany(AuditAuditor::class);
    }
    public function checklistItems()
    {
        return $this->hasManyThrough(AuditChecklistItem::class, AuditType::class, 'id', 'audit_type_id', 'audit_type_id', 'id');
    }
    public function responses()
    {
        return $this->hasMany(AuditItemResponse::class);
    }
    public function findings()
    {
        return $this->hasMany(AuditFinding::class);
    }
    public function actions()
    {
        return $this->hasMany(AuditAction::class);
    }
    public function leadAuditor()
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }
    public function auditeeUser()
    {
        return $this->belongsTo(User::class, 'auditee_user_id');
    }
    public function scopes()
    {
        return $this->hasMany(AuditScope::class);
    }
    public function documents()
    {
        return $this->hasMany(AuditDocument::class);
    }
    public function risks()
    {
        return $this->hasMany(AuditRisk::class);
    }
    public function notifications()
    {
        return $this->hasMany(AuditNotification::class);
    }
    public function tags()
    {
        return $this->belongsToMany(AuditTag::class, 'audit_tag_pivots');
    }
    public function schedules()
    {
        return $this->hasMany(AuditSchedule::class);
    }
    public function metrics()
    {
        return $this->hasMany(AuditMetricsCache::class);
    }
    public function statusHistories()
    {
        return $this->morphMany(AuditStatusHistory::class, 'auditable');
    }
}
