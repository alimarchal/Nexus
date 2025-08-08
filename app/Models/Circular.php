<?php

namespace App\Models;

use App\Traits\UserTracking;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Circular extends Model
{
    use HasFactory, UserTracking;
    use SoftDeletes;
    use LogsActivity;


    protected $fillable = [
        'circular_no',
        'division_id',
        'attachment',
        'title',
        'description',
    ];

    // Define the relationship with the User (created by)
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Define the relationship with the Division
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    // Define the relationship with the User (updated by)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'update_by');
    }


    /**
     * Get activity log options.
     *
     * @return \Spatie\Activitylog\LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User has been {$eventName}");
    }

}