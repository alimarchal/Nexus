<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCircularRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return auth()->check() && auth()->user()->can('update-circulars');
        return true;
        // Or simply return true if no permission system
        // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $circularId = $this->route('circular')->id;
        
        return [
            'circular_no' => [
                'required',
                'string',
                'max:50',
                'unique:circulars,circular_no,' . $circularId,
                'regex:/^[A-Z0-9\-\/]+$/'
            ],
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:5000',
            'division_id' => 'required|exists:divisions,id',
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:5120'
            ],
            'priority' => 'nullable|in:low,medium,high',
            'effective_date' => 'nullable|date',
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
            'expiry_date.after' => 'Expiry date must be after the effective date.'
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->circular_no) {
            $this->merge([
                'circular_no' => strtoupper(trim($this->circular_no))
            ]);
        }
    }
}