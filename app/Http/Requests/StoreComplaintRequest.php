<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            // Core complaint details
            'title' => [
                'required',
                'string',
                'max:255',
                'min:5'
            ],
            'description' => [
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
            'priority' => [
                'required',
                Rule::in(['Low', 'Medium', 'High', 'Critical'])
            ],
            'source' => [
                'required',
                Rule::in(['Phone', 'Email', 'Portal', 'Walk-in', 'Other'])
            ],

            // Complainant contact details
            'complainant_name' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/' // Only letters, spaces, hyphens, dots, apostrophes
            ],
            'complainant_email' => [
                'nullable',
                'email',
                'max:100'
            ],
            'complainant_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/' // Phone number format
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
            'region_id' => [
                'nullable',
                'exists:regions,id'
            ],
            'division_id' => [
                'nullable',
                'exists:divisions,id'
            ],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                'different:created_by' // Cannot assign to self during creation
            ],

            // SLA and timeline
            'expected_resolution_date' => [
                'nullable',
                'date',
                'after:today'
            ],

            // File attachments
            'attachments' => [
                'nullable',
                'array',
                'max:10' // Maximum 10 files
            ],
            'attachments.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar',
                'max:10240' // 10MB max per file
            ],

            // Initial comment
            'comments' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'comment_type' => [
                'nullable',
                Rule::in(['Internal', 'Customer', 'System']),
                'required_with:comments'
            ],
            'is_private' => [
                'nullable',
                'boolean'
            ],

            // Category assignment
            'category_id' => [
                'nullable',
                'exists:complaint_categories,id'
            ],

            // Watchers
            'watchers' => [
                'nullable',
                'array',
                'max:20' // Maximum 20 watchers
            ],
            'watchers.*' => [
                'exists:users,id',
                'distinct' // No duplicate watchers
            ],

            // Additional fields
            'template_id' => [
                'nullable',
                'exists:complaint_templates,id'
            ],
            'urgency_level' => [
                'nullable',
                'integer',
                'between:1,5'
            ],
            'customer_impact' => [
                'nullable',
                Rule::in(['Low', 'Medium', 'High', 'Critical'])
            ],
            'business_impact' => [
                'nullable',
                Rule::in(['Low', 'Medium', 'High', 'Critical'])
            ],

            // Harassment specific (conditionally required when category is Harassment)
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'harassment_location' => [
                'nullable',
                'string',
                'max:150'
            ],
            'harassment_witnesses' => [
                'nullable',
                'string',
                'max:255'
            ],
            'harassment_reported_to' => [
                'nullable',
                'string',
                'max:150'
            ],
            'harassment_details' => [
                'nullable',
                'string',
                'min:10',
                'max:5000'
            ],
            'harassment_confidential' => [
                'nullable',
                'boolean'
            ],

            // Sub-category for harassment
            'harassment_sub_category' => [
                'nullable',
                'string',
                'max:150'
            ],

            // Harassment employee (complainant or victim) extra identifiers
            'harassment_employee_number' => [
                'nullable',
                'string',
                'max:50'
            ],
            'harassment_employee_phone' => [
                'nullable',
                'string',
                'max:50'
            ],

            // Abuser details (for harassment cases)
            'harassment_abuser_employee_number' => ['nullable', 'string', 'max:50'],
            'harassment_abuser_name' => ['nullable', 'string', 'max:150'],
            'harassment_abuser_phone' => ['nullable', 'string', 'max:50'],
            'harassment_abuser_email' => ['nullable', 'email', 'max:150'],
            'harassment_abuser_relationship' => ['nullable', 'string', 'max:100'],

            // Witnesses dynamic arrays
            'witnesses' => ['nullable', 'array', 'max:10'],
            'witnesses.*.employee_number' => ['nullable', 'string', 'max:50'],
            'witnesses.*.name' => ['required_with:witnesses', 'string', 'max:150'],
            'witnesses.*.phone' => ['nullable', 'string', 'max:50'],
            'witnesses.*.email' => ['nullable', 'email', 'max:150'],
            'witnesses.*.statement' => ['nullable', 'string', 'max:2000'],
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
            'region_id' => 'region',
            'division_id' => 'division',
            'assigned_to' => 'assigned user',
            'expected_resolution_date' => 'expected resolution date',
            'attachments.*' => 'attachment file',
            'comment_type' => 'comment type',
            'category_id' => 'complaint category',
            'watchers.*' => 'watcher',
            'template_id' => 'template'
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

            'source.required' => 'Please select how this complaint was received.',
            'source.in' => 'The selected source is invalid.',

            'complainant_name.regex' => 'The complainant name may only contain letters, spaces, hyphens, dots, and apostrophes.',
            'complainant_email.email' => 'Please provide a valid email address.',
            'complainant_phone.regex' => 'Please provide a valid phone number format.',
            'complainant_account_number.alpha_num' => 'The account number may only contain letters and numbers.',

            'branch_id.exists' => 'The selected branch is invalid.',
            'assigned_to.exists' => 'The selected assignee is invalid.',
            'assigned_to.different' => 'You cannot assign a complaint to yourself during creation.',

            'expected_resolution_date.after' => 'The expected resolution date must be after today.',

            'attachments.max' => 'You cannot upload more than 10 files.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'File type not allowed. Please upload PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, TXT, ZIP, or RAR files.',
            'attachments.*.max' => 'Each file cannot exceed 10MB.',

            'comments.max' => 'The initial comment cannot exceed 2000 characters.',
            'comment_type.required_with' => 'Please select a comment type when adding a comment.',
            'comment_type.in' => 'The selected comment type is invalid.',

            'category_id.exists' => 'The selected category is invalid.',

            'watchers.max' => 'You cannot add more than 20 watchers.',
            'watchers.*.exists' => 'One or more selected watchers are invalid.',
            'watchers.*.distinct' => 'Duplicate watchers are not allowed.',

            'template_id.exists' => 'The selected template is invalid.',

            'urgency_level.between' => 'The urgency level must be between 1 and 5.',

            'customer_impact.in' => 'The selected customer impact level is invalid.',
            'business_impact.in' => 'The selected business impact level is invalid.'
            ,

            // Harassment
            'harassment_incident_date.before_or_equal' => 'Incident date cannot be in the future.',
            'harassment_details.min' => 'Harassment details must be at least 10 characters.',
            'harassment_details.max' => 'Harassment details may not exceed 5000 characters.'
            ,
            'harassment_sub_category.max' => 'Harassment sub-category may not exceed 150 characters.',
            'harassment_sub_category.required' => 'Sub category is required for harassment complaints.',
            'harassment_employee_number.max' => 'Employee number may not exceed 50 characters.',
            'harassment_employee_phone.max' => 'Employee phone may not exceed 50 characters.',
            'witnesses.max' => 'No more than 10 witnesses may be added.',
            'witnesses.*.name.required_with' => 'Each witness requires a name.',
            'witnesses.*.email.email' => 'Witness email must be valid.'
            ,
            'harassment_abuser_employee_number.max' => 'Abuser employee number may not exceed 50 characters.',
            'harassment_abuser_name.max' => 'Abuser name may not exceed 150 characters.',
            'harassment_abuser_phone.max' => 'Abuser phone may not exceed 50 characters.',
            'harassment_abuser_email.email' => 'Abuser email must be valid.',
            'harassment_abuser_email.max' => 'Abuser email may not exceed 150 characters.',
            'harassment_abuser_relationship.max' => 'Abuser relationship may not exceed 100 characters.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
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

        // Remove empty watchers
        if ($this->has('watchers')) {
            $this->merge([
                'watchers' => array_filter($this->watchers, function ($value) {
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
            // Custom validation logic

            // Validate that at least one contact method is provided if complainant name is given
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

            // Validate file count and total size
            if ($this->hasFile('attachments')) {
                $totalSize = 0;
                foreach ($this->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $totalSize += $file->getSize();
                    }
                }

                // Check total file size (50MB limit)
                if ($totalSize > 52428800) { // 50MB in bytes
                    $validator->errors()->add(
                        'attachments',
                        'The total size of all attachments cannot exceed 50MB.'
                    );
                }
            }

            // Validate priority vs expected resolution date
            if ($this->filled('priority') && $this->filled('expected_resolution_date')) {
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

            // Conditional harassment field requirements
            $isHarassment = false;
            if ($this->filled('category_id')) {
                $cat = \App\Models\ComplaintCategory::find($this->input('category_id'));
                if ($cat && strcasecmp($cat->category_name, 'Harassment') === 0) {
                    $isHarassment = true;
                }
            }

            if ($isHarassment) {
                $requiredFields = [
                    'harassment_sub_category' => 'Sub category is required for harassment complaints.',
                    'harassment_incident_date' => 'Incident date is required for harassment complaints.',
                    'harassment_location' => 'Location is required for harassment complaints.',
                    'harassment_details' => 'Details/evidence summary is required for harassment complaints.'
                ];
                foreach ($requiredFields as $field => $message) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add($field, $message);
                    }
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
                return true;
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $validated;
    }
}