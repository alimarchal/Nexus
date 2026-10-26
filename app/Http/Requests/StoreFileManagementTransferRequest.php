<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFileManagementTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('transfer file management systems') ?? false;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_id.exists' => 'The receiving custodian must be an active user.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'destination_fileable_type' => ['required', Rule::in(['branch', 'region', 'division', 'head-office'])],
            'destination_fileable_id' => ['required', 'integer'],
            'recipient_id' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', 'Yes')],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
