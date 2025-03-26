<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            // 'description' => 'required|string',
            'status_id' => 'required|exists:complaint_status_types,id',
            'assigned_to' => 'required|exists:divisions,id',
            // 'priority' => 'required|in:low,medium,high',
            // 'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(7)->toDateString()],
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048'
        ];
    }
}