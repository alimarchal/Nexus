<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

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

    public function status()
    {
        return $this->belongsTo(ComplaintStatusType::class, 'status_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }
}