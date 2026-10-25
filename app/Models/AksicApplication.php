<?php

namespace App\Models;

use App\Support\OfficeAccess;
use Database\Factories\AksicApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AksicApplication extends Model
{
    /** @use HasFactory<AksicApplicationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'applicant_id',
        'name',
        'fatherName',
        'cnic',
        'application_no',
        'cnic_issue_date',
        'dob',
        'phone',
        'businessName',
        'businessType',
        'quota',
        'businessAddress',
        'permanentAddress',
        'business_category_id',
        'business_sub_category_id',
        'tier',
        'amount',
        'district_id',
        'tehsil_id',
        'applicant_choosed_branch_id',
        'branch_id',
        'challan_branch_id',
        'challan_fee',
        'challan_image',
        'cnic_front',
        'cnic_back',
        'challan_image_url',
        'cnic_front_url',
        'cnic_back_url',
        'fee_status',
        'status',
        'bank_status',
        'fee_branch_code',
        'district_name',
        'tehsil_name',
        'api_call_json',
        'applicant_choosed_branch_code',
        'challan_branch_code',
    ];

    protected $casts = [
        'cnic_issue_date' => 'date',
        'dob' => 'date',
        'amount' => 'decimal:2',
        'challan_fee' => 'decimal:2',
        'api_call_json' => 'array',
    ];

    /**
     * Get the education records for this application.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(AksicApplicationEducation::class, 'aksic_application_id');
    }

    /**
     * Get the status logs for this application.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(AksicApplicationStatusLog::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the business category for this application.
     */
    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(AksicBusinessCategory::class, 'business_category_id');
    }

    /**
     * Get the business sub-category for this application.
     */
    public function businessSubCategory(): BelongsTo
    {
        return $this->belongsTo(AksicBusinessCategory::class, 'business_sub_category_id');
    }

    /**
     * Applications the user may see: linked to an allowed branch, or (for rows the sync could not link yet)
     * naming an allowed branch's code as the branch the applicant chose.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $ids = OfficeAccess::branchIds($user);

        if ($ids === null) {
            return $query;
        }

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        $codes = Branch::query()->whereIn('id', $ids)->pluck('code')->all();

        return $query->where(function (Builder $visible) use ($ids, $codes) {
            $visible->whereIn('branch_id', $ids)
                ->orWhere(function (Builder $unlinked) use ($codes) {
                    $unlinked->whereNull('branch_id')->whereIn('applicant_choosed_branch_code', $codes);
                });
        });
    }
}
