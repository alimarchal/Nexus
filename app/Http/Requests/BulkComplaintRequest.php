<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow users with bulk operations permission
        return auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->can('bulk-update-complaints')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'complaint_ids' => [
                'required',
                'array',
                'min:1',
                'max:100' // Limit bulk operations to 100 complaints
            ],
            'complaint_ids.*' => [
                'exists:complaints,id'
            ],
            'operation_type' => [
                'required',
                Rule::in(['status_update', 'assignment', 'priority_change', 'branch_transfer', 'bulk_comment', 'bulk_delete'])
            ]
        ];

        // Add specific rules based on operation type
        switch ($this->input('operation_type')) {
            case 'status_update':
                $rules = array_merge($rules, [
                    'status' => [
                        'required',
                        Rule::in(['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'])
                    ],
                    'bulk_comment' => [
                        'nullable',
                        'string',
                        'max:1000'
                    ],
                    'status_change_reason' => [
                        'required_if:status,Resolved,Closed',
                        'string',
                        'max:500'
                    ]
                ]);
                break;

            case 'assignment':
                $rules = array_merge($rules, [
                    'assigned_to' => [
                        'required',
                        'exists:users,id'
                    ],
                    'assignment_reason' => [
                        'required',
                        'string',
                        'min:5',
                        'max:500'
                    ],
                    'notify_assignee' => [
                        'nullable',
                        'boolean'
                    ]
                ]);
                break;

            case 'priority_change':
                $rules = array_merge($rules, [
                    'priority' => [
                        'required',
                        Rule::in(['Low', 'Medium', 'High', 'Critical'])
                    ],
                    'priority_change_reason' => [
                        'required',
                        'string',
                        'min:5',
                        'max:500'
                    ]
                ]);
                break;

            case 'branch_transfer':
                $rules = array_merge($rules, [
                    'branch_id' => [
                        'required',
                        'exists:branches,id'
                    ],
                    'transfer_reason' => [
                        'required',
                        'string',
                        'min:5',
                        'max:500'
                    ]
                ]);
                break;

            case 'bulk_comment':
                $rules = array_merge($rules, [
                    'comment_text' => [
                        'required',
                        'string',
                        'min:5',
                        'max:2000'
                    ],
                    'comment_type' => [
                        'required',
                        Rule::in(['Internal', 'System'])
                    ],
                    'is_private' => [
                        'nullable',
                        'boolean'
                    ]
                ]);
                break;

            case 'bulk_delete':
                $rules = array_merge($rules, [
                    'deletion_reason' => [
                        'required',
                        'string',
                        'min:10',
                        'max:500'
                    ],
                    'confirm_deletion' => [
                        'required',
                        'accepted'
                    ]
                ]);
                break;
        }

        return $rules;
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'complaint_ids' => 'selected complaints',
            'complaint_ids.*' => 'complaint',
            'operation_type' => 'operation type',
            'status' => 'new status',
            'bulk_comment' => 'bulk comment',
            'status_change_reason' => 'status change reason',
            'assigned_to' => 'assignee',
            'assignment_reason' => 'assignment reason',
            'notify_assignee' => 'notify assignee',
            'priority' => 'new priority',
            'priority_change_reason' => 'priority change reason',
            'branch_id' => 'new branch',
            'transfer_reason' => 'transfer reason',
            'comment_text' => 'comment text',
            'comment_type' => 'comment type',
            'is_private' => 'privacy setting',
            'deletion_reason' => 'deletion reason',
            'confirm_deletion' => 'deletion confirmation'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'complaint_ids.required' => 'Please select at least one complaint.',
            'complaint_ids.array' => 'Invalid complaint selection.',
            'complaint_ids.min' => 'Please select at least one complaint.',
            'complaint_ids.max' => 'You can only process up to 100 complaints at once.',
            'complaint_ids.*.exists' => 'One or more selected complaints are invalid.',

            'operation_type.required' => 'Please select an operation type.',
            'operation_type.in' => 'The selected operation type is invalid.',

            // Status update messages
            'status.required' => 'Please select a new status.',
            'status.in' => 'The selected status is invalid.',
            'bulk_comment.max' => 'The bulk comment cannot exceed 1000 characters.',
            'status_change_reason.required_if' => 'A reason is required when resolving or closing complaints.',
            'status_change_reason.max' => 'The status change reason cannot exceed 500 characters.',

            // Assignment messages
            'assigned_to.required' => 'Please select an assignee.',
            'assigned_to.exists' => 'The selected assignee is invalid.',
            'assignment_reason.required' => 'Please provide a reason for the bulk assignment.',
            'assignment_reason.min' => 'The assignment reason must be at least 5 characters.',
            'assignment_reason.max' => 'The assignment reason cannot exceed 500 characters.',

            // Priority change messages
            'priority.required' => 'Please select a new priority.',
            'priority.in' => 'The selected priority is invalid.',
            'priority_change_reason.required' => 'Please provide a reason for the priority change.',
            'priority_change_reason.min' => 'The priority change reason must be at least 5 characters.',
            'priority_change_reason.max' => 'The priority change reason cannot exceed 500 characters.',

            // Branch transfer messages
            'branch_id.required' => 'Please select a branch.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'transfer_reason.required' => 'Please provide a reason for the branch transfer.',
            'transfer_reason.min' => 'The transfer reason must be at least 5 characters.',
            'transfer_reason.max' => 'The transfer reason cannot exceed 500 characters.',

            // Bulk comment messages
            'comment_text.required' => 'Please enter the comment text.',
            'comment_text.min' => 'The comment must be at least 5 characters.',
            'comment_text.max' => 'The comment cannot exceed 2000 characters.',
            'comment_type.required' => 'Please select a comment type.',
            'comment_type.in' => 'The selected comment type is invalid.',

            // Bulk delete messages
            'deletion_reason.required' => 'Please provide a reason for deletion.',
            'deletion_reason.min' => 'The deletion reason must be at least 10 characters.',
            'deletion_reason.max' => 'The deletion reason cannot exceed 500 characters.',
            'confirm_deletion.required' => 'You must confirm the deletion.',
            'confirm_deletion.accepted' => 'You must confirm that you want to delete the selected complaints.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean text inputs
        if ($this->has('bulk_comment')) {
            $this->merge(['bulk_comment' => trim($this->bulk_comment)]);
        }

        if ($this->has('status_change_reason')) {
            $this->merge(['status_change_reason' => trim($this->status_change_reason)]);
        }

        if ($this->has('assignment_reason')) {
            $this->merge(['assignment_reason' => trim($this->assignment_reason)]);
        }

        if ($this->has('priority_change_reason')) {
            $this->merge(['priority_change_reason' => trim($this->priority_change_reason)]);
        }

        if ($this->has('transfer_reason')) {
            $this->merge(['transfer_reason' => trim($this->transfer_reason)]);
        }

        if ($this->has('comment_text')) {
            $this->merge(['comment_text' => trim($this->comment_text)]);
        }

        if ($this->has('deletion_reason')) {
            $this->merge(['deletion_reason' => trim($this->deletion_reason)]);
        }

        // Remove empty complaint IDs
        if ($this->has('complaint_ids')) {
            $this->merge([
                'complaint_ids' => array_filter($this->complaint_ids, function ($id) {
                    return !empty($id);
                })
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $operationType = $this->input('operation_type');
            $complaintIds = $this->input('complaint_ids', []);

            if (empty($complaintIds)) {
                return;
            }

            // Get the selected complaints
            $complaints = \App\Models\Complaint::whereIn('id', $complaintIds)->get();

            // Validate based on operation type
            switch ($operationType) {
                case 'status_update':
                    $this->validateStatusUpdate($validator, $complaints);
                    break;

                case 'assignment':
                    $this->validateAssignment($validator, $complaints);
                    break;

                case 'priority_change':
                    $this->validatePriorityChange($validator, $complaints);
                    break;

                case 'bulk_delete':
                    $this->validateBulkDelete($validator, $complaints);
                    break;
            }

            // General validation: Check if user has permission to modify all selected complaints
            foreach ($complaints as $complaint) {
                if (!$this->canModifyComplaint($complaint)) {
                    $validator->errors()->add(
                        'complaint_ids',
                        "You don't have permission to modify complaint #{$complaint->complaint_number}."
                    );
                    break;
                }
            }
        });
    }

    /**
     * Validate status update operation
     */
    private function validateStatusUpdate($validator, $complaints)
    {
        $newStatus = $this->input('status');
        $invalidComplaints = [];

        foreach ($complaints as $complaint) {
            // Cannot close complaints that aren't resolved
            if ($newStatus === 'Closed' && !in_array($complaint->status, ['Resolved', 'In Progress', 'Pending'])) {
                $invalidComplaints[] = $complaint->complaint_number;
            }

            // Cannot reopen complaints that aren't closed or resolved
            if ($newStatus === 'Reopened' && !in_array($complaint->status, ['Resolved', 'Closed'])) {
                $invalidComplaints[] = $complaint->complaint_number;
            }

            // Cannot change status of already closed complaints (except reopen)
            if ($complaint->status === 'Closed' && $newStatus !== 'Reopened') {
                $invalidComplaints[] = $complaint->complaint_number;
            }
        }

        if (!empty($invalidComplaints)) {
            $validator->errors()->add(
                'status',
                'The following complaints cannot be changed to ' . $newStatus . ': ' . implode(', ', $invalidComplaints)
            );
        }

        // Validate that resolving complaints requires resolution text for critical/high priority
        if ($newStatus === 'Resolved') {
            $highPriorityComplaints = $complaints->whereIn('priority', ['Critical', 'High'])->pluck('complaint_number');
            if ($highPriorityComplaints->isNotEmpty() && !$this->filled('status_change_reason')) {
                $validator->errors()->add(
                    'status_change_reason',
                    'Resolution reason is required for high/critical priority complaints: ' . $highPriorityComplaints->implode(', ')
                );
            }
        }
    }

    /**
     * Validate assignment operation
     */
    private function validateAssignment($validator, $complaints)
    {
        $assigneeId = $this->input('assigned_to');
        $assignee = \App\Models\User::find($assigneeId);

        if (!$assignee) {
            return;
        }

        // Check if assignee has capacity (basic business rule)
        $currentAssignments = \App\Models\Complaint::where('assigned_to', $assigneeId)
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->count();

        $newAssignmentCount = $complaints->count();
        $maxCapacity = 50; // Configurable limit

        if (($currentAssignments + $newAssignmentCount) > $maxCapacity) {
            $validator->errors()->add(
                'assigned_to',
                "The selected assignee will exceed their capacity limit of {$maxCapacity} open complaints."
            );
        }

        // Cannot assign closed complaints
        $closedComplaints = $complaints->where('status', 'Closed')->pluck('complaint_number');
        if ($closedComplaints->isNotEmpty()) {
            $validator->errors()->add(
                'assigned_to',
                'Cannot assign closed complaints: ' . $closedComplaints->implode(', ')
            );
        }

        // Check if assignee has required permissions for high-priority complaints
        $criticalComplaints = $complaints->where('priority', 'Critical');
        if ($criticalComplaints->isNotEmpty() && !$assignee->hasAnyRole(['senior_agent', 'manager', 'admin'])) {
            $validator->errors()->add(
                'assigned_to',
                'The selected assignee cannot handle critical priority complaints.'
            );
        }
    }

    /**
     * Validate priority change operation
     */
    private function validatePriorityChange($validator, $complaints)
    {
        $newPriority = $this->input('priority');

        // Escalating to Critical requires special justification
        if ($newPriority === 'Critical') {
            $escalatingComplaints = $complaints->where('priority', '!=', 'Critical');
            if ($escalatingComplaints->isNotEmpty() && !$this->filled('priority_change_reason')) {
                $validator->errors()->add(
                    'priority_change_reason',
                    'Detailed justification is required when escalating complaints to Critical priority.'
                );
            }
        }

        // Cannot change priority of closed complaints
        $closedComplaints = $complaints->where('status', 'Closed')->pluck('complaint_number');
        if ($closedComplaints->isNotEmpty()) {
            $validator->errors()->add(
                'priority',
                'Cannot change priority of closed complaints: ' . $closedComplaints->implode(', ')
            );
        }

        // Validate business rule: Critical complaints must have assigned users
        if ($newPriority === 'Critical') {
            $unassignedComplaints = $complaints->whereNull('assigned_to')->pluck('complaint_number');
            if ($unassignedComplaints->isNotEmpty()) {
                $validator->errors()->add(
                    'priority',
                    'Critical priority complaints must be assigned. Unassigned complaints: ' . $unassignedComplaints->implode(', ')
                );
            }
        }
    }

    /**
     * Validate bulk delete operation
     */
    private function validateBulkDelete($validator, $complaints)
    {
        // Cannot delete complaints that have been escalated
        $escalatedComplaints = $complaints->whereHas('escalations')->pluck('complaint_number');
        if ($escalatedComplaints->isNotEmpty()) {
            $validator->errors()->add(
                'complaint_ids',
                'Cannot delete escalated complaints: ' . $escalatedComplaints->implode(', ')
            );
        }

        // Cannot delete complaints with customer satisfaction scores
        $ratedComplaints = $complaints->whereHas('metrics', function ($query) {
            $query->whereNotNull('customer_satisfaction_score');
        })->pluck('complaint_number');

        if ($ratedComplaints->isNotEmpty()) {
            $validator->errors()->add(
                'complaint_ids',
                'Cannot delete complaints with customer feedback: ' . $ratedComplaints->implode(', ')
            );
        }

        // Limit deletion to non-critical complaints unless admin
        if (!auth()->user()->hasRole('admin')) {
            $criticalComplaints = $complaints->where('priority', 'Critical')->pluck('complaint_number');
            if ($criticalComplaints->isNotEmpty()) {
                $validator->errors()->add(
                    'complaint_ids',
                    'Only administrators can delete critical complaints: ' . $criticalComplaints->implode(', ')
                );
            }
        }
    }

    /**
     * Check if user can modify a specific complaint
     */
    private function canModifyComplaint($complaint): bool
    {
        return auth()->user()->hasRole('admin') ||
            auth()->id() === $complaint->assigned_to ||
            auth()->id() === $complaint->created_by ||
            auth()->user()->can('modify-all-complaints');
    }

    /**
     * Get the operation type
     */
    public function getOperationType(): string
    {
        return $this->input('operation_type');
    }

    /**
     * Get the selected complaint IDs
     */
    public function getComplaintIds(): array
    {
        return $this->input('complaint_ids', []);
    }

    /**
     * Get the count of selected complaints
     */
    public function getComplaintCount(): int
    {
        return count($this->getComplaintIds());
    }

    /**
     * Check if this is a high-risk operation
     */
    public function isHighRiskOperation(): bool
    {
        $highRiskOperations = ['bulk_delete', 'status_update'];
        $highRiskStatuses = ['Closed', 'Resolved'];

        return in_array($this->getOperationType(), $highRiskOperations) ||
            ($this->getOperationType() === 'status_update' && in_array($this->input('status'), $highRiskStatuses)) ||
            $this->getComplaintCount() > 20;
    }

    /**
     * Get operation summary for confirmation
     */
    public function getOperationSummary(): array
    {
        $summary = [
            'operation' => $this->getOperationType(),
            'complaint_count' => $this->getComplaintCount(),
            'is_high_risk' => $this->isHighRiskOperation()
        ];

        switch ($this->getOperationType()) {
            case 'status_update':
                $summary['new_status'] = $this->input('status');
                $summary['reason'] = $this->input('status_change_reason');
                break;

            case 'assignment':
                $assignee = \App\Models\User::find($this->input('assigned_to'));
                $summary['assignee'] = $assignee ? $assignee->name : 'Unknown';
                $summary['reason'] = $this->input('assignment_reason');
                break;

            case 'priority_change':
                $summary['new_priority'] = $this->input('priority');
                $summary['reason'] = $this->input('priority_change_reason');
                break;

            case 'branch_transfer':
                $branch = \App\Models\Branch::find($this->input('branch_id'));
                $summary['new_branch'] = $branch ? $branch->name : 'Unknown';
                $summary['reason'] = $this->input('transfer_reason');
                break;

            case 'bulk_comment':
                $summary['comment_type'] = $this->input('comment_type');
                $summary['comment_preview'] = substr($this->input('comment_text'), 0, 100) . '...';
                break;

            case 'bulk_delete':
                $summary['reason'] = $this->input('deletion_reason');
                break;
        }

        return $summary;
    }
}