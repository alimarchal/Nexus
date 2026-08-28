<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'fileable_type' => ['required', Rule::in($this->allowedFileableTypes())],
            'fileable_id' => ['required', 'integer'],
            'document_date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'pages' => ['nullable', 'array'],
            'pages.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }

    /**
     * Branch may only be selected as an org unit by super admins; other users
     * pick region/division (or have their branch auto-assigned).
     *
     * @return array<int, string>
     */
    private function allowedFileableTypes(): array
    {
        $user = $this->user();
        $isSuperAdmin = $user && ($user->is_super_admin === 'Yes' || $user->hasRole('super-admin'));

        return $isSuperAdmin ? ['branch', 'region', 'division'] : ['region', 'division'];
    }
}
