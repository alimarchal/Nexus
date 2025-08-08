<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreComplaintRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   // In StoreComplaintRequest:
public function rules()
{
    return [
        'subject' => 'required|string|max:255',
        'description' => 'required|string',
        'status_id' => 'required|exists:complaint_status_types,id',
        'assigned_to' => 'required|exists:divisions,id',
        'due_date' => 'required|date|after_or_equal:today',
        'priority' => 'sometimes|in:low,medium,high',
        'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,png,doc,docx'
    ];
}
}
