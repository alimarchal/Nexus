<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCircularRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Changed from false to true
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'circular_no' => 'required|string|max:255|unique:circulars,circular_no',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'division_id' => 'required|exists:divisions,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'circular_no.unique' => 'This circular number is already taken. Please use a different circular number.',
        ];
    }
}