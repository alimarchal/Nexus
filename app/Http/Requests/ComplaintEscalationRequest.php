<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintEscalationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        // Allow escalation if user is assigned to complaint or has escalation permissions
        return auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->id() === $complaint->assigned_to ||
            auth()->user()->can('escalate-complaints')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $complaint = $this->route('complaint');
        $currentEscalationLevel = $complaint->escalations()->max('escalation_level') ?? 0;

        return [
            'escalated_to' => [
                'required',
                'exists:users,id',
                'different:' . auth()->id(), // Cannot escalate to self
                'different:' . $complaint->assigned_to // Cannot escalate to current assignee
            ],
            'escalation_reason' => [
                'required',
                'string',
                'min:10',
                'max:1000'
            ],
            'escalation_level' => [
                'required',
                'integer',
                'min:' . ($currentEscalationLevel + 1),
                'max:5'
            ],
            'urgency_justification' => [
                'nullable',
                'string',
                'max:500'
            ],
            'expected_resolution_date' => [
                'nullable',
                'date',
                'after:now'
            ],
            'notify_stakeholders' => [
                'nullable',
                'boolean'
            ],
            'escalation_type' => [
                'required',
                Rule::in(['Management', 'Technical', 'Legal', 'Customer Service'])
            ]
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'escalated_to' => 'escalation recipient',
            'escalation_reason' => 'escalation reason',
            'escalation_level' => 'escalation level',
            'urgency_justification' => 'urgency justification',
            'expected_resolution_date' => 'expected resolution date',
            'escalation_type' => 'escalation type'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'escalated_to.required' => 'Please select who to escalate the complaint to.',
            'escalated_to.exists' => 'The selected escalation recipient is invalid.',
            'escalated_to.different' => 'You cannot escalate a complaint to yourself or the current assignee.',

            'escalation_reason.required' => 'Please provide a reason for escalation.',
            'escalation_reason.min' => 'The escalation reason must be at least 10 characters.',
            'escalation_reason.max' => 'The escalation reason cannot exceed 1000 characters.',

            'escalation_level.required' => 'Please specify the escalation level.',
            'escalation_level.min' => 'Escalation level must be higher than the current level.',
            'escalation_level.max' => 'Maximum escalation level is 5.',

            'urgency_justification.max' => 'The urgency justification cannot exceed 500 characters.',

            'expected_resolution_date.after' => 'The expected resolution date must be in the future.',

            'escalation_type.required' => 'Please select the type of escalation.',
            'escalation_type.in' => 'The selected escalation type is invalid.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('escalation_reason')) {
            $this->merge([
                'escalation_reason' => trim($this->escalation_reason)
            ]);
        }

        if ($this->has('urgency_justification')) {
            $this->merge([
                'urgency_justification' => trim($this->urgency_justification)
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

            // Validate that complaint is not already resolved or closed
            if (in_array($complaint->status, ['Resolved', 'Closed'])) {
                $validator->errors()->add(
                    'escalation_reason',
                    'Cannot escalate a resolved or closed complaint.'
                );
            }

            // Validate escalation hierarchy (basic business rule)
            $escalatedToUser = \App\Models\User::find($this->input('escalated_to'));
            if ($escalatedToUser && !$escalatedToUser->hasAnyRole(['manager', 'admin', 'supervisor'])) {
                $validator->errors()->add(
                    'escalated_to',
                    'Complaints can only be escalated to managers, supervisors, or administrators.'
                );
            }

            // Validate that high-level escalations require urgency justification
            if ($this->input('escalation_level') >= 3 && !$this->filled('urgency_justification')) {
                $validator->errors()->add(
                    'urgency_justification',
                    'High-level escalations (Level 3+) require urgency justification.'
                );
            }

            // Validate escalation frequency (prevent spam escalations)
            $recentEscalations = $complaint->escalations()
                ->where('escalated_at', '>=', now()->subHours(24))
                ->count();

            if ($recentEscalations >= 3) {
                $validator->errors()->add(
                    'escalation_reason',
                    'This complaint has been escalated too frequently. Please wait before escalating again.'
                );
            }

            // Validate that Critical priority complaints require Management escalation
            if ($complaint->priority === 'Critical' && $this->input('escalation_type') !== 'Management') {
                $validator->errors()->add(
                    'escalation_type',
                    'Critical priority complaints must be escalated to Management.'
                );
            }
        });
    }

    /**
     * Get the current escalation level
     */
    public function getCurrentEscalationLevel(): int
    {
        $complaint = $this->route('complaint');
        return $complaint->escalations()->max('escalation_level') ?? 0;
    }

    /**
     * Check if this is a high-level escalation
     */
    public function isHighLevelEscalation(): bool
    {
        return $this->input('escalation_level') >= 3;
    }
}