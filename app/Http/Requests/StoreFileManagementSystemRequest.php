<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'file_no' => ['nullable', 'string', 'max:60'],
            'fileable_type' => ['required', Rule::in($this->allowedFileableTypes())],
            'fileable_id' => ['required', 'integer'],
            'document_date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }

    /**
     * Super-admins may select an organization unit. Other users submit their
     * own assigned unit, which is enforced by the controller.
     *
     * @return array<int, string>
     */
    private function allowedFileableTypes(): array
    {
        $user = $this->user();
        $isSuperAdmin = $user && ($user->is_super_admin === 'Yes' || $user->hasRole('super-admin'));

        if ($isSuperAdmin) {
            return ['branch', 'region', 'division', 'head-office'];
        }

        return match (true) {
            $user?->hasRole('branch') && $user->branch_id => ['branch'],
            $user?->hasRole('region') && $user->region_id => ['region'],
            $user?->hasRole('division') && $user->division_id => ['division'],
            $user?->hasRole('head-office') && $user->head_office_id => ['head-office'],
            default => [],
        };
    }
}
