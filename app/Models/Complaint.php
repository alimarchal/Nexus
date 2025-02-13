<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use SoftDeletes;

     protected $fillable = [
            'reference_number',
            'subject',
            'description',
            'status_id',
            'created_by',
            'assigned_to',
            'due_date',
            'priority',
            'meta_data',
        ];



    protected $casts = [
        'due_date' => 'date',
        'meta_data' => 'json',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(ComplaintStatusType::class, 'status_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }
}