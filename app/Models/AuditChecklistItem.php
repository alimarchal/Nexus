<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditChecklistItem extends Model
{
    /** @use HasFactory<\Database\Factories\AuditChecklistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'audit_type_id',
        'parent_id',
        'reference_code',
        'title',
        'criteria',
        'guidance',
        'response_type',
        'max_score',
        'display_order',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    public function type()
    {
        return $this->belongsTo(AuditType::class, 'audit_type_id');
    }
    public function parent()
    {
        return $this->belongsTo(AuditChecklistItem::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(AuditChecklistItem::class, 'parent_id');
    }
    public function responses()
    {
        return $this->hasMany(AuditItemResponse::class);
    }
}
