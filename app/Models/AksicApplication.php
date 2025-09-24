<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AksicApplication extends Model
{
    /** @use HasFactory<\Database\Factories\AksicApplicationFactory> */
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
        return $this->hasMany(AksicApplicationStatusLog::class, 'aksic_id', 'applicant_id');
    }
}
