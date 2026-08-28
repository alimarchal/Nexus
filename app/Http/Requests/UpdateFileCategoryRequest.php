<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFileCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit file categories') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $fileCategory = $this->route('file_category');

        return [
            'category_code' => ['required', 'string', 'max:20', 'unique:file_categories,category_code,'.$fileCategory->id],
            'category_name' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'in:0,1'],
        ];
    }
}
