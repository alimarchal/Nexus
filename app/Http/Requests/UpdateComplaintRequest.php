<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:complaint_status_types,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,pdf|max:2048', // Optional file uploads
        ];
    }
    }