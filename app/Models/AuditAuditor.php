<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditAuditor extends Model
{
    /** @use HasFactory<\Database\Factories\AuditAuditorFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['audit_id', 'user_id', 'role', 'is_primary'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
