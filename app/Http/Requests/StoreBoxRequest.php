<?php

namespace App\Http\Requests;

use App\Models\FileManagementSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create boxes') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            // Users posted to an office create boxes for it; a super admin has no office and must pick one.
            'office' => [
                Rule::requiredIf(fn (): bool => FileManagementSystem::officeUnitOf($this->user()) === null),
                'nullable', 'string', 'regex:/^(branch|region|division|head-office):\d+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location.required' => 'Box location is required (e.g., Vault-A-Shelf-2)',
            'location.string' => 'Box location must be a text value',
            'office.required' => 'Choose the office this box belongs to.',
            'office.regex' => 'Choose the office this box belongs to.',
            'capacity.integer' => 'Box capacity must be a whole number',
            'capacity.min' => 'Box capacity must be at least 1',
        ];
    }
}
