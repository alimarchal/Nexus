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

        // Count existing boxes for this org unit in the current year
        $count = self::where('boxable_type', $boxableType)
            ->where('boxable_id', $boxableId)
            ->whereYear('created_at', $year)
            ->count();

        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "BOX-{$year}-{$sequence}";
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
        $userOrgUnits = $user->getOrgUnitTypes();

        if ($user->hasRole('super-admin')) {
            return $query;
        }

        $conditions = [];
        foreach ($userOrgUnits as $type => $id) {
            $conditions[] = ['boxable_type', $type, 'boxable_id', $id];
        }

        return $query->where(function ($q) use ($conditions) {
            foreach ($conditions as [$typeCol, $typeVal, $idCol, $idVal]) {
                $q->orWhere([$typeCol => $typeVal, $idCol => $idVal]);
            }
        });
    }
}
