<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FileManagementSystem extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes, UserTracking;

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
        'current_custodian_id',
        'box_id',
        'is_archived',
        'archived_at',
        'position_in_box',
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
            'archived_at' => 'datetime',
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

    public function currentCustodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_custodian_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(FileManagementTransfer::class)->latest();
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    /**
     * Check if file is archived in a box.
     */
    public function isArchived(): bool
    {
        return $this->is_archived === true && $this->box_id !== null;
    }

    /**
     * Get archive location (box number and position).
     */
    public function getArchiveLocationAttribute(): ?string
    {
        if ($this->isArchived() && $this->box) {
            return "{$this->box->box_number} (Position {$this->position_in_box})";
        }

        return null;
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

        $orgUnit = match (true) {
            $user->hasRole('branch') && $user->branch_id => [(new Branch)->getMorphClass(), $user->branch_id],
            $user->hasRole('region') && $user->region_id => [(new Region)->getMorphClass(), $user->region_id],
            $user->hasRole('division') && $user->division_id => [(new Division)->getMorphClass(), $user->division_id],
            $user->hasRole('head-office') && $user->head_office_id => [(new HeadOffice)->getMorphClass(), $user->head_office_id],
            default => null,
        };

        if (! $orgUnit) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('fileable_type', $orgUnit[0])->where('fileable_id', $orgUnit[1]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "File Management System has been {$eventName}");
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
