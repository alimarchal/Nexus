<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audit_type_id' => 'required|exists:audit_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope_summary' => 'nullable|string',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'lead_auditor_id' => 'nullable|exists:users,id',
            'auditee_user_id' => 'nullable|exists:users,id',
            'risk_overall' => 'nullable|in:low,medium,high,critical',
            'is_template' => 'sometimes|boolean',
            'parent_audit_id' => 'nullable|exists:audits,id',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip|max:10240',
        ];
    }
}
