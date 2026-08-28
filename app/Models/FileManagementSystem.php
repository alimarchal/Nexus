<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\FileManagementSystemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FileManagementSystem extends Model implements HasMedia
{
    /** @use HasFactory<FileManagementSystemFactory> */
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'digital_id',
        'file_category_id',
        'fileable_type',
        'fileable_id',
        'document_date',
        'title',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
        ];
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fileCategory(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class);
    }

    /**
     * Human-friendly label for the polymorphic org-unit type (Branch/Region/Division).
     */
    public function getFileableLabelAttribute(): string
    {
        return ucfirst((string) $this->fileable_type);
    }

    /**
     * Name of the related org-unit record, regardless of its type.
     */
    public function getFileableNameAttribute(): ?string
    {
        return $this->fileable?->name;
    }

    public function registerMediaCollections(): void
    {
        // Scanned documents (pdf/docx/images) are stored on the public disk so they're servable via /storage.
        $this->addMediaCollection('pages')->useDisk('public');
    }

    public function scopeVisibleTo($query, User $user)
    {
        // Sees everything, no filter.
        if ($user->is_super_admin === 'Yes' || $user->hasRole('head-office')) {
            return $query;
        }

        if ($user->hasRole('division') && $user->division_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('fileable_type', (new Division)->getMorphClass())->where('fileable_id', $user->division_id)
                    ->orWhereHasMorph('fileable', [Region::class], fn ($q2) => $q2->where('division_id', $user->division_id))
                    ->orWhereHasMorph('fileable', [Branch::class], fn ($q2) => $q2->whereHas('region', fn ($q3) => $q3->where('division_id', $user->division_id)));
            });
        }

        if ($user->hasRole('region') && $user->region_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('fileable_type', (new Region)->getMorphClass())->where('fileable_id', $user->region_id)
                    ->orWhereHasMorph('fileable', [Branch::class], fn ($q2) => $q2->where('region_id', $user->region_id));
            });
        }

        if ($user->hasRole('branch') && $user->branch_id) {
            return $query->where('fileable_type', (new Branch)->getMorphClass())->where('fileable_id', $user->branch_id);
        }

        // No matching role/scope — see nothing, not everything.
        return $query->whereRaw('1 = 0');
    }

    public function scopeDocumentDateFrom($query, string $date)
    {
        return $query->whereDate('document_date', '>=', $date);
    }

    public function scopeDocumentDateTo($query, string $date)
    {
        return $query->whereDate('document_date', '<=', $date);
    }
}
