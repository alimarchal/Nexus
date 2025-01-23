<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'annual_target_amount' => ['sometimes', 'decimal:0,3', 'min:0'],
            'target_start_date' => ['sometimes', 'date'],
            'fiscal_year' => ['sometimes', 'integer', 'min:2000', 'max:2099',
                Rule::unique('branch_targets')
                    ->where('branch_id', $this->input('branch_id')) // Access branch_id from the request directly
                    ->whereNull('deleted_at')
            ],
        ];
    }
}
