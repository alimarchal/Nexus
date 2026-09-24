<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\AksicFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;

class Aksic extends Model
{
    /** @use HasFactory<AksicFactory> */
    use HasFactory, HasUuids, SoftDeletes, UserTracking;

    protected $fillable = [
        'name',
        'father_name',
        'cnic',
        'application_no',
        'account_no',
        'cnic_issue_date',
        'dob',
        'phone',
        'business_name',
        'business_type',
        'is_startup_business',
        'quota',
        'gender',
        'business_address',
        'permanent_address',
        'business_category_id',
        'business_sub_category_id',
        'tier',
        'amount',
        'district_id',
        'tehsil_id',
        'aksic_rule_id',
        'applicant_choosed_branch_id',
        'branch_id',
        'challan_branch_id',
        'applicant_choosed_branch_code',
        'challan_branch_code',
        'challan_fee',
        'challan_image',
        'cnic_front',
        'cnic_back',
        'challan_image_url',
        'cnic_front_url',
        'cnic_back_url',
        'status',
        'bank_status',
        'fee_branch_code',
        'district_name',
        'tehsil_name',
        'principal_amount',
        'tenure',
        'disbursement_date',
        'site_visit_completed',
        'site_visit_date',
        'consent_entry',
        'consent_date',
        'liquid_security',
        'personal_guarantees',
        'mortgage',
        'kibor_rate',
        'spread_rate',
        'total_rate',
        'total_interest',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'cnic_issue_date' => 'date',
            'dob' => 'date',
            'amount' => 'decimal:2',
            'challan_fee' => 'decimal:2',
            'principal_amount' => 'decimal:2',
            'tenure' => 'integer',
            'disbursement_date' => 'date',
            'site_visit_completed' => 'boolean',
            'site_visit_date' => 'date',
            'consent_date' => 'date',
            'is_startup_business' => 'boolean',
            'kibor_rate' => 'decimal:2',
            'spread_rate' => 'decimal:2',
            'total_rate' => 'decimal:2',
            'total_interest' => 'decimal:6',
        ];
    }

    /** Bumped on every change so the cached list totals refresh at once. */
    public const STATS_VERSION_KEY = 'aksic-stats-version';

    protected static function booted(): void
    {
        $bump = fn () => rescue(fn () => Cache::forever(self::STATS_VERSION_KEY, (string) microtime(true)), null, false);
        static::saved($bump);
        static::deleted($bump);
    }

    /**
     * @return array<int, AllowedFilter>
     */
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('tier'),
            AllowedFilter::exact('branch_id'),
            AllowedFilter::exact('district_id'),
            AllowedFilter::exact('quota'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('father_name'),
            AllowedFilter::partial('cnic'),
            AllowedFilter::partial('application_no'),
            AllowedFilter::partial('account_no'),
            AllowedFilter::partial('business_name'),
            AllowedFilter::partial('business_type'),
            AllowedFilter::partial('district_name'),
            AllowedFilter::partial('tehsil_name'),
            // One search box built for large tables (10k - 1M rows): every branch
            // uses an index -- exact CNIC, prefix match on application / account
            // no and applicant name. No leading-wildcard LIKE scans.
            AllowedFilter::callback('search', function ($query, $value): void {
                $value = trim((string) $value);
                if ($value === '') {
                    return;
                }
                $digits = preg_replace('/\D+/', '', $value);

                if (strlen($digits) === 13) {
                    // Full CNIC typed with or without dashes -> exact lookup on the unique index.
                    $query->where(fn ($q) => $q
                        ->whereIn('cnic', [$digits, substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12)])
                        ->orWhere('application_no', $value)
                        ->orWhere('account_no', $value));

                    return;
                }

                $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value).'%';
                $query->where(function ($q) use ($like): void {
                    $q->where('application_no', 'like', $like)
                        ->orWhere('account_no', 'like', $like)
                        ->orWhere('cnic', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            }),
            // Pending = awaiting approval; generated = approved (schedule is created on approval).
            // Uses the indexed status column instead of an EXISTS on the schedule table.
            AllowedFilter::callback('schedule', function ($query, $value): void {
                $query->where('status', $value === 'generated' ? 'Approved' : 'Pending');
            }),
            AllowedFilter::callback('date_from', function ($query, $value): void {
                // Range on the raw column (not DATE()) so the created_at index is used.
                if ($date = rescue(fn () => Carbon::parse((string) $value)->startOfDay(), null, false)) {
                    $query->where('created_at', '>=', $date);
                }
            }),
            AllowedFilter::callback('date_to', function ($query, $value): void {
                if ($date = rescue(fn () => Carbon::parse((string) $value)->addDay()->startOfDay(), null, false)) {
                    $query->where('created_at', '<', $date);
                }
            }),
            AllowedFilter::callback('amount_min', function ($query, $value): void {
                $query->where('principal_amount', '>=', $value);
            }),
            AllowedFilter::callback('amount_max', function ($query, $value): void {
                $query->where('principal_amount', '<=', $value);
            }),
        ];
    }

    public function amortizations(): HasMany
    {
        return $this->hasMany(AksicAmortization::class);
    }

    public function claimItems(): HasMany
    {
        return $this->hasMany(AksicClaimItem::class);
    }

    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(AksicBusinessCategory::class, 'business_category_id');
    }

    public function businessSubCategory(): BelongsTo
    {
        return $this->belongsTo(AksicBusinessCategory::class, 'business_sub_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Scheme rules that must be met before the case can be approved (the
     * district's AKSIC rule decides which apply). Empty array = ready.
     *
     * @return array<int, string>
     */
    public function approvalBlockers(): array
    {
        $rule = $this->aksicRule
            ?? AksicRule::query()->where('district_id', $this->district_id)->where('is_active', true)->first();
        $blockers = [];

        if (! $rule) {
            $blockers[] = 'No active AKSIC rule for this district.';
        }
        if ($rule?->requires_site_visit && (! $this->site_visit_completed || ! $this->site_visit_date)) {
            $blockers[] = 'Site visit must be completed and its date entered.';
        }
        if ($rule?->requires_business_nature && ! in_array($this->business_type, ['Existing', 'New'], true)) {
            $blockers[] = 'Business nature (Existing / New) is required.';
        }
        foreach (['principal_amount' => 'Principal amount', 'tenure' => 'Tenure', 'disbursement_date' => 'Disbursement date', 'kibor_rate' => 'KIBOR rate', 'spread_rate' => 'Spread rate'] as $field => $label) {
            if ($this->{$field} === null || $this->{$field} === '') {
                $blockers[] = $label.' is required.';
            }
        }

        return $blockers;
    }

    public function aksicRule(): BelongsTo
    {
        return $this->belongsTo(AksicRule::class);
    }
}
