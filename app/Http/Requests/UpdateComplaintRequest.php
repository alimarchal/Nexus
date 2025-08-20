<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        // Allow update if user is admin, assigned to complaint, or complaint creator
        return auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->id() === $complaint->assigned_to ||
            auth()->id() === $complaint->created_by ||
            auth()->user()->can('update-complaints')
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

        return [
            // Core complaint details
            // Use "sometimes" so PATCH/partial updates (from the show page operations) can submit
            // only the fields they intend to change without failing validation for missing fields.
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:5'
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:5000'
            ],
            'category' => [
                'nullable',
                'string',
                'max:255'
            ],

            // Business logic fields
            // Business logic fields (allow sometimes for partial updates)
            'priority' => [
                'sometimes',
                'required',
                Rule::in(['Low', 'Medium', 'High', 'Critical'])
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::in(['Open', 'In Progress', 'Pending', 'Resolved', 'Closed', 'Reopened'])
            ],
            'source' => [
                'sometimes',
                'required',
                Rule::in(['Phone', 'Email', 'Portal', 'Walk-in', 'Other'])
            ],

            // Complainant contact details
            'complainant_name' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'complainant_email' => [
                'nullable',
                'email:rfc,dns',
                'max:100'
            ],
            'complainant_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'complainant_account_number' => [
                'nullable',
                'string',
                'max:50',
                'alpha_num'
            ],

            // Location and assignment
            'branch_id' => [
                'nullable',
                'exists:branches,id'
            ],
            'assigned_to' => [
                'nullable',
                'exists:users,id'
            ],

            // Resolution fields
            'resolution' => [
                'nullable',
                'string',
                'max:5000',
                'required_if:status,Resolved,Closed'
            ],
            'resolved_by' => [
                'nullable',
                'exists:users,id',
                'required_if:status,Resolved'
            ],
            'resolved_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
                'required_if:status,Resolved'
            ],

            // Timeline fields
            'expected_resolution_date' => [
                'nullable',
                'date'
            ],
            'closed_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
                'required_if:status,Closed'
            ],

            // SLA tracking
            'sla_breached' => [
                'nullable',
                'boolean'
            ],

            // File attachments
            'attachments' => [
                'nullable',
                'array',
                'max:10'
            ],
            'attachments.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar',
                'max:10240'
            ],

            // Delete existing attachments
            'delete_attachments' => [
                'nullable',
                'array'
            ],
            'delete_attachments.*' => [
                'exists:complaint_attachments,id'
            ],

            // Update comment
            'update_comment' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'comment_type' => [
                'nullable',
                Rule::in(['Internal', 'Customer', 'System']),
                'required_with:update_comment'
            ],
            'is_private' => [
                'nullable',
                'boolean'
            ],

            // Reassignment reason
            'assignment_reason' => [
                'nullable',
                'string',
                'max:500',
                'required_if:assignment_changed,true'
            ],

            // Status change reason
            'status_change_reason' => [
                'nullable',
                'string',
                'max:500'
            ],

            // Customer satisfaction (for resolved complaints)
            'customer_satisfaction_score' => [
                'nullable',
                'numeric',
                'between:1,5'
            ],

            // Escalation fields
            'escalate' => [
                'nullable',
                'boolean'
            ],
            'escalated_to' => [
                'nullable',
                'exists:users,id',
                'required_if:escalate,true'
            ],
            'escalation_reason' => [
                'nullable',
                'string',
                'max:1000',
                'required_if:escalate,true'
            ],
            'escalation_level' => [
                'nullable',
                'integer',
                'between:1,5',
                'required_if:escalate,true'
            ],

            // Watchers update
            'watchers' => [
                'nullable',
                'array',
                'max:20'
            ],
            'watchers.*' => [
                'exists:users,id',
                'distinct'
            ],

            // Additional update fields
            'reopen_reason' => [
                'nullable',
                'string',
                'max:1000',
                'required_if:status,Reopened'
            ],

            'priority_change_reason' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'complaint title',
            'description' => 'complaint description',
            'complainant_name' => 'complainant name',
            'complainant_email' => 'complainant email',
            'complainant_phone' => 'complainant phone',
            'complainant_account_number' => 'account number',
            'branch_id' => 'branch',
            'assigned_to' => 'assigned user',
            'resolved_by' => 'resolved by user',
            'resolved_at' => 'resolution date',
            'expected_resolution_date' => 'expected resolution date',
            'closed_at' => 'closure date',
            'attachments.*' => 'attachment file',
            'delete_attachments.*' => 'attachment to delete',
            'update_comment' => 'update comment',
            'comment_type' => 'comment type',
            'assignment_reason' => 'assignment reason',
            'status_change_reason' => 'status change reason',
            'customer_satisfaction_score' => 'customer satisfaction score',
            'escalated_to' => 'escalation recipient',
            'escalation_reason' => 'escalation reason',
            'escalation_level' => 'escalation level',
            'watchers.*' => 'watcher',
            'reopen_reason' => 'reopen reason',
            'priority_change_reason' => 'priority change reason'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The complaint title is required.',
            'title.min' => 'The complaint title must be at least 5 characters.',
            'title.max' => 'The complaint title cannot exceed 255 characters.',

            'description.required' => 'The complaint description is required.',
            'description.min' => 'The complaint description must be at least 10 characters.',
            'description.max' => 'The complaint description cannot exceed 5000 characters.',

            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'The selected priority is invalid.',

            'status.required' => 'Please select a status.',
            'status.in' => 'The selected status is invalid.',

            'source.required' => 'Please select how this complaint was received.',
            'source.in' => 'The selected source is invalid.',

            'complainant_name.regex' => 'The complainant name may only contain letters, spaces, hyphens, dots, and apostrophes.',
            'complainant_email.email' => 'Please provide a valid email address.',
            'complainant_phone.regex' => 'Please provide a valid phone number format.',
            'complainant_account_number.alpha_num' => 'The account number may only contain letters and numbers.',

            'branch_id.exists' => 'The selected branch is invalid.',
            'assigned_to.exists' => 'The selected assignee is invalid.',

            'resolution.required_if' => 'Resolution details are required when marking complaint as resolved or closed.',
            'resolution.max' => 'The resolution cannot exceed 5000 characters.',

            'resolved_by.required_if' => 'Please specify who resolved the complaint.',
            'resolved_by.exists' => 'The selected resolver is invalid.',

            'resolved_at.required_if' => 'Please specify when the complaint was resolved.',
            'resolved_at.before_or_equal' => 'The resolution date cannot be in the future.',

            'closed_at.required_if' => 'Please specify when the complaint was closed.',
            'closed_at.before_or_equal' => 'The closure date cannot be in the future.',

            'attachments.max' => 'You cannot upload more than 10 files.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'File type not allowed. Please upload PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, TXT, ZIP, or RAR files.',
            'attachments.*.max' => 'Each file cannot exceed 10MB.',

            'delete_attachments.*.exists' => 'One or more attachments to delete are invalid.',

            'update_comment.max' => 'The update comment cannot exceed 2000 characters.',
            'comment_type.required_with' => 'Please select a comment type when adding a comment.',
            'comment_type.in' => 'The selected comment type is invalid.',

            'assignment_reason.required_if' => 'Please provide a reason for the assignment change.',
            'assignment_reason.max' => 'The assignment reason cannot exceed 500 characters.',

            'status_change_reason.max' => 'The status change reason cannot exceed 500 characters.',

            'customer_satisfaction_score.between' => 'The customer satisfaction score must be between 1 and 5.',
            'customer_satisfaction_score.numeric' => 'The customer satisfaction score must be a number.',

            'escalated_to.required_if' => 'Please select who to escalate the complaint to.',
            'escalated_to.exists' => 'The selected escalation recipient is invalid.',

            'escalation_reason.required_if' => 'Please provide a reason for escalation.',
            'escalation_reason.max' => 'The escalation reason cannot exceed 1000 characters.',

            'escalation_level.required_if' => 'Please specify the escalation level.',
            'escalation_level.between' => 'The escalation level must be between 1 and 5.',

            'watchers.max' => 'You cannot add more than 20 watchers.',
            'watchers.*.exists' => 'One or more selected watchers are invalid.',
            'watchers.*.distinct' => 'Duplicate watchers are not allowed.',

            'reopen_reason.required_if' => 'Please provide a reason for reopening the complaint.',
            'reopen_reason.max' => 'The reopen reason cannot exceed 1000 characters.',

            'priority_change_reason.max' => 'The priority change reason cannot exceed 500 characters.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $complaint = $this->route('complaint');

        // Clean and prepare data before validation
        if ($this->has('complainant_phone')) {
            $this->merge([
                'complainant_phone' => preg_replace('/[^\+0-9\s\-\(\)]/', '', $this->complainant_phone)
            ]);
        }

        if ($this->has('complainant_name')) {
            $this->merge([
                'complainant_name' => trim($this->complainant_name)
            ]);
        }

        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title)
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description)
            ]);
        }

        // Check if assignment is being changed
        if ($this->has('assigned_to') && $complaint->assigned_to != $this->input('assigned_to')) {
            $this->merge(['assignment_changed' => true]);
        }

        // Auto-fill resolution fields for resolved status
        if ($this->input('status') === 'Resolved' && !$this->has('resolved_at')) {
            $this->merge([
                'resolved_at' => now()->format('Y-m-d H:i:s'),
                'resolved_by' => auth()->id()
            ]);
        }

        // Auto-fill closure date for closed status
        if ($this->input('status') === 'Closed' && !$this->has('closed_at')) {
            $this->merge([
                'closed_at' => now()->format('Y-m-d H:i:s')
            ]);
        }

        // Remove empty watchers
        if ($this->has('watchers')) {
            $this->merge([
                'watchers' => array_filter($this->watchers, function ($value) {
                    return !empty($value);
                })
            ]);
        }

        // Remove empty delete_attachments
        if ($this->has('delete_attachments')) {
            $this->merge([
                'delete_attachments' => array_filter($this->delete_attachments, function ($value) {
                    return !empty($value);
                })
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $complaint = $this->route('complaint');

            // Custom validation logic

            // Validate that complaint cannot be reopened if not previously closed
            if (
                $this->input('status') === 'Reopened' &&
                !in_array($complaint->status, ['Resolved', 'Closed'])
            ) {
                $validator->errors()->add(
                    'status',
                    'A complaint can only be reopened if it was previously resolved or closed.'
                );
            }

            // Validate that resolved complaints have resolution text
            if ($this->input('status') === 'Resolved' && empty($this->input('resolution'))) {
                $validator->errors()->add(
                    'resolution',
                    'Resolution details are required when marking complaint as resolved.'
                );
            }

            // Validate escalation logic
            if ($this->boolean('escalate')) {
                $escalatedTo = $this->input('escalated_to');

                // Cannot escalate to same person
                if ($escalatedTo === $complaint->assigned_to) {
                    $validator->errors()->add(
                        'escalated_to',
                        'Cannot escalate complaint to the currently assigned person.'
                    );
                }

                // Cannot escalate to self
                if ($escalatedTo === auth()->id()) {
                    $validator->errors()->add(
                        'escalated_to',
                        'Cannot escalate complaint to yourself.'
                    );
                }
            }

            // Validate file count and total size for new attachments
            if ($this->hasFile('attachments')) {
                $totalSize = 0;
                $existingAttachments = $complaint->attachments()->count();
                $newAttachmentCount = count($this->file('attachments'));
                $deletingCount = $this->has('delete_attachments') ? count($this->input('delete_attachments')) : 0;

                $finalAttachmentCount = $existingAttachments + $newAttachmentCount - $deletingCount;

                if ($finalAttachmentCount > 20) {
                    $validator->errors()->add(
                        'attachments',
                        'Total attachments cannot exceed 20 files. Please delete some existing files first.'
                    );
                }

                foreach ($this->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $totalSize += $file->getSize();
                    }
                }

                // Check total file size (50MB limit)
                if ($totalSize > 52428800) { // 50MB in bytes
                    $validator->errors()->add(
                        'attachments',
                        'The total size of new attachments cannot exceed 50MB.'
                    );
                }
            }

            // Validate that closed complaints cannot be assigned
            if ($this->input('status') === 'Closed' && $this->filled('assigned_to')) {
                $validator->errors()->add(
                    'assigned_to',
                    'Closed complaints cannot be assigned to users.'
                );
            }

            // Validate resolution date is not before creation date
            if ($this->filled('resolved_at')) {
                $resolvedAt = \Carbon\Carbon::parse($this->input('resolved_at'));
                if ($resolvedAt->lt($complaint->created_at)) {
                    $validator->errors()->add(
                        'resolved_at',
                        'Resolution date cannot be before the complaint creation date.'
                    );
                }
            }

            // Validate closure date is not before resolution date
            if ($this->filled('closed_at') && $this->filled('resolved_at')) {
                $closedAt = \Carbon\Carbon::parse($this->input('closed_at'));
                $resolvedAt = \Carbon\Carbon::parse($this->input('resolved_at'));
                if ($closedAt->lt($resolvedAt)) {
                    $validator->errors()->add(
                        'closed_at',
                        'Closure date cannot be before the resolution date.'
                    );
                }
            }

            // Validate priority change with business rules
            if ($this->filled('priority') && $complaint->priority !== $this->input('priority')) {
                $oldPriority = $complaint->priority;
                $newPriority = $this->input('priority');

                // If escalating priority to Critical, require reason
                if ($newPriority === 'Critical' && $oldPriority !== 'Critical' && !$this->filled('priority_change_reason')) {
                    $validator->errors()->add(
                        'priority_change_reason',
                        'A reason is required when escalating priority to Critical.'
                    );
                }
            }

            // Validate customer satisfaction score only for resolved/closed complaints
            if (
                $this->filled('customer_satisfaction_score') &&
                !in_array($this->input('status'), ['Resolved', 'Closed'])
            ) {
                $validator->errors()->add(
                    'customer_satisfaction_score',
                    'Customer satisfaction score can only be set for resolved or closed complaints.'
                );
            }

            // Validate that at least one contact method exists if complainant name is provided
            if (
                $this->filled('complainant_name') &&
                !$this->filled('complainant_email') &&
                !$this->filled('complainant_phone')
            ) {
                $validator->errors()->add(
                    'complainant_email',
                    'Please provide either an email or phone number for the complainant.'
                );
            }

            // Business rule: Cannot change status from Closed to anything except Reopened
            if (
                $complaint->status === 'Closed' &&
                $this->input('status') !== 'Closed' &&
                $this->input('status') !== 'Reopened'
            ) {
                $validator->errors()->add(
                    'status',
                    'Closed complaints can only be reopened, not changed to other statuses.'
                );
            }

            // Validate expected resolution date based on priority
            if ($this->filled('expected_resolution_date') && $this->filled('priority')) {
                $priority = $this->input('priority');
                $expectedDate = \Carbon\Carbon::parse($this->input('expected_resolution_date'));
                $now = \Carbon\Carbon::now();

                $maxDays = [
                    'Critical' => 1,
                    'High' => 3,
                    'Medium' => 7,
                    'Low' => 14
                ];

                if ($expectedDate->diffInDays($now) > $maxDays[$priority]) {
                    $validator->errors()->add(
                        'expected_resolution_date',
                        "For {$priority} priority complaints, expected resolution should be within {$maxDays[$priority]} day(s)."
                    );
                }
            }
        });
    }

    /**
     * Get validated data with additional processing
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Add any additional processing of validated data here
        if (is_null($key)) {
            // Remove empty optional fields
            $validated = array_filter($validated, function ($value, $key) {
                if (in_array($key, ['complainant_name', 'complainant_email', 'complainant_phone', 'complainant_account_number'])) {
                    return !empty($value);
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // Remove internal validation flags
            unset($validated['assignment_changed']);
        }

        return $validated;
    }

    /**
     * Check if status is changing to resolved
     *
     * @return bool
     */
    public function isResolvingComplaint(): bool
    {
        $complaint = $this->route('complaint');
        return $this->input('status') === 'Resolved' && $complaint->status !== 'Resolved';
    }

    /**
     * Check if status is changing to closed
     *
     * @return bool
     */
    public function isClosingComplaint(): bool
    {
        $complaint = $this->route('complaint');
        return $this->input('status') === 'Closed' && $complaint->status !== 'Closed';
    }

    /**
     * Check if complaint is being reopened
     *
     * @return bool
     */
    public function isReopeningComplaint(): bool
    {
        $complaint = $this->route('complaint');
        return $this->input('status') === 'Reopened' &&
            in_array($complaint->status, ['Resolved', 'Closed']);
    }

    /**
     * Check if assignment is changing
     *
     * @return bool
     */
    public function isAssignmentChanging(): bool
    {
        $complaint = $this->route('complaint');
        return $this->has('assigned_to') && $complaint->assigned_to != $this->input('assigned_to');
    }

    /**
     * Check if priority is changing
     *
     * @return bool
     */
    public function isPriorityChanging(): bool
    {
        $complaint = $this->route('complaint');
        return $this->has('priority') && $complaint->priority != $this->input('priority');
    }

    /**
     * Get the original complaint model
     *
     * @return \App\Models\Complaint
     */
    public function getComplaint()
    {
        return $this->route('complaint');
    }
}