<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintWitness extends Model
{
    use HasFactory, UserTracking;

    protected $fillable = [
        'complaint_id',
        'employee_number',
        'name',
        'phone',
        'email',
        'statement',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
