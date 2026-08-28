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
        'file_no',
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
        if ($user->is_super_admin === 'Yes' || $user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    public function scopeDocumentDateFrom($query, string $date)
    {
        return $query->whereDate('document_date', '>=', $date);
    }

    public function scopeDocumentDateTo($query, string $date)
    {
        return $query->whereDate('document_date', '<=', $date);
    }

    public function scopeBranchId($query, string $branchId)
    {
        return $query->where('fileable_type', (new Branch)->getMorphClass())->where('fileable_id', $branchId);
    }

    public function scopeRegionId($query, string $regionId)
    {
        return $query->where('fileable_type', (new Region)->getMorphClass())->where('fileable_id', $regionId);
    }

    public function scopeDivisionId($query, string $divisionId)
    {
        return $query->where('fileable_type', (new Division)->getMorphClass())->where('fileable_id', $divisionId);
    }
}
