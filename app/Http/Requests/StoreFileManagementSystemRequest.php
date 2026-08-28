<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileManagementSystemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create file management systems') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file_category_id' => ['required', 'uuid', 'exists:file_categories,id'],
            'fileable_type' => ['required', 'in:branch,region,division'],
            'fileable_id' => ['required', 'integer'],
            'document_date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }
}
