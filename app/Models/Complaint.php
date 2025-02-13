<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use SoftDeletes;


    public function histories()
    {
        return $this->hasMany(ComplaintHistory::class);
    }

// Also add these relationships if not already present
    public function status()
    {
        return $this->belongsTo(ComplaintStatusType::class, 'status_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }


    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


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
        'meta_data'
    ];

    protected $casts = [
        'due_date' => 'date',
        'meta_data' => 'array'
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
