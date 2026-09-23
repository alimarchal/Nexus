<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\AccountOpeningRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Starts a new Account Opening Form: picks the branch, the printed form
 * variant and the customer category, and opens the CIF record.
 */
class StoreAccountOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create account openings') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'aof_form_type' => ['required', Rule::in([AccountOpeningRequest::FORM_INDIVIDUAL, AccountOpeningRequest::FORM_ENTITY])],
            'customer_category_id' => ['required', 'uuid', 'exists:customer_categories,id'],
            'customer_category_other' => ['nullable', 'string', 'max:150'],
            'category_code_description' => ['nullable', 'string', 'max:200'],
            'economic_sector_id' => ['nullable', 'uuid', 'exists:economic_sectors,id'],
            'request_date' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'aof_form_type' => 'account opening form',
            'customer_category_id' => 'customer category',
        ];
    }
}
