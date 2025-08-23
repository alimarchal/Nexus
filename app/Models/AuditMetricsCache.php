<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditMetricsCache extends Model
{
    /** @use HasFactory<\Database\Factories\AuditMetricsCacheFactory> */
    use HasFactory;

    protected $fillable = ['audit_id', 'metric_key', 'metric_value', 'numeric_value', 'payload', 'calculated_at', 'ttl_seconds'];

    protected $casts = [
        'metric_value' => 'float',
        'numeric_value' => 'integer',
        'payload' => 'array',
        'calculated_at' => 'datetime'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}
