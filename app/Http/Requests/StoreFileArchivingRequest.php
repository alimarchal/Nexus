<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileArchivingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('archive file management systems') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'box_id' => ['required', 'string', 'exists:boxes,id'],
            'file_ids' => ['required', 'array', 'min:1'],
            'file_ids.*' => ['string', 'exists:file_management_systems,id'],
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
            'box_id.required' => 'Box selection is required',
            'box_id.exists' => 'Selected box does not exist',
            'file_ids.required' => 'Please select at least one file to archive',
            'file_ids.min' => 'Please select at least one file to archive',
            'file_ids.*.exists' => 'One or more selected files do not exist',
        ];
    }
}
