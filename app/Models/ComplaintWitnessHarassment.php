<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintWitnessHarassment extends Model
{
    protected $fillable = [
        'complaint_id',
        'accused_name',
        'accused_designation',
        'accused_id',
        'incident_datetime', // single column for date+time
        'incident_location',
        'harassment_type',
        'witnesses',
    ];

    protected $casts = [
        'witnesses' => 'array', // JSON to array
        'incident_datetime' => 'datetime', // automatic cast to Carbon
    ];
}
