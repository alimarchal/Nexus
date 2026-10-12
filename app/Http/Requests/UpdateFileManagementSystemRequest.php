<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFileManagementSystemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit file management systems') ?? false;
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
            'file_no' => ['nullable', 'string', 'max:60'],
            'document_date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'pages' => ['nullable', 'array'],
            'pages.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }

    /**
     * A record always keeps at least one scanned page: when it has none yet,
     * this edit must upload one.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $document = $this->route('file_management_system');

            if ($document && $document->media()->count() === 0 && ! $this->hasFile('pages')) {
                $validator->errors()->add('pages', 'Upload at least one scanned page before saving this record.');
            }
        });
    }
}
