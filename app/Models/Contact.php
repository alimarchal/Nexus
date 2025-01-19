<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Contact has been {$eventName}");
    }

    protected $fillable = [
        'branch_id',
        'contact',
        'status',
    ];

    protected $with = ['branch'];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUSES = [
        'email',
        'fax',
        'telephone_no',
        'mobile_no',
        'whatsapp'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getContactAttribute(?string $value): string
    {
        return $value ?? '';
    }

    public function getStatusAttribute(?string $value): string
    {
        return in_array($value, self::STATUSES) ? $value : self::STATUSES[0];
    }
}
