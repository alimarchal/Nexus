<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditStatusHistory extends Model
{
    /** @use HasFactory<\Database\Factories\AuditStatusHistoryFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['auditable_type', 'auditable_id', 'from_status', 'to_status', 'changed_by', 'note', 'metadata', 'changed_at'];

    protected $casts = [
        'metadata' => 'array',
        'changed_at' => 'datetime'
    ];

    public function auditable()
    {
        return $this->morphTo();
    }
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
