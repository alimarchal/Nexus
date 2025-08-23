<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditType extends Model
{
    /** @use HasFactory<\Database\Factories\AuditTypeFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'default_metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_metadata' => 'array'
    ];

    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
    public function checklistItems()
    {
        return $this->hasMany(AuditChecklistItem::class);
    }
}
