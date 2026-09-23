<?php

namespace App\Http\Requests;

use App\Models\AksicClaim;
use App\Support\AksicDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAksicClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['claim_date', 'period_from', 'period_to'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => AksicDate::toDatabase($this->input($field))]);
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'claim_date' => ['required', 'date'],
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'gender' => ['nullable', Rule::in(AksicClaim::GENDERS)],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'period_from' => 'period from',
            'period_to' => 'period to',
        ];
    }
}
