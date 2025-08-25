<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_no' => [
                'required',
                'string',
                'max:50',
                'unique:employee_resources,resource_no',
                'regex:/^[A-Z0-9\-\/]+$/'
            ],
            'division_id' => 'required|exists:divisions,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:5000',
            'attachment' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:5120'
            ],
            'resource_number' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'resource_no.required' => 'Resource number is required.',
            'resource_no.unique' => 'This resource number already exists.',
            'resource_no.regex' => 'Resource number must contain only uppercase letters, numbers, hyphens, and slashes.',
            'division_id.required' => 'Please select a division.',
            'division_id.exists' => 'Selected division does not exist.',
            'attachment.mimes' => 'Attachment must be a PDF, Word document, or image file.',
            'attachment.max' => 'Attachment size cannot exceed 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->resource_no) {
            $this->merge([
                'resource_no' => strtoupper(trim($this->resource_no))
            ]);
        }
        // No transformation needed for category_id
    }
}
