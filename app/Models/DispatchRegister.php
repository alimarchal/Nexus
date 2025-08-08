<?php

namespace App\Models;

use App\Traits\UserTracking;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DispatchRegister extends Model
{
    /** @use HasFactory<\Database\Factories\DispatchRegisterFactory> */
     use HasFactory, UserTracking;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'reference_number', // Changed from reference_no to match database column
        'date',
        'dispatch_no',
        'division_id',
        'name_of_courier_service', // Changed from courier_name to match form
        'receipt_no',
        'particulars',
        'address',
        'attachment',
        'created_by',
        'updated_by'
    ];

    // Example relationship
    public function division()
    {
        return $this->belongsTo(Division::class);
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
            ->setDescriptionForEvent(fn (string $eventName) => "Dispatch has been {$eventName}");
    }

}

