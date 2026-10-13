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
            'form_edition' => ['nullable', Rule::in([AccountOpeningRequest::EDITION_NEW, AccountOpeningRequest::EDITION_OLD])],
            'aof_form_type' => ['required', Rule::in([AccountOpeningRequest::FORM_INDIVIDUAL, AccountOpeningRequest::FORM_ENTITY])],
            'customer_category_id' => ['required', 'uuid', 'exists:customer_categories,id'],
            'customer_category_other' => ['nullable', 'string', 'max:150'],
            'category_code_description' => ['nullable', 'string', 'max:200'],
            // Typed, not picked: required for the Government / organisation form, not used for Individual accounts.
            'economic_sector_code' => [
                Rule::requiredIf(fn (): bool => $this->input('aof_form_type') === AccountOpeningRequest::FORM_ENTITY),
                'nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9.\-]+$/',
            ],
            'request_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'economic_sector_code.required' => 'Please enter the Economic Sector Code for a Government / organisation account.',
            'economic_sector_code.regex' => 'The Economic Sector Code may contain only letters, numbers, dots and dashes.',
        ];
    }

    public function attributes(): array
    {
        return [
            'economic_sector_code' => 'economic sector code',
            'aof_form_type' => 'account opening form',
            'form_edition' => 'form edition',
            'customer_category_id' => 'customer category',
        ];
    }
}
