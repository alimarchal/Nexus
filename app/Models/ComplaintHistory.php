<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintHistory extends Model
{
    protected $fillable = [
        'complaint_id',
        'status_id',
        'changed_by',
        'comments',
        'changes'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function status()
    {
        return $this->belongsTo(ComplaintStatusType::class, 'status_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }



}
