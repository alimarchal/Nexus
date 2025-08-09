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
        // Check if user has permission to create circulars
        // return auth()->check() && auth()->user()->can('create-circulars');
        return true;
        // Or simply return true if no permission system
        // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'circular_no' => [
                'required',
                'string',
                'max:50', // Reduced from 255 for circular numbers
                'unique:circulars,circular_no',
                'regex:/^[A-Z0-9\-\/]+$/' // Allow only uppercase letters, numbers, hyphens, slashes
            ],
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:5000', // Set reasonable limit
            'division_id' => 'required|exists:divisions,id',
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png', // Added more formats
                'max:5120' // 5MB limit
            ],
            'priority' => 'nullable|in:low,medium,high',
            'effective_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after:effective_date'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'circular_no.required' => 'Circular number is required.',
            'circular_no.unique' => 'This circular number already exists. Please use a different number.',
            'circular_no.regex' => 'Circular number must contain only uppercase letters, numbers, hyphens, and slashes.',
            'title.required' => 'Circular title is required.',
            'title.min' => 'Title must be at least 3 characters long.',
            'division_id.required' => 'Please select a division.',
            'division_id.exists' => 'Selected division does not exist.',
            'attachment.mimes' => 'Attachment must be a PDF, Word document, or image file.',
            'attachment.max' => 'Attachment size cannot exceed 5MB.',
            'effective_date.after_or_equal' => 'Effective date cannot be in the past.',
            'expiry_date.after' => 'Expiry date must be after the effective date.'
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Auto-format circular number to uppercase
        if ($this->circular_no) {
            $this->merge([
                'circular_no' => strtoupper(trim($this->circular_no))
            ]);
        }
    }

    /**
     * Custom validation attributes for better error messages
     */
    public function attributes(): array
    {
        return [
            'circular_no' => 'circular number',
            'division_id' => 'division',
            'effective_date' => 'effective date',
            'expiry_date' => 'expiry date'
        ];
    }
}