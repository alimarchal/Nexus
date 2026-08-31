<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'capacity.integer' => 'Box capacity must be a whole number',
            'capacity.min' => 'Box capacity must be at least 1',
        ];
    }
}
