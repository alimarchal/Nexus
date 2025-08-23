<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTagPivot extends Model
{
    /** @use HasFactory<\Database\Factories\AuditTagPivotFactory> */
    use HasFactory;

    protected $fillable = ['audit_id', 'audit_tag_id', 'tagged_by'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function tag()
    {
        return $this->belongsTo(AuditTag::class, 'audit_tag_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'tagged_by');
    }
}
