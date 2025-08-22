<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\Activitylog\LogOptions; // ensure activity log options class is imported

class Complaint extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'complaint_number',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'source',
        'complainant_name',
        'complainant_email',
        'complainant_phone',
        'complainant_account_number',
        'branch_id',
        'region_id',
        'division_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'resolution',
        'resolved_by',
        'resolved_at',
        'closed_at',
        'expected_resolution_date',
        'sla_breached',
        // Optional reasoning / audit fields used in UpdateComplaintRequest (ensure safe mass-assignment if present)
        'reopen_reason',
        'priority_change_reason',
        'status_change_reason',
        // Harassment specific supplemental fields (only used when category = Harassment)
        'harassment_incident_date',
        'harassment_location',
        'harassment_witnesses',
        'harassment_reported_to',
        'harassment_details',
        'harassment_confidential',
        'harassment_sub_category',
        'harassment_employee_number',
        'harassment_employee_phone',
        'harassment_abuser_employee_number',
        'harassment_abuser_name',
        'harassment_abuser_phone',
        'harassment_abuser_email',
        'harassment_abuser_relationship',
        // Grievance specific fields
        'grievance_employee_id',
        'grievance_department_position',
        'grievance_supervisor_name',
        'grievance_employment_start_date',
        'grievance_type',
        'grievance_policy_violated',
        'grievance_previous_attempts',
        'grievance_previous_attempts_details',
        'grievance_desired_outcome',
        'grievance_subject_name',
        'grievance_subject_position',
        'grievance_subject_relationship',
        'grievance_union_representation',
        'grievance_anonymous',
        'grievance_acknowledgment',
        'grievance_first_occurred_date',
        'grievance_most_recent_date',
        'grievance_pattern_frequency',
        'grievance_performance_effect',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'expected_resolution_date' => 'datetime',
        'sla_breached' => 'boolean',
        'harassment_incident_date' => 'datetime',
        'harassment_confidential' => 'boolean',
        'grievance_union_representation' => 'boolean',
        'grievance_anonymous' => 'boolean',
        'grievance_acknowledgment' => 'boolean',
        'grievance_employment_start_date' => 'date',
        'grievance_first_occurred_date' => 'date',
        'grievance_most_recent_date' => 'date',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ComplaintHistory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ComplaintComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }


    public function assignments(): HasMany
    {
        return $this->hasMany(ComplaintAssignment::class);
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(ComplaintEscalation::class);
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(ComplaintWatcher::class);
    }

    public function metrics(): HasOne
    {
        return $this->hasOne(ComplaintMetric::class);
    }

    public function witnesses(): HasMany
    {
        return $this->hasMany(ComplaintWitness::class);
    }

    // Spatie Query Builder
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('complaint_number'),
            AllowedFilter::partial('title'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
            AllowedFilter::exact('source'),
            AllowedFilter::exact('category'),
            AllowedFilter::exact('branch_id'),
            AllowedFilter::exact('assigned_to'),
            AllowedFilter::exact('assigned_by'),
            AllowedFilter::exact('resolved_by'),
            AllowedFilter::exact('sla_breached'),
            AllowedFilter::scope('created_between'),
            AllowedFilter::scope('resolved_between'),
            AllowedFilter::scope('assigned_between'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            AllowedSort::field('id'),
            AllowedSort::field('complaint_number'),
            AllowedSort::field('title'),
            AllowedSort::field('status'),
            AllowedSort::field('priority'),
            AllowedSort::field('created_at'),
            AllowedSort::field('updated_at'),
            AllowedSort::field('assigned_at'),
            AllowedSort::field('resolved_at'),
            AllowedSort::field('expected_resolution_date'),
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            AllowedInclude::relationship('branch'),
            AllowedInclude::relationship('assignedTo'),
            AllowedInclude::relationship('assignedBy'),
            AllowedInclude::relationship('resolvedBy'),
            AllowedInclude::relationship('histories'),
            AllowedInclude::relationship('comments'),
            AllowedInclude::relationship('attachments'),
            AllowedInclude::relationship('assignments'),
            AllowedInclude::relationship('escalations'),
            AllowedInclude::relationship('watchers'),
            AllowedInclude::relationship('metrics'),
        ];
    }

    // Scopes for filters
    public function scopeCreatedBetween($query, $dates)
    {
        return $query->whereBetween('created_at', $dates);
    }

    public function scopeResolvedBetween($query, $dates)
    {
        return $query->whereBetween('resolved_at', $dates);
    }

    public function scopeAssignedBetween($query, $dates)
    {
        return $query->whereBetween('assigned_at', $dates);
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->expected_resolution_date &&
            $this->expected_resolution_date->isPast() &&
            !$this->resolved_at;
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['Resolved', 'Closed']);
    }

    // Auto-generate complaint number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (!$complaint->complaint_number) {
                $complaint->complaint_number = generateUniqueId('complaint', 'complaints', 'complaint_number');
            }
        });

    }

    // Primary key is UUID
    public $incrementing = false;
    protected $keyType = 'string';



    /**
     * Get activity log options.
     *
     * @return \Spatie\Activitylog\LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Circular has been {$eventName}");
    }
}