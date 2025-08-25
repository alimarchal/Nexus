<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditActionUpdate extends Model
{
    /** @use HasFactory<\Database\Factories\AuditActionUpdateFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['audit_action_id', 'created_by', 'update_text', 'status_after', 'is_system_generated', 'metadata'];

    protected $casts = [
        'is_system_generated' => 'boolean',
        'metadata' => 'array'
    ];

    public function action()
    {
        return $this->belongsTo(AuditAction::class, 'audit_action_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
