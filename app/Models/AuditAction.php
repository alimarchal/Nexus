<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditAction extends Model
{
    /** @use HasFactory<\Database\Factories\AuditActionFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'audit_id',
        'audit_finding_id',
        'reference_no',
        'title',
        'description',
        'action_type',
        'status',
        'priority',
        'due_date',
        'completed_date',
        'owner_user_id',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
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
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updates()
    {
        return $this->hasMany(AuditActionUpdate::class);
    }
    public function statusHistories()
    {
        return $this->morphMany(AuditStatusHistory::class, 'auditable');
    }
}
