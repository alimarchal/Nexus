<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditRisk extends Model
{
    /** @use HasFactory<\Database\Factories\AuditRiskFactory> */
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'audit_finding_id',
        'title',
        'description',
        'likelihood',
        'impact',
        'risk_level',
        'status',
        'owner_user_id',
        'created_by',
        'metadata'
    ];

    protected $casts = [
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
}
