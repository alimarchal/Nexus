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
use Illuminate\Support\Str;
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
            'is_archived' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'locked_at' => 'datetime',
            'unlocked_at' => 'datetime',
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

    /**
     * An approved record is locked for everyone until a super admin unlocks it.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Whether the record's details and pages may be changed right now. A record that is waiting for its
     * checker, or that is approved and locked, cannot be edited. Without maker-checker everything is editable.
     */
    public function isEditable(): bool
    {
        if (! config('file_management.maker_checker.enabled', true)) {
            return true;
        }

        if ($this->isLocked()) {
            return false;
        }

        // Records that existed before maker-checker are approved but were never locked, so they stay editable; a change
        // to such a record (or to any approved record when locking is off) sends it back for approval.
        return in_array($this->status, ['draft', 'rejected', 'approved'], true);
    }

    /**
     * A record can be transferred to another office or archived into a box only once a checker has approved it.
     * Without maker-checker every record is treated as approved.
     */
    public function isReadyToMove(): bool
    {
        return ! config('file_management.maker_checker.enabled', true) || $this->status === 'approved';
    }

    /**
     * Why the record cannot be transferred or archived yet, or null when it can.
     *
     * @param  string  $action  e.g. "transferred", "archived"
     */
    public function movementBlockedReason(string $action): ?string
    {
        if ($this->isReadyToMove()) {
            return null;
        }

        return $this->status === 'submitted'
            ? "This record is waiting for the checker, so it cannot be {$action} until it is approved."
            : "This record has not been approved yet, so it cannot be {$action} until it is submitted and approved.";
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
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
        // Scanned documents (pdf/docx/images) live on a private disk and are only opened through the protected page route.
        $this->addMediaCollection('pages')->useDisk((string) config('file_management.pages_disk', 'local'));
    }

    /**
     * The office unit ([morph type, id]) a user archives files and creates boxes
     * for -- same order as the controller: branch, region, division, head office.
     * Null for users with no office posting (e.g. super admin).
     *
     * @return array{0: string, 1: int}|null
     */
    public static function officeUnitOf(User $user): ?array
    {
        foreach (['branch' => 'branch_id', 'region' => 'region_id', 'division' => 'division_id', 'head-office' => 'head_office_id'] as $role => $column) {
            if ($user->hasRole($role) && $user->{$column}) {
                return [$role, (int) $user->{$column}];
            }
        }

        return null;
    }

    /**
     * Whether the user may create archive boxes: they hold the permission and either belong to an office or, as a
     * super admin, may pick the office the box belongs to.
     */
    public static function canCreateBoxes(User $user): bool
    {
        if (! $user->can('create boxes')) {
            return false;
        }

        return self::officeUnitOf($user) !== null || $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }

    /**
     * "What am I looking at" label for list and dashboard headers.
     */
    public static function officeLabelFor(User $user): string
    {
        if ($user->is_super_admin === 'Yes' || $user->hasRole('super-admin')) {
            return 'All offices';
        }

        $unit = self::officeUnitOf($user);
        if (! $unit) {
            return 'No office set';
        }

        $office = match ($unit[0]) {
            'branch' => $user->branch,
            'region' => $user->region,
            'division' => $user->division,
            default => $user->headOffice,
        };

        return Str::headline($unit[0]).': '.trim(($office->code ?? '').' '.($office->name ?? ''));
    }

    /**
     * Only the office that holds a file may archive it into one of its boxes.
     */
    public function isHeldBy(User $user): bool
    {
        $unit = self::officeUnitOf($user);

        return $unit !== null && $this->fileable_type === $unit[0] && (int) $this->fileable_id === $unit[1];
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
            ->logExcept(['updated_by', 'updated_at', 'status', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'locked_at', 'unlocked_by', 'unlocked_at', 'unlock_reason'])
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

    public function scopeBoxNumber($query, string $boxNumber)
    {
        return $query->whereHas('box', fn ($q) => $q->where('box_number', 'like', "%{$boxNumber}%"));
    }
}
