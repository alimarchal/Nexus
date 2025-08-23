<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTag extends Model
{
    /** @use HasFactory<\Database\Factories\AuditTagFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function audits()
    {
        return $this->belongsToMany(Audit::class, 'audit_tag_pivots');
    }
}
