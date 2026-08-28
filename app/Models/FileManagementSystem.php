<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\FileManagementSystemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;

class FileManagementSystem extends Model
{
    /** @use HasFactory<FileManagementSystemFactory> */
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    public function fileable()
    {
        return $this->morphTo();
    }

    public function fileCategory()
    {
        return $this->belongsTo(FileCategory::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pages')->useDisk('branch_archive');
    }

    public function scopeVisibleTo($query, User $user)
    {
        // Sees everything, no filter.
        if ($user->is_super_admin === 'Yes' || $user->hasRole('head-office')) {
            return $query;
        }

        if ($user->hasRole('division') && $user->division_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('fileable_type', Division::class)->where('fileable_id', $user->division_id)
                    ->orWhereHasMorph('fileable', [Region::class], fn ($q2) => $q2->where('division_id', $user->division_id))
                    ->orWhereHasMorph('fileable', [Branch::class], fn ($q2) => $q2->whereHas('region', fn ($q3) => $q3->where('division_id', $user->division_id)));
            });
        }

        if ($user->hasRole('region') && $user->region_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('fileable_type', Region::class)->where('fileable_id', $user->region_id)
                    ->orWhereHasMorph('fileable', [Branch::class], fn ($q2) => $q2->where('region_id', $user->region_id));
            });
        }

        if ($user->hasRole('branch') && $user->branch_id) {
            return $query->where('fileable_type', Branch::class)->where('fileable_id', $user->branch_id);
        }

        // No matching role/scope — see nothing, not everything.
        return $query->whereRaw('1 = 0');
    }
}
