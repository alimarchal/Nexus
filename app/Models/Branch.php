<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'region_id',
        'district_id',
        'code',
        'name',
        'address',
    ];

    // protected $with = ['region', 'district', 'contacts', 'branchTargets'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'address'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Branch has been {$eventName}");
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function branchTargets(): HasMany
    {
        return $this->hasMany(BranchTarget::class);
    }

    public function getCodeAttribute(?string $value): string
    {
        return $value ?? '';
    }

    public function getNameAttribute(?string $value): string
    {
        return $value ?? '';
    }

    public function getAddressAttribute(?string $value): string
    {
        return $value ?? '';
    }
}
