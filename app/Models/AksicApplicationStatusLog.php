<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AksicApplicationStatusLog extends Model
{
    /** @use HasFactory<\Database\Factories\AksicApplicationStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'aksic_id',
        'applicant_id',
        'old_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
        'remarks',
        'status_json',
    ];

    protected $casts = [
        'status_json' => 'array',
    ];

    /**
     * Get the application that this status log belongs to.
     * Note: This uses aksic_id to match applicant_id in applications table
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AksicApplication::class, 'aksic_id', 'applicant_id');
    }
}
