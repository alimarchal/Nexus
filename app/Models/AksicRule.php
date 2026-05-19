<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedFilter;

class AksicRule extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $fillable = [
        'district_id',
        'district_name',
        'population_percentage',
        'proposed_beneficiaries',
        'male_percentage',
        'female_percentage',
        'special_person_percentage',
        'transgender_percentage',
        'requires_site_visit',
        'requires_business_nature',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'population_percentage' => 'decimal:2',
            'male_percentage' => 'decimal:2',
            'female_percentage' => 'decimal:2',
            'special_person_percentage' => 'decimal:2',
            'transgender_percentage' => 'decimal:2',
            'proposed_beneficiaries' => 'integer',
            'requires_site_visit' => 'boolean',
            'requires_business_nature' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('district_id'),
            AllowedFilter::exact('is_active'),
            AllowedFilter::partial('district_name'),
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function aksics(): HasMany
    {
        return $this->hasMany(Aksic::class);
    }

    public function quotaPercentageFor(string $quota): string
    {
        return match ($quota) {
            'Male' => (string) $this->male_percentage,
            'Female' => (string) $this->female_percentage,
            'Disabled' => (string) $this->special_person_percentage,
            'Special Person' => (string) $this->special_person_percentage,
            'Transgender' => (string) $this->transgender_percentage,
            default => '0',
        };
    }
}
