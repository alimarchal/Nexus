<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditScope extends Model
{
    /** @use HasFactory<\Database\Factories\AuditScopeFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['audit_id', 'scope_item', 'description', 'is_in_scope', 'display_order'];

    protected $casts = [
        'is_in_scope' => 'boolean'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}
