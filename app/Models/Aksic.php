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
        'cnic_issue_date',
        'dob',
        'phone',
        'business_name',
        'business_type',
        'quota',
        'business_address',
        'permanent_address',
        'business_category_id',
        'business_sub_category_id',
        'tier',
        'amount',
        'district_id',
        'tehsil_id',
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
        'sanction_date',
        'kibor_rate',
        'spread_rate',
        'total_rate',
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
            'sanction_date' => 'date',
            'kibor_rate' => 'decimal:2',
            'spread_rate' => 'decimal:2',
            'total_rate' => 'decimal:2',
        ];
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
            AllowedFilter::partial('name'),
            AllowedFilter::partial('father_name'),
            AllowedFilter::partial('cnic'),
            AllowedFilter::partial('application_no'),
            AllowedFilter::partial('business_name'),
            AllowedFilter::partial('business_type'),
            AllowedFilter::partial('district_name'),
            AllowedFilter::partial('tehsil_name'),
            AllowedFilter::callback('date_from', function ($query, $value): void {
                $query->whereDate('created_at', '>=', $value);
            }),
            AllowedFilter::callback('date_to', function ($query, $value): void {
                $query->whereDate('created_at', '<=', $value);
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
}
