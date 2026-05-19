<?php

namespace App\Http\Requests;

use App\Models\Aksic;
use App\Models\AksicRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAksicRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'max:255', 'unique:aksics,cnic'],
            'application_no' => ['required', 'string', 'max:255', 'unique:aksics,application_no'],
            'cnic_issue_date' => ['nullable', 'date'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['required', Rule::in(['Existing', 'Startup', 'New'])],
            'is_startup_business' => ['required', 'boolean'],
            'quota' => ['required', Rule::in(['Male', 'Female', 'Disabled', 'Special Person', 'Transgender'])],
            'gender' => ['nullable', 'required_if:quota,Disabled,Special Person', Rule::in(['Male', 'Female'])],
            'business_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'business_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'business_sub_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'tier' => ['nullable', 'integer', 'min:1'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
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
            'site_visit_completed' => ['required', 'boolean'],
            'site_visit_date' => ['nullable', 'date'],
            'kibor_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'spread_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateAksicRules($validator);
        });
    }

    private function validateAksicRules($validator): void
    {
        if (! $this->filled('district_id') || ! $this->filled('quota')) {
            return;
        }

        $rule = AksicRule::query()
            ->where('district_id', $this->integer('district_id'))
            ->where('is_active', true)
            ->first();

        if (! $rule) {
            $validator->errors()->add('district_id', 'No active AKSIC rule exists for the selected district.');

            return;
        }

        if ($rule->requires_business_nature && ! in_array($this->input('business_type'), ['Existing', 'Startup', 'New'], true)) {
            $validator->errors()->add('business_type', 'Business nature must be Existing, Startup, or New.');
        }

        if ($rule->requires_site_visit && $this->input('status') === 'Approved') {
            if (! $this->boolean('site_visit_completed')) {
                $validator->errors()->add('site_visit_completed', 'Site visit must be completed before approving AKSIC loan.');
            }

            if (! $this->filled('site_visit_date')) {
                $validator->errors()->add('site_visit_date', 'Site visit date is required before approving AKSIC loan.');
            }
        }

        $districtUsage = Aksic::query()
            ->where('district_id', $rule->district_id)
            ->count();

        if (($districtUsage + 1) > $rule->proposed_beneficiaries) {
            $validator->errors()->add('district_id', "Selected district quota is full. Limit: {$rule->proposed_beneficiaries} beneficiaries.");
        }

        $totalBeneficiaries = (int) AksicRule::query()->where('is_active', true)->sum('proposed_beneficiaries');
        $quotaPercentage = (float) $rule->quotaPercentageFor($this->input('quota'));
        $quotaLimit = (int) floor($totalBeneficiaries * ($quotaPercentage / 100));

        $quotaUsage = Aksic::query()
            ->whereIn('quota', $this->quotaAliases($this->input('quota')))
            ->count();

        if ($quotaLimit > 0 && ($quotaUsage + 1) > $quotaLimit) {
            $validator->errors()->add('quota', "Selected quota is full. Limit: {$quotaLimit} beneficiaries.");
        }
    }

    /**
     * @return array<int, string>
     */
    private function quotaAliases(string $quota): array
    {
        return $quota === 'Disabled' || $quota === 'Special Person'
            ? ['Disabled', 'Special Person']
            : [$quota];
    }
}
