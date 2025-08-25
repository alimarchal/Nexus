<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmployeeResource extends Model
{
    use HasFactory, UserTracking, SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'resource_no',            // Manually entered number
        'resource_number',        // System generated reference
        'reference_no',           // Optional duplicate business reference
        'division_id',
        'category_id',
        'title',
        'description',
        'attachment',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'update_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Employee Resource has been {$eventName}");
    }
}
