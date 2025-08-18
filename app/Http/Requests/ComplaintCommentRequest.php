<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        // Allow comment if user has access to complaint
        return auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->id() === $complaint->assigned_to ||
            auth()->id() === $complaint->created_by ||
            auth()->user()->can('comment-complaints')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment_text' => [
                'required',
                'string',
                'min:5',
                'max:2000'
            ],
            'comment_type' => [
                'required',
                Rule::in(['Internal', 'Customer', 'System'])
            ],
            'is_private' => [
                'nullable',
                'boolean'
            ],
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,txt',
                'max:5120' // 5MB max
            ]
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'comment_text' => 'comment',
            'comment_type' => 'comment type',
            'is_private' => 'privacy setting',
            'attachment' => 'attachment file'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'comment_text.required' => 'The comment text is required.',
            'comment_text.min' => 'The comment must be at least 5 characters.',
            'comment_text.max' => 'The comment cannot exceed 2000 characters.',
            'comment_type.required' => 'Please select a comment type.',
            'comment_type.in' => 'The selected comment type is invalid.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.mimes' => 'The attachment must be a PDF, DOC, DOCX, JPG, JPEG, PNG, or TXT file.',
            'attachment.max' => 'The attachment cannot exceed 5MB.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('comment_text')) {
            $this->merge([
                'comment_text' => trim($this->comment_text)
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $complaint = $this->route('complaint');

            // Validate that system comments can only be added by admins
            if ($this->input('comment_type') === 'System' && !auth()->user()->hasRole('admin')) {
                $validator->errors()->add(
                    'comment_type',
                    'Only administrators can add system comments.'
                );
            }

            // Validate that customer comments should not be private
            if ($this->input('comment_type') === 'Customer' && $this->boolean('is_private')) {
                $validator->errors()->add(
                    'is_private',
                    'Customer comments should be public.'
                );
            }

            // Validate that closed complaints cannot receive customer comments
            if ($complaint->status === 'Closed' && $this->input('comment_type') === 'Customer') {
                $validator->errors()->add(
                    'comment_type',
                    'Customer comments cannot be added to closed complaints.'
                );
            }
        });
    }
}