<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Box extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'box_number',
        'boxable_type',
        'boxable_id',
        'status',
        'location',
        'file_count',
        'capacity',
        'archived_date',
        'created_by',
    ];

    /**
     * Casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_date' => 'datetime',
        ];
    }

    /**
     * Polymorphic relationship to org units (Branch, Region, Division, HeadOffice).
     */
    public function boxable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Files stored in this box.
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileManagementSystem::class);
    }

    /**
     * User who created this box.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate auto-incrementing box number for a specific org unit.
     *
     * Format: BOX-{YEAR}-{SEQUENCE}
     * Example: BOX-2026-001, BOX-2026-002
     */
    public static function generateBoxNumber(string $boxableType, int|string $boxableId): string
    {
        $year = now()->year;

        // The box number is unique across the whole bank, so the sequence runs across all offices for the year.
        $last = self::query()
            ->where('box_number', 'like', "BOX-{$year}-%")
            ->pluck('box_number')
            ->map(fn (string $number): int => (int) substr($number, strlen("BOX-{$year}-")))
            ->max() ?? 0;

        $sequence = str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);

        return "BOX-{$year}-{$sequence}";
    }

    /**
     * Set the file count from the files that are really in the box, and keep a full/open box in step with it.
     * A box that was closed by hand stays closed.
     */
    public function refreshFileCount(): void
    {
        $count = $this->files()->where('is_archived', true)->count();

        $status = match (true) {
            $this->status === 'open' && $count >= $this->capacity => 'full',
            $this->status === 'full' && $count < $this->capacity => 'open',
            default => $this->status,
        };

        $this->forceFill(['file_count' => $count, 'status' => $status])->save();
    }

    /**
     * The next free place in the box: one after the highest position in use, so no two files share a position
     * even when a file has left the box.
     */
    public function nextPosition(): int
    {
        return (int) $this->files()->where('is_archived', true)->max('position_in_box') + 1;
    }

    /**
     * Check if this box can accept more files.
     */
    public function canAcceptFiles(): bool
    {
        return $this->status === 'open' && $this->file_count < $this->capacity;
    }

    /**
     * Get visibility scope for current user's org unit.
     */
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

        return $query->where('boxable_type', $orgUnit[0])->where('boxable_id', $orgUnit[1]);
    }
}
