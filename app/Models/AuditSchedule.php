<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\AuditScheduleFactory> */
    use HasFactory;

    protected $fillable = ['audit_id', 'frequency', 'scheduled_date', 'next_run_date', 'is_generated', 'created_by', 'metadata'];

    protected $casts = [
        'scheduled_date' => 'date',
        'next_run_date' => 'date',
        'is_generated' => 'boolean',
        'metadata' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
