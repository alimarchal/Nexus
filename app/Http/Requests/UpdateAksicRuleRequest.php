<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAksicRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aksicRule = $this->route('aksic_rule');

        return [
            'district_id' => ['required', 'integer', 'exists:districts,id', Rule::unique('aksic_rules', 'district_id')->ignore($aksicRule)],
            'population_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'proposed_beneficiaries' => ['required', 'integer', 'min:1'],
            'male_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'female_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'special_person_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'transgender_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'requires_site_visit' => ['required', 'boolean'],
            'requires_business_nature' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $total = (float) $this->input('male_percentage')
                + (float) $this->input('female_percentage')
                + (float) $this->input('special_person_percentage')
                + (float) $this->input('transgender_percentage');

            if (round($total, 2) !== 100.00) {
                $validator->errors()->add('male_percentage', 'Gender quota percentages must total 100%.');
            }
        });
    }
}
