<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAksicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $aksic = $this->route('aksic');

        return [
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'max:255', Rule::unique('aksics', 'cnic')->ignore($aksic)],
            'application_no' => ['required', 'string', 'max:255', Rule::unique('aksics', 'application_no')->ignore($aksic)],
            'cnic_issue_date' => ['nullable', 'date'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'quota' => ['nullable', 'string', 'max:255'],
            'business_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'business_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'business_sub_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'tier' => ['nullable', 'integer', 'min:1'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'tehsil_id' => ['nullable', 'integer', 'min:1'],
            'applicant_choosed_branch_id' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'challan_branch_id' => ['nullable', 'string', 'max:255'],
            'applicant_choosed_branch_code' => ['nullable', 'string', 'max:255'],
            'challan_branch_code' => ['nullable', 'string', 'max:255'],
            'challan_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['Pending', 'Approved'])],
            'bank_status' => ['nullable', 'string', 'max:255'],
            'fee_branch_code' => ['nullable', 'string', 'max:255'],
            'district_name' => ['nullable', 'string', 'max:255'],
            'tehsil_name' => ['nullable', 'string', 'max:255'],
            'principal_amount' => ['required', 'numeric', 'gt:0'],
            'tenure' => ['required', 'integer', 'min:1', 'max:600'],
            'disbursement_date' => ['required', 'date'],
            'sanction_date' => ['nullable', 'date'],
            'kibor_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'spread_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
