<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\User;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\ComplaintComment;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintEscalation;
use App\Models\ComplaintWatcher;
use App\Models\ComplaintMetric;
use App\Models\ComplaintStatusType;
use App\Models\ComplaintTemplate;
use App\Models\ComplaintWitness;
use App\View\Components\Division;
use Illuminate\Http\Request;
use App\Helpers\FileStorageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Carbon\Carbon;

/**
 * ComplaintController handles comprehensive CRUD operations for complaints
 * Manages file uploads, assignments, escalations, histories, and all related entities
 */
class ComplaintController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view complaints', only: ['index', 'show']),
            new Middleware('role_or_permission:create complaints', only: ['create', 'store']),
            new Middleware('role_or_permission:edit complaints', only: ['edit', 'update']),
            new Middleware('role_or_permission:delete complaints', only: ['destroy']),
            new Middleware('role_or_permission:assign complaints', only: ['store', 'update']),
            new Middleware('role_or_permission:escalate complaints', only: ['escalate']),
        ];
    }

    /**
     * Display paginated list of complaints with advanced filtering capabilities
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Build query with filters using Spatie QueryBuilder
        $complaints = QueryBuilder::for(Complaint::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('complaint_number'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('source'),
                AllowedFilter::partial('category'),
                AllowedFilter::exact('branch_id'),
                // Assigned to (supports special 'unassigned' token)
                AllowedFilter::callback('assigned_to', function ($query, $value) {
                    if ($value === 'unassigned') {
                        $query->whereNull('assigned_to');
                    } elseif (is_numeric($value)) {
                        $query->where('assigned_to', $value);
                    }
                }),
                AllowedFilter::exact('assigned_by'),
                AllowedFilter::exact('resolved_by'),
                AllowedFilter::exact('sla_breached'),
                AllowedFilter::exact('region_id'),
                AllowedFilter::exact('division_id'),
                AllowedFilter::partial('complainant_name'),
                AllowedFilter::partial('complainant_email'),
                // Escalated filter: 1 => has escalations, 0 => none
                AllowedFilter::callback('escalated', function ($query, $value) {
                    if ($value === '1') {
                        $query->whereHas('escalations');
                    } elseif ($value === '0') {
                        $query->whereDoesntHave('escalations');
                    }
                }),
                // Harassment only (category = Harassment)
                AllowedFilter::callback('harassment_only', function ($query, $value) {
                    if (in_array($value, ['1', 'true', 1, true], true)) {
                        $query->whereRaw('LOWER(category) = ?', ['harassment']);
                    }
                }),
                // Has witnesses
                AllowedFilter::callback('has_witnesses', function ($query, $value) {
                    if ($value === '1') {
                        $query->whereHas('witnesses');
                    } elseif ($value === '0') {
                        $query->whereDoesntHave('witnesses');
                    }
                }),
                // Harassment confidentiality flag
                AllowedFilter::callback('harassment_confidential', function ($query, $value) {
                    if ($value === '1') {
                        $query->where('harassment_confidential', true);
                    } elseif ($value === '0') {
                        $query->where(function ($q) {
                            $q->where('harassment_confidential', false)->orWhereNull('harassment_confidential');
                        });
                    }
                }),
                // Harassment sub category
                AllowedFilter::partial('harassment_sub_category'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('assigned_date_from', function ($query, $value) {
                    $query->whereDate('assigned_at', '>=', $value);
                }),
                AllowedFilter::callback('assigned_date_to', function ($query, $value) {
                    $query->whereDate('assigned_at', '<=', $value);
                }),
                AllowedFilter::callback('resolved_date_from', function ($query, $value) {
                    $query->whereDate('resolved_at', '>=', $value);
                }),
                AllowedFilter::callback('resolved_date_to', function ($query, $value) {
                    $query->whereDate('resolved_at', '<=', $value);
                })
            ])
            ->allowedSorts([
                'id',
                'complaint_number',
                'title',
                'status',
                'priority',
                'created_at',
                'updated_at',
                'assigned_at',
                'resolved_at',
                'expected_resolution_date'
            ])
            ->with([
                'branch',
                'assignedTo',
                'assignedBy',
                'resolvedBy',
                'histories' => function ($query) {
                    $query->latest()->limit(3);
                },
                'comments' => function ($query) {
                    $query->latest()->limit(2);
                },
                'attachments',
                'metrics'
            ])
            ->latest()
            ->paginate(15);

        // Get filter options for dropdowns
        $branches = Branch::orderBy('name')->get();
        $users = User::active()->orderBy('name')->get();
        $statusTypes = ComplaintStatusType::active()->orderBy('name')->get();
        // Added: categories, regions, divisions for dropdown filters in index view
        $categories = ComplaintCategory::orderBy('category_name')->get();
        $regions = \App\Models\Region::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('short_name')->orderBy('name')->get();

        // Get statistics for dashboard
        $statistics = [
            'total_complaints' => Complaint::count(),
            'open_complaints' => Complaint::whereIn('status', ['Open', 'In Progress', 'Pending'])->count(),
            'resolved_complaints' => Complaint::whereIn('status', ['Resolved', 'Closed'])->count(),
            'overdue_complaints' => Complaint::where('expected_resolution_date', '<', now())
                ->whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'high_priority' => Complaint::where('priority', 'High')->count(),
            'critical_priority' => Complaint::where('priority', 'Critical')->count(),
            'sla_breached' => Complaint::where('sla_breached', true)->count(),
        ];

        return view('complaints.index', compact(
            'complaints',
            'branches',
            'users',
            'statusTypes',
            'statistics',
            'categories',
            'regions',
            'divisions'
        ));
    }

    /**
     * Show form to create new complaint
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $categories = ComplaintCategory::topLevel()->orderBy('category_name')->get();
        $templates = ComplaintTemplate::orderBy('template_name')->get();
        $regions = \App\Models\Region::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('short_name')->orderBy('name')->get();

        return view('complaints.create', compact('branches', 'users', 'categories', 'templates', 'regions', 'divisions'));
    }

    /**
     * Store new complaint with comprehensive data handling
     * Uses transaction for data consistency
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreComplaintRequest $request)
    {
        // 
        $validated = $request->validated();

        // Start database transaction
        DB::beginTransaction();

        try {
            // Auto-generate complaint number
            $validated['complaint_number'] = generateUniqueId('complaint', 'complaints', 'complaint_number');

            // Set default values
            $validated['status'] = 'Open';
            $validated['assigned_by'] = auth()->id();

            if ($validated['assigned_to']) {
                $validated['assigned_at'] = now();
            }

            // Auto-set expected_resolution_date from SLA (priority mapping or category SLA hours) if not provided
            if (empty($validated['expected_resolution_date'])) {
                $priority = $validated['priority'] ?? 'Medium';
                $prioritySlaDays = ['Critical' => 1, 'High' => 3, 'Medium' => 7, 'Low' => 14];
                $targetDate = null;
                if (!empty($validated['category_id'])) {
                    $catForSla = ComplaintCategory::find($validated['category_id']);
                    if ($catForSla && $catForSla->sla_hours) {
                        $targetDate = now()->copy()->addHours($catForSla->sla_hours);
                    }
                }
                if (!$targetDate) {
                    $days = $prioritySlaDays[$priority] ?? 7;
                    $targetDate = now()->copy()->addDays($days);
                }
                $validated['expected_resolution_date'] = $targetDate;
            }

            // If category_id provided, also copy its name into legacy 'category' field for backward compatibility
            if (!empty($validated['category_id'])) {
                $cat = ComplaintCategory::find($validated['category_id']);
                if ($cat) {
                    $validated['category'] = $cat->category_name;
                }
            }

            // Create complaint record (includes region_id / division_id / branch_id which are nullable)
            $complaint = Complaint::create($validated);

            // Persist witnesses if harassment or grievance category and witnesses provided
            if (!empty($validated['category_id'])) {
                $cat = ComplaintCategory::find($validated['category_id']);
                if ($cat && (strcasecmp($cat->category_name, 'Harassment') === 0 || strcasecmp($cat->category_name, 'Grievance') === 0)) {
                    $groupedSets = [
                        $request->input('witnesses', []), // harassment witness fields
                        $request->input('grievance_witnesses', []) // grievance witness fields
                    ];
                    foreach ($groupedSets as $witnesses) {
                        if (is_array($witnesses)) {
                            foreach ($witnesses as $w) {
                                if (!empty($w['name'])) {
                                    ComplaintWitness::create([
                                        'complaint_id' => $complaint->id,
                                        'employee_number' => $w['employee_number'] ?? null,
                                        'name' => $w['name'],
                                        'phone' => $w['phone'] ?? null,
                                        'email' => $w['email'] ?? null,
                                        'statement' => $w['statement'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Create folder path for attachments
            $folderName = 'Complaints/' . $complaint->complaint_number;

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file->isValid()) {
                        $filePath = FileStorageHelper::storeSinglePrivateFile(
                            $file,
                            $folderName
                        );
                        // Defensive MIME truncation (column originally 50, migrated to 150)
                        $mime = (string) $file->getMimeType();
                        $mime = substr($mime, 0, 150); // ensure max length safety

                        ComplaintAttachment::create([
                            'complaint_id' => $complaint->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_size' => $file->getSize(),
                            'file_type' => $mime,
                        ]);
                    }
                }
            }

            // Create initial comment if provided
            if ($request->filled('comments')) {
                ComplaintComment::create([
                    'complaint_id' => $complaint->id,
                    'comment_text' => $request->comments,
                    'comment_type' => $request->comment_type ?? 'Internal',
                    'is_private' => $request->boolean('is_private', false),
                ]);
            }

            // (Removed) Previously duplicated complaint category row tied to complaint.

            // Create assignment record if assigned
            if ($complaint->assigned_to) {
                ComplaintAssignment::create([
                    'complaint_id' => $complaint->id,
                    'assigned_to' => $complaint->assigned_to,
                    'assigned_by' => $complaint->assigned_by,
                    'assignment_type' => 'Primary',
                    'assigned_at' => $complaint->assigned_at,
                    'reason' => 'Initial assignment during complaint creation',
                    'is_active' => true,
                ]);
            }

            // Add watchers if provided
            if ($request->filled('watchers')) {
                foreach ($request->watchers as $userId) {
                    ComplaintWatcher::create([
                        'complaint_id' => $complaint->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            // Create initial history record
            $statusType = ComplaintStatusType::where('code', 'CREATED')->first()
                ?? ComplaintStatusType::first();

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Created',
                    'old_value' => null,
                    'new_value' => 'Open',
                    'comments' => 'Complaint created successfully',
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Customer',
                ]);
            }

            // Create metrics record
            ComplaintMetric::create([
                'complaint_id' => $complaint->id,
                'time_to_first_response' => null,
                'time_to_resolution' => null,
                'reopened_count' => 0,
                'escalation_count' => 0,
                'assignment_count' => $complaint->assigned_to ? 1 : 0,
                'customer_satisfaction_score' => null,
            ]);

            // Commit transaction if everything successful
            DB::commit();

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', "Complaint '{$complaint->title}' created successfully with number: {$complaint->complaint_number}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error creating complaint', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['attachments'])
            ]);

            $detail = app()->environment('local') ? (' Details: ' . $e->getMessage()) : '';
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create complaint. Please try again.' . $detail);
        }
    }

    /**
     * Display the specified complaint with all related data
     * 
     * @param Complaint $complaint
     * @return \Illuminate\View\View
     */
    public function show(Complaint $complaint)
    {
        // Eager load all complaint relationships to avoid N+1 queries
        // This loads related data in a single query per relationship instead of multiple queries
        $complaint->load([
            // Basic user and branch relationships
            'branch',           // The branch where this complaint was logged
            'region',           // Region reference
            'division',         // Division reference
            'assignedTo',       // User currently assigned to handle this complaint
            'assignedBy',       // User who performed the assignment
            'resolvedBy',       // User who resolved this complaint

            // History tracking with nested relationships, ordered by most recent first
            'histories' => function ($query) {
                $query->with(['status', 'performedBy'])->latest();
            },

            // Comments with their creators, ordered by most recent first
            'comments' => function ($query) {
                $query->with('creator')->latest();
            },

            // File attachments with their uploaders, ordered by most recent first
            'attachments' => function ($query) {
                $query->with('creator')->latest();
            },

            // (Removed categories relationship after decoupling)

            // Assignment history with assignment details, ordered by most recent first
            'assignments' => function ($query) {
                $query->with(['assignedTo', 'assignedBy'])->latest();
            },

            // Escalation records with escalation path details, ordered by most recent first
            'escalations' => function ($query) {
                $query->with(['escalatedFrom', 'escalatedTo'])->latest();
            },

            // Users watching this complaint for notifications
            'watchers' => function ($query) {
                $query->with('user');
            },

            // Performance metrics and SLA tracking data
            'metrics',
            // Witnesses for harassment complaints
            'witnesses'
        ]);

        // Fetch additional reference data needed for complaint management forms
        // These provide dropdown options and reference data for various actions

        // All active users for assignment, escalation, and watcher operations
        $users = User::orderBy('name')->get();

        // Available status types for status change operations
        $statusTypes = ComplaintStatusType::orderBy('name')->get();

        // Email/response templates for quick communication
        $templates = ComplaintTemplate::orderBy('template_name')->get();

        // All branches / regions / divisions for transfer operations
        $branches = Branch::orderBy('name')->get();
        $regions = \App\Models\Region::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('short_name')->orderBy('name')->get();

        // Return the complaint detail view with all loaded data
        // The view will have access to the fully loaded complaint model and all reference data
        // Preprocess history display values (avoid Blade inline PHP that caused parse issues previously)
        if ($complaint->histories && $complaint->histories->count()) {
            // Build a simple id->name map once
            $userNameMap = User::pluck('name', 'id');
            foreach ($complaint->histories as $h) {
                $oldVal = $h->old_value;
                $newVal = $h->new_value;
                if ($h->action_type === 'Reassigned') {
                    if (is_numeric($oldVal)) {
                        $oldVal = $userNameMap[$oldVal] ?? ('User #' . $oldVal);
                    }
                    if (is_numeric($newVal)) {
                        $newVal = $userNameMap[$newVal] ?? ('User #' . $newVal);
                    }
                }
                // Attach non-persistent attributes for Blade rendering
                $h->display_old_value = $oldVal;
                $h->display_new_value = $newVal;
            }
        }
        return view('complaints.show', compact('complaint', 'users', 'statusTypes', 'templates', 'branches', 'regions', 'divisions'));
    }

    /**
     * Return a full JSON snapshot of a complaint with ALL related entities (assignments, escalations,
     * histories, comments, attachments, watchers, metrics, witnesses, status types mapping, category tree, templates)
     * for offline analysis / export. Intended for admin/internal use.
     */
    public function fullData(Complaint $complaint)
    {
        $complaint->load([
            'branch',
            'region',
            'division',
            'assignedTo',
            'assignedBy',
            'resolvedBy',
            'histories.status',
            'histories.performedBy',
            'comments.creator',
            'attachments.creator',
            'assignments.assignedTo',
            'assignments.assignedBy',
            'escalations.escalatedFrom',
            'escalations.escalatedTo',
            'watchers.user',
            'metrics',
            'witnesses'
        ]);

        // Compute handling duration (post first response) on-the-fly for export
        if ($complaint->metrics && $complaint->metrics->time_to_first_response !== null && $complaint->metrics->time_to_resolution !== null) {
            $handling = max(0, $complaint->metrics->time_to_resolution - $complaint->metrics->time_to_first_response);
            $complaint->metrics->setAttribute('handling_duration', $handling);
        }

        // Map any legacy numeric user IDs in reassignment histories to user names (server-side for PDF consistency)
        if ($complaint->histories && $complaint->histories->count()) {
            $numericUserIds = [];
            foreach ($complaint->histories as $h) {
                if ($h->action_type === 'Reassigned') {
                    if (is_numeric($h->old_value)) {
                        $numericUserIds[] = (int) $h->old_value;
                    }
                    if (is_numeric($h->new_value)) {
                        $numericUserIds[] = (int) $h->new_value;
                    }
                }
            }
            if ($numericUserIds) {
                $userNameMap = User::whereIn('id', array_unique($numericUserIds))->pluck('name', 'id');
                foreach ($complaint->histories as $h) {
                    if ($h->action_type === 'Reassigned') {
                        if (is_numeric($h->old_value)) {
                            $h->old_value = $userNameMap[(int) $h->old_value] ?? ('User #' . $h->old_value);
                        }
                        if (is_numeric($h->new_value)) {
                            $h->new_value = $userNameMap[(int) $h->new_value] ?? ('User #' . $h->new_value);
                        }
                    }
                }
            }
        }

        // Optionally include reference lookups
        $statusTypes = ComplaintStatusType::select('id', 'name', 'code')->get();
        $templates = ComplaintTemplate::select('id', 'template_name', 'template_subject', 'category_id')->get();
        $categories = ComplaintCategory::select('id', 'category_name', 'parent_category_id', 'sla_hours', 'default_priority', 'is_active')->get();

        return response()->json([
            'complaint' => $complaint,
            'status_types' => $statusTypes,
            'templates' => $templates,
            'categories' => $categories,
            'exported_at' => now()->toIso8601String(),
            'version' => '1.1'
        ]);
    }

    /**
     * Show form to edit existing complaint
     * 
     * @param Complaint $complaint
     * @return \Illuminate\View\View
     */
    public function edit(Complaint $complaint)
    {
        return redirect()
            ->route('complaints.show', $complaint)
            ->with('error', "Complaint '{$complaint->title}' is not allowed to be edit number: {$complaint->complaint_number}");
    }

    /**
     * Update existing complaint with comprehensive data handling
     * Uses transaction for data consistency
     * 
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        // Validate request data
        $validated = $request->validated();
        // Start database transaction
        DB::beginTransaction();

        try {
            $newAttachmentNames = [];
            // Store original values for history tracking
            $originalValues = $complaint->getOriginal();

            // Handle assignment changes (guard for partial updates)
            $assignmentChanged = false;
            if (array_key_exists('assigned_to', $validated) && $complaint->assigned_to != $validated['assigned_to']) {
                $assignmentChanged = true;
                if ($validated['assigned_to']) {
                    $validated['assigned_by'] = auth()->id();
                    $validated['assigned_at'] = now();
                } else {
                    $validated['assigned_by'] = null;
                    $validated['assigned_at'] = null;
                }
            }

            // Handle status changes (guard for partial updates)
            if (array_key_exists('status', $validated)) {
                if ($validated['status'] === 'Resolved' && $complaint->status !== 'Resolved') {
                    $validated['resolved_by'] = auth()->id();
                    $validated['resolved_at'] = now();
                } elseif ($validated['status'] === 'Closed' && $complaint->status !== 'Closed') {
                    $validated['closed_at'] = now();
                }
            }

            // Update complaint record
            $complaint->update($validated);

            // Create folder path for new attachments
            $folderName = 'Complaints/' . $complaint->complaint_number;

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file->isValid()) {
                        $filePath = FileStorageHelper::storeSinglePrivateFile($file, $folderName);
                        $mime = substr((string) $file->getMimeType(), 0, 150);
                        $attachment = ComplaintAttachment::create([
                            'complaint_id' => $complaint->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_size' => $file->getSize(),
                            'file_type' => $mime,
                        ]);
                        $newAttachmentNames[] = $attachment->file_name;
                    }
                }
            }

            // Create new assignment record if assignment changed
            if ($assignmentChanged && $validated['assigned_to']) {
                // Deactivate previous assignments
                ComplaintAssignment::where('complaint_id', $complaint->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'unassigned_at' => now()]);

                // Create new assignment
                ComplaintAssignment::create([
                    'complaint_id' => $complaint->id,
                    'assigned_to' => $validated['assigned_to'],
                    'assigned_by' => auth()->id(),
                    'assignment_type' => 'Primary',
                    'assigned_at' => now(),
                    'reason' => 'Assignment changed during complaint update',
                    'is_active' => true,
                ]);

                // Update metrics
                $complaint->metrics()->increment('assignment_count');
            }

            // Create history records for significant changes
            $this->createHistoryRecords($complaint, $originalValues, $validated);

            // Update metrics based on status changes (pass arrays but function is defensive)
            $this->updateComplaintMetrics($complaint, $originalValues, $validated);

            // Commit transaction if update successful
            DB::commit();

            // Add history entry for newly attached files (single consolidated record)
            if (!empty($newAttachmentNames)) {
                $statusType = ComplaintStatusType::first();
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'File Attached',
                    'old_value' => null,
                    'new_value' => count($newAttachmentNames) . ' file(s)',
                    'comments' => 'Attached: ' . implode(', ', array_slice($newAttachmentNames, 0, 5)) . (count($newAttachmentNames) > 5 ? '…' : ''),
                    'status_id' => $statusType?->id ?? 1,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }

            $message = !empty($newAttachmentNames)
                ? (count($newAttachmentNames) . ' attachment(s) uploaded successfully.')
                : "Complaint '{$complaint->title}' updated successfully.";

            return redirect()->route('complaints.show', $complaint)->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error updating complaint', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update complaint. Please try again.');
        }
    }

    /**
     * Add new attachments only (without other field validation)
     *
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addAttachments(Request $request, Complaint $complaint)
    {
        $request->validate([
            'attachments' => 'required|array|max:10',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $folderName = 'Complaints/' . $complaint->complaint_number;
            $newAttachmentNames = [];

            foreach ($request->file('attachments', []) as $file) {
                if ($file && $file->isValid()) {
                    $filePath = FileStorageHelper::storeSinglePrivateFile($file, $folderName);
                    $mime = substr((string) $file->getMimeType(), 0, 150);
                    $attachment = ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_type' => $mime,
                    ]);
                    $newAttachmentNames[] = $attachment->file_name;
                }
            }

            if (!empty($newAttachmentNames)) {
                $statusType = ComplaintStatusType::first();
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'File Attached',
                    'old_value' => null,
                    'new_value' => count($newAttachmentNames) . ' file(s)',
                    'comments' => 'Attached: ' . implode(', ', array_slice($newAttachmentNames, 0, 5)) . (count($newAttachmentNames) > 5 ? '…' : ''),
                    'status_id' => $statusType?->id ?? 1,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }

            DB::commit();

            return redirect()->route('complaints.show', $complaint)
                ->with('success', count($newAttachmentNames) . ' attachment(s) uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attachment upload failed', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to upload attachments.');
        }
    }

    /**
     * Update only the status of a complaint (used by operations tab)
     *
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed,Reopened',
            'status_change_reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $complaint->status;

            if ($oldStatus === $validated['status']) {
                return redirect()->route('complaints.show', $complaint)->with('info', 'No status change detected.');
            }

            $updateData = ['status' => $validated['status']];

            if ($validated['status'] === 'Resolved') {
                $updateData['resolved_by'] = auth()->id();
                $updateData['resolved_at'] = now();
            } elseif ($validated['status'] === 'Closed') {
                $updateData['closed_at'] = now();
            }

            $complaint->update($updateData);

            // Create history record
            $statusType = ComplaintStatusType::first();
            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Status Changed',
                    'old_value' => $oldStatus,
                    'new_value' => $validated['status'],
                    'comments' => $validated['status_change_reason'] ?? 'Status updated via operations',
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }

            // Update metrics if necessary
            $this->updateComplaintMetrics($complaint, ['status' => $oldStatus], $updateData);

            DB::commit();

            return redirect()->route('complaints.show', $complaint)->with('success', 'Status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating complaint status', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    /**
     * Remove the specified complaint from storage (soft delete)
     * 
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Complaint $complaint)
    {

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('error', "Complaint '{$complaint->title}' cannot be deleted. Complaint number: {$complaint->complaint_number} is protected from deletion.");
    }

    /**
     * Add comment to complaint
     * 
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addComment(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'comment_text' => 'required|string',
            'comment_type' => 'required|in:Internal,Customer,System',
            'is_private' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            ComplaintComment::create([
                'complaint_id' => $complaint->id,
                'comment_text' => $validated['comment_text'],
                'comment_type' => $validated['comment_type'],
                'is_private' => $request->boolean('is_private', false),
            ]);

            // Create history record
            $statusType = ComplaintStatusType::where('code', 'COMMENT')->first()
                ?? ComplaintStatusType::first();

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Comment Added',
                    'old_value' => null,
                    'new_value' => $validated['comment_type'] . ' comment',
                    'comments' => 'Comment added: ' . substr($validated['comment_text'], 0, 100),
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Comment added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add comment. Please try again.');
        }
    }

    /**
     * Escalate complaint to higher authority
     * 
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function escalate(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'escalated_to' => 'required|exists:users,id',
            'escalation_reason' => 'required|string',
            'escalation_level' => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();

        try {
            // Ensure metrics record exists (in case it wasn't created at complaint creation)
            $metrics = $complaint->metrics()->first();
            if (!$metrics) {
                $metrics = $complaint->metrics()->create([
                    'time_to_first_response' => 0,
                    'time_to_resolution' => 0,
                    'reopened_count' => 0,
                    'escalation_count' => 0,
                    'assignment_count' => 0,
                ]);
            }

            // Create escalation record
            ComplaintEscalation::create([
                'complaint_id' => $complaint->id,
                'escalated_from' => auth()->id(),
                'escalated_to' => $validated['escalated_to'],
                'escalation_level' => $validated['escalation_level'],
                'escalated_at' => now(),
                'escalation_reason' => $validated['escalation_reason'],
            ]);

            // Update complaint assignment
            $complaint->update([
                'assigned_to' => $validated['escalated_to'],
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);

            // Update metrics (safe increment)
            $complaint->metrics()->increment('escalation_count');

            // Create history record
            $statusType = ComplaintStatusType::where('code', 'ESCALATED')->first();
            if (!$statusType) {
                // Create a dummy ESCALATED status type if it doesn't exist
                $statusType = ComplaintStatusType::firstOrCreate(
                    ['code' => 'ESCALATED'],
                    [
                        'name' => 'Escalated',
                        'description' => 'Auto generated status for escalated complaints',
                        'is_active' => true,
                    ]
                );
            }

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Escalated',
                    'old_value' => 'Level ' . ($validated['escalation_level'] - 1),
                    'new_value' => 'Level ' . $validated['escalation_level'],
                    'comments' => $validated['escalation_reason'],
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Complaint escalated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Escalation failed', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 1000),
            ]);
            $msg = app()->environment('local') ? ('Failed to escalate: ' . $e->getMessage()) : 'Failed to escalate complaint. Please try again.';
            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Add/Remove watchers for complaint
     * 
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateWatchers(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'watchers' => 'nullable|array',
            'watchers.*' => 'exists:users,id'
        ]);

        DB::beginTransaction();

        try {
            // Remove existing watchers
            $complaint->watchers()->delete();

            // Add new watchers
            if (!empty($validated['watchers'])) {
                foreach ($validated['watchers'] as $userId) {
                    ComplaintWatcher::create([
                        'complaint_id' => $complaint->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Watchers updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update watchers. Please try again.');
        }
    }

    /**
     * Download attachment file
     * 
     * @param ComplaintAttachment $attachment
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadAttachment(ComplaintAttachment $attachment)
    {
        try {
            if (!Storage::disk('local')->exists($attachment->file_path)) {
                return redirect()->back()
                    ->with('error', 'File not found.');
            }

            return Storage::disk('local')->download(
                $attachment->file_path,
                $attachment->file_name
            );

        } catch (\Exception $e) {
            Log::error('Error downloading attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to download file. Please try again.');
        }
    }

    /**
     * Delete attachment file
     * 
     * @param ComplaintAttachment $attachment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAttachment(ComplaintAttachment $attachment)
    {
        DB::beginTransaction();

        try {
            // Delete physical file
            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }

            // Delete database record
            $attachment->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Attachment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete attachment. Please try again.');
        }
    }

    /**
     * Create history records for complaint changes
     * 
     * @param Complaint $complaint
     * @param array $originalValues
     * @param array $newValues
     * @return void
     */
    private function createHistoryRecords(Complaint $complaint, array $originalValues, array $newValues)
    {
        $statusType = ComplaintStatusType::first();
        $trackableFields = [
            'status' => 'Status Changed',
            'priority' => 'Priority Changed',
            'assigned_to' => 'Reassigned',
            'category' => 'Category Changed',
            'branch_id' => 'Branch Transfer',
            'region_id' => 'Region Transfer',
            'division_id' => 'Division Transfer'
        ];

        foreach ($trackableFields as $field => $actionType) {
            // Only create a history record when the incoming update explicitly contains the field
            // and the value actually changed compared to the original.
            $oldVal = $originalValues[$field] ?? null;
            if (array_key_exists($field, $newValues) && $oldVal != $newValues[$field]) {
                $newVal = $newValues[$field];

                // For foreign keys, fetch readable names
                if (in_array($field, ['branch_id', 'region_id', 'division_id'])) {
                    $oldVal = match ($field) {
                        'branch_id' => $oldVal ? optional(\App\Models\Branch::find($oldVal))->name : 'None',
                        'region_id' => $oldVal ? optional(\App\Models\Region::find($oldVal))->name : 'None',
                        'division_id' => $oldVal ? optional(\App\Models\Division::find($oldVal))->short_name ?? optional(\App\Models\Division::find($oldVal))->name : 'None',
                        default => $oldVal,
                    };
                    $newVal = match ($field) {
                        'branch_id' => $newVal ? optional(\App\Models\Branch::find($newVal))->name : 'None',
                        'region_id' => $newVal ? optional(\App\Models\Region::find($newVal))->name : 'None',
                        'division_id' => $newVal ? (optional(\App\Models\Division::find($newVal))->short_name ?? optional(\App\Models\Division::find($newVal))->name) : 'None',
                        default => $newVal,
                    };
                }

                // For user assignment changes convert IDs to human readable names (avoid leaking raw IDs in history)
                if ($field === 'assigned_to') {
                    $oldVal = $oldVal ? (User::find($oldVal)?->name ?? "User #{$oldVal}") : 'Unassigned';
                    $newVal = $newVal ? (User::find($newVal)?->name ?? "User #{$newVal}") : 'Unassigned';
                }
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => $actionType,
                    'old_value' => $oldVal ?? 'None',
                    'new_value' => $newVal ?? 'None',
                    'comments' => "{$field} changed from '" . ($oldVal ?? 'None') . "' to '" . ($newVal ?? 'None') . "'",
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Internal',
                ]);
            }
        }
    }

    /**
     * Update complaint metrics based on changes
     * 
     * @param Complaint $complaint
     * @param array $originalValues
     * @param array $newValues
     * @return void
     */
    private function updateComplaintMetrics(Complaint $complaint, array $originalValues, array $newValues)
    {
        $metrics = $complaint->metrics;
        if (!$metrics) {
            return;
        }

        // Defensive retrieval of status values
        $origStatus = $originalValues['status'] ?? null;
        $newStatus = array_key_exists('status', $newValues) ? $newValues['status'] : $origStatus;

        // Calculate time to first response (if this is the first status change)
        if (
            !$metrics->time_to_first_response &&
            $origStatus === 'Open' &&
            $newStatus !== 'Open'
        ) {
            $firstResponseAt = now();
            $timeToResponse = $complaint->created_at->diffInMinutes($firstResponseAt);
            $metrics->update([
                'time_to_first_response' => $timeToResponse,
                'first_response_at' => $firstResponseAt,
            ]);
        }

        // Lazy backfill first_response_at if historical data exists without timestamp
        if ($metrics->time_to_first_response && !$metrics->first_response_at) {
            $backfill = $complaint->created_at->copy()->addMinutes($metrics->time_to_first_response);
            $metrics->update(['first_response_at' => $backfill]);
        }

        // Calculate or refresh time to resolution when entering Resolved/Closed
        if (in_array($newStatus, ['Resolved', 'Closed'])) {
            $resolvedAt = $complaint->resolved_at ?? now();
            $ttr = $complaint->created_at->diffInMinutes($resolvedAt);
            // Optionally compute handling duration separately if a definition change is desired.
            if (!$metrics->time_to_resolution || $metrics->time_to_resolution != $ttr) {
                $metrics->update(['time_to_resolution' => $ttr]);
            }
        }

        // If reopened after resolution, clear resolution time so it can be recalculated
        if ($newStatus === 'Reopened' && in_array($origStatus, ['Resolved', 'Closed'])) {
            if ($metrics->time_to_resolution) {
                $metrics->update(['time_to_resolution' => null]);
            }
        }

        // Track reopened count
        if ($origStatus === 'Closed' && $newStatus === 'Reopened') {
            $metrics->increment('reopened_count');
        }
    }

    /**
     * Bulk update complaints status
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:complaints,id',
            'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed',
            'bulk_comment' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $updatedCount = 0;
            $statusType = ComplaintStatusType::first();

            foreach ($validated['complaint_ids'] as $complaintId) {
                $complaint = Complaint::find($complaintId);
                if ($complaint && $complaint->status !== $validated['status']) {
                    $oldStatus = $complaint->status;

                    // Update complaint status
                    $updateData = ['status' => $validated['status']];

                    if ($validated['status'] === 'Resolved') {
                        $updateData['resolved_by'] = auth()->id();
                        $updateData['resolved_at'] = now();
                    } elseif ($validated['status'] === 'Closed') {
                        $updateData['closed_at'] = now();
                    }

                    $complaint->update($updateData);

                    // Create history record
                    if ($statusType) {
                        ComplaintHistory::create([
                            'complaint_id' => $complaint->id,
                            'action_type' => 'Status Changed',
                            'old_value' => $oldStatus,
                            'new_value' => $validated['status'],
                            'comments' => 'Bulk status update: ' . ($validated['bulk_comment'] ?? 'No comment'),
                            'status_id' => $statusType->id,
                            'performed_by' => auth()->id(),
                            'performed_at' => now(),
                            'complaint_type' => 'Internal',
                        ]);
                    }

                    $updatedCount++;
                }
            }

            DB::commit();

            return redirect()
                ->route('complaints.index')
                ->with('success', "Successfully updated {$updatedCount} complaints.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk status update', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'complaint_ids' => $validated['complaint_ids']
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update complaints. Please try again.');
        }
    }

    /**
     * Bulk assign complaints to user
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:complaints,id',
            'assigned_to' => 'required|exists:users,id',
            'assignment_reason' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $assignedCount = 0;
            $statusType = ComplaintStatusType::first();

            foreach ($validated['complaint_ids'] as $complaintId) {
                $complaint = Complaint::find($complaintId);
                if ($complaint) {
                    $oldAssignee = $complaint->assigned_to;

                    // Update complaint assignment
                    $complaint->update([
                        'assigned_to' => $validated['assigned_to'],
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);

                    // Deactivate previous assignments
                    ComplaintAssignment::where('complaint_id', $complaint->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false, 'unassigned_at' => now()]);

                    // Create new assignment record
                    ComplaintAssignment::create([
                        'complaint_id' => $complaint->id,
                        'assigned_to' => $validated['assigned_to'],
                        'assigned_by' => auth()->id(),
                        'assignment_type' => 'Primary',
                        'assigned_at' => now(),
                        'reason' => 'Bulk assignment: ' . ($validated['assignment_reason'] ?? 'No reason provided'),
                        'is_active' => true,
                    ]);

                    // Update metrics
                    $complaint->metrics()->increment('assignment_count');

                    // Create history record
                    if ($statusType) {
                        $oldAssigneeName = $oldAssignee ? User::find($oldAssignee)->name : 'Unassigned';
                        $newAssigneeName = User::find($validated['assigned_to'])->name;

                        ComplaintHistory::create([
                            'complaint_id' => $complaint->id,
                            'action_type' => 'Reassigned',
                            'old_value' => $oldAssigneeName,
                            'new_value' => $newAssigneeName,
                            'comments' => 'Bulk assignment: ' . ($validated['assignment_reason'] ?? 'No reason provided'),
                            'status_id' => $statusType->id,
                            'performed_by' => auth()->id(),
                            'performed_at' => now(),
                            'complaint_type' => 'Internal',
                        ]);
                    }

                    $assignedCount++;
                }
            }

            DB::commit();

            return redirect()
                ->route('complaints.index')
                ->with('success', "Successfully assigned {$assignedCount} complaints.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk assignment', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'complaint_ids' => $validated['complaint_ids']
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign complaints. Please try again.');
        }
    }

    /**
     * Export complaints to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        try {
            $query = QueryBuilder::for(Complaint::class)
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('priority'),
                    AllowedFilter::exact('branch_id'),
                    AllowedFilter::exact('assigned_to'),
                    AllowedFilter::callback('date_from', function ($query, $value) {
                        $query->whereDate('created_at', '>=', $value);
                    }),
                    AllowedFilter::callback('date_to', function ($query, $value) {
                        $query->whereDate('created_at', '<=', $value);
                    }),
                ])
                ->with(['branch', 'assignedTo', 'resolvedBy']);

            $complaints = $query->get();

            $filename = 'complaints_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($complaints) {
                $handle = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($handle, [
                    'Complaint Number',
                    'Title',
                    'Description',
                    'Category',
                    'Priority',
                    'Status',
                    'Source',
                    'Complainant Name',
                    'Complainant Email',
                    'Complainant Phone',
                    'Branch',
                    'Assigned To',
                    'Resolved By',
                    'Created At',
                    'Assigned At',
                    'Resolved At',
                    'Expected Resolution Date',
                    'SLA Breached'
                ]);

                // CSV Data
                foreach ($complaints as $complaint) {
                    fputcsv($handle, [
                        $complaint->complaint_number,
                        $complaint->title,
                        $complaint->description,
                        $complaint->category,
                        $complaint->priority,
                        $complaint->status,
                        $complaint->source,
                        $complaint->complainant_name,
                        $complaint->complainant_email,
                        $complaint->complainant_phone,
                        $complaint->branch ? $complaint->branch->name : '',
                        $complaint->assignedTo ? $complaint->assignedTo->name : '',
                        $complaint->resolvedBy ? $complaint->resolvedBy->name : '',
                        $complaint->created_at ? $complaint->created_at->format('Y-m-d H:i:s') : '',
                        $complaint->assigned_at ? $complaint->assigned_at->format('Y-m-d H:i:s') : '',
                        $complaint->resolved_at ? $complaint->resolved_at->format('Y-m-d H:i:s') : '',
                        $complaint->expected_resolution_date ? $complaint->expected_resolution_date->format('Y-m-d H:i:s') : '',
                        $complaint->sla_breached ? 'Yes' : 'No'
                    ]);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting complaints', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            $filename = 'complaints_export_error_' . now()->format('Y_m_d_H_i_s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            return response()->stream(function () use ($e) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Export Failed']);
                fputcsv($handle, ['Message', app()->environment('local') ? $e->getMessage() : 'An error occurred generating the export']);
                fclose($handle);
            }, 200, $headers);
        }
    }

    /**
     * Generate complaint analytics/dashboard data
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function analytics(Request $request)
    {
        try {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request);

            // Build a base filtered query (re-usable clone per aggregation)
            $baseQuery = Complaint::query();
            $this->applyAnalyticsFilters($baseQuery, $request, $dateFrom, $dateTo);

            $totalComplaints = (clone $baseQuery)->count();
            $resolvedComplaints = (clone $baseQuery)->whereIn('status', ['Resolved', 'Closed'])->count();
            $openComplaints = (clone $baseQuery)->whereIn('status', ['Open', 'In Progress', 'Pending'])->count();
            $overdueComplaints = (clone $baseQuery)
                ->where('expected_resolution_date', '<', now())
                ->whereNotIn('status', ['Resolved', 'Closed'])
                ->count();

            $unassignedComplaints = (clone $baseQuery)->whereNull('assigned_to')->count();
            $escalatedComplaints = (clone $baseQuery)->whereHas('escalations')->count();
            $harassmentComplaints = (clone $baseQuery)->whereRaw('LOWER(category) = ?', ['harassment'])->count();
            $harassmentConfidential = (clone $baseQuery)->where('harassment_confidential', true)->count();
            $withWitnesses = (clone $baseQuery)->whereHas('witnesses')->count();
            $slaBreached = (clone $baseQuery)->where('sla_breached', true)->count();

            $statusDistribution = (clone $baseQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            $priorityDistribution = (clone $baseQuery)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get();

            $sourceDistribution = (clone $baseQuery)
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->get();

            $branchPerformance = (clone $baseQuery)
                ->join('branches', 'complaints.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name as branch_name, COUNT(*) as total_complaints,
                    SUM(CASE WHEN complaints.status IN ("Resolved", "Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('branches.id', 'branches.name')
                ->get();

            $userPerformance = (clone $baseQuery)
                ->join('users', 'complaints.assigned_to', '=', 'users.id')
                ->selectRaw('users.name as user_name, COUNT(*) as assigned_complaints,
                    SUM(CASE WHEN complaints.status IN ("Resolved", "Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('users.id', 'users.name')
                ->get();

            $avgResolutionTime = ComplaintMetric::join('complaints', 'complaint_metrics.complaint_id', '=', 'complaints.id')
                ->whereBetween('complaints.created_at', [$dateFrom, $dateTo])
                ->whereNotNull('time_to_resolution')
                ->when($request->filled('filter.category'), function ($q) use ($request) {
                    $q->where('complaints.category', 'like', '%' . $request->input('filter.category') . '%');
                })
                ->avg('time_to_resolution');

            $slaCompliance = $totalComplaints > 0 ? (($totalComplaints - $slaBreached) / $totalComplaints) * 100 : 100;

            // Monthly trend constrained within selected date range (group by month)
            $monthlyTrend = Complaint::query()
                ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('created_at', '<=', $dateTo))
                ->tap(function ($q) use ($request) {
                    // Apply the same non-date filters for consistency
                    $this->applyAnalyticsFilters($q, $request, null, null, true); // skip date in second pass
                })
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Provide initial payload to Blade (JS will enhance later)
            $initialPayload = [
                'total' => $totalComplaints,
                'resolved' => $resolvedComplaints,
                'open' => $openComplaints,
                'overdue' => $overdueComplaints,
                'unassigned' => $unassignedComplaints,
                'escalated' => $escalatedComplaints,
                'harassment' => $harassmentComplaints,
                'harassment_confidential' => $harassmentConfidential,
                'with_witnesses' => $withWitnesses,
                'sla_breached' => $slaBreached,
                'avg_resolution_time_minutes' => $avgResolutionTime,
                'sla_compliance' => $slaCompliance,
            ];

            // Extended distributions & rankings
            $categoryDistribution = (clone $baseQuery)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get();
            $harassmentSubCategoryDistribution = (clone $baseQuery)
                ->whereRaw('LOWER(category) = ?', ['harassment'])
                ->whereNotNull('harassment_sub_category')
                ->selectRaw('harassment_sub_category as sub_category, COUNT(*) as count')
                ->groupBy('harassment_sub_category')
                ->orderByDesc('count')
                ->get();
            $topResolvers = (clone $baseQuery)
                ->whereIn('status', ["Resolved", "Closed"])
                ->whereNotNull('resolved_by')
                ->join('users as resolver', 'complaints.resolved_by', '=', 'resolver.id')
                ->selectRaw('resolver.name as user_name, COUNT(*) as resolved_count')
                ->groupBy('resolver.id', 'resolver.name')
                ->orderByDesc('resolved_count')
                ->limit(5)
                ->get();
            $topWatchers = (clone $baseQuery)
                ->join('complaint_watchers', 'complaint_watchers.complaint_id', '=', 'complaints.id')
                ->join('users as watcher', 'complaint_watchers.user_id', '=', 'watcher.id')
                ->selectRaw('watcher.name as user_name, COUNT(DISTINCT complaints.id) as watched_complaints')
                ->groupBy('watcher.id', 'watcher.name')
                ->orderByDesc('watched_complaints')
                ->limit(5)
                ->get();
            $categoryResolutionRates = (clone $baseQuery)
                ->selectRaw('category, COUNT(*) as total, SUM(CASE WHEN status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved')
                ->groupBy('category')
                ->get()
                ->map(function ($r) {
                    $r->resolution_rate = $r->total > 0 ? round(($r->resolved / $r->total) * 100, 1) : 0;
                    return $r;
                });

            return view('complaints.analytics', [
                'totalComplaints' => $totalComplaints,
                'resolvedComplaints' => $resolvedComplaints,
                'openComplaints' => $openComplaints,
                'overdueComplaints' => $overdueComplaints,
                'statusDistribution' => $statusDistribution,
                'priorityDistribution' => $priorityDistribution,
                'sourceDistribution' => $sourceDistribution,
                'branchPerformance' => $branchPerformance,
                'userPerformance' => $userPerformance,
                'avgResolutionTime' => $avgResolutionTime,
                'slaCompliance' => $slaCompliance,
                'monthlyTrend' => $monthlyTrend,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'initialPayload' => $initialPayload,
                'categoryDistribution' => $categoryDistribution,
                'harassmentSubCategoryDistribution' => $harassmentSubCategoryDistribution,
                'topResolvers' => $topResolvers,
                'topWatchers' => $topWatchers,
                'categoryResolutionRates' => $categoryResolutionRates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating complaint analytics', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate analytics. Please try again.');
        }
    }

    /**
     * JSON endpoint for dynamic analytics dashboard updates
     */
    public function analyticsData(Request $request)
    {
        try {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request);
            $baseQuery = Complaint::query();
            $this->applyAnalyticsFilters($baseQuery, $request, $dateFrom, $dateTo);

            $total = (clone $baseQuery)->count();
            $resolved = (clone $baseQuery)->whereIn('status', ['Resolved', 'Closed'])->count();
            $open = (clone $baseQuery)->whereIn('status', ['Open', 'In Progress', 'Pending'])->count();
            $overdue = (clone $baseQuery)->where('expected_resolution_date', '<', now())
                ->whereNotIn('status', ['Resolved', 'Closed'])->count();
            $unassigned = (clone $baseQuery)->whereNull('assigned_to')->count();
            $escalated = (clone $baseQuery)->whereHas('escalations')->count();
            $harassment = (clone $baseQuery)->whereRaw('LOWER(category) = ?', ['harassment'])->count();
            $harassment_confidential = (clone $baseQuery)->where('harassment_confidential', true)->count();
            $with_witnesses = (clone $baseQuery)->whereHas('witnesses')->count();
            $sla_breached = (clone $baseQuery)->where('sla_breached', true)->count();

            $avgResolutionTime = ComplaintMetric::join('complaints', 'complaint_metrics.complaint_id', '=', 'complaints.id')
                ->whereBetween('complaints.created_at', [$dateFrom, $dateTo])
                ->whereNotNull('time_to_resolution')
                ->avg('time_to_resolution');

            $sla_compliance = $total > 0 ? (($total - $sla_breached) / $total) * 100 : 100;

            $statusDistribution = (clone $baseQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')->get();
            $priorityDistribution = (clone $baseQuery)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')->get();
            $sourceDistribution = (clone $baseQuery)
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')->get();

            $monthlyTrend = Complaint::query()
                ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('created_at', '<=', $dateTo))
                ->tap(function ($q) use ($request) {
                    $this->applyAnalyticsFilters($q, $request, null, null, true);
                })
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(CASE WHEN status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved_count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Extended datasets for JSON
            $categoryDistribution = (clone $baseQuery)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get();
            $harassmentSubCategoryDistribution = (clone $baseQuery)
                ->whereRaw('LOWER(category) = ?', ['harassment'])
                ->whereNotNull('harassment_sub_category')
                ->selectRaw('harassment_sub_category as sub_category, COUNT(*) as count')
                ->groupBy('harassment_sub_category')
                ->orderByDesc('count')
                ->get();
            $topResolvers = (clone $baseQuery)
                ->whereIn('status', ["Resolved", "Closed"])
                ->whereNotNull('resolved_by')
                ->join('users as resolver', 'complaints.resolved_by', '=', 'resolver.id')
                ->selectRaw('resolver.name as user_name, COUNT(*) as resolved_count')
                ->groupBy('resolver.id', 'resolver.name')
                ->orderByDesc('resolved_count')
                ->limit(5)
                ->get();
            $topWatchers = (clone $baseQuery)
                ->join('complaint_watchers', 'complaint_watchers.complaint_id', '=', 'complaints.id')
                ->join('users as watcher', 'complaint_watchers.user_id', '=', 'watcher.id')
                ->selectRaw('watcher.name as user_name, COUNT(DISTINCT complaints.id) as watched_complaints')
                ->groupBy('watcher.id', 'watcher.name')
                ->orderByDesc('watched_complaints')
                ->limit(5)
                ->get();
            $categoryResolutionRates = (clone $baseQuery)
                ->selectRaw('category, COUNT(*) as total, SUM(CASE WHEN status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved')
                ->groupBy('category')
                ->get()
                ->map(function ($r) {
                    $r->resolution_rate = $r->total > 0 ? round(($r->resolved / $r->total) * 100, 1) : 0;
                    return $r;
                });

            $branchPerformance = (clone $baseQuery)
                ->leftJoin('branches', 'complaints.branch_id', '=', 'branches.id')
                ->selectRaw('COALESCE(branches.name, "(None)") as branch_name, COUNT(*) as total_complaints, SUM(CASE WHEN complaints.status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('branch_name')
                ->orderByDesc('total_complaints')
                ->limit(10)
                ->get();
            $userPerformance = (clone $baseQuery)
                ->leftJoin('users', 'complaints.assigned_to', '=', 'users.id')
                ->selectRaw('COALESCE(users.name, "(Unassigned)") as user_name, COUNT(*) as assigned_complaints, SUM(CASE WHEN complaints.status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('user_name')
                ->orderByDesc('assigned_complaints')
                ->limit(10)
                ->get();

            return response()->json([
                'metrics' => compact(
                    'total',
                    'resolved',
                    'open',
                    'overdue',
                    'unassigned',
                    'escalated',
                    'harassment',
                    'harassment_confidential',
                    'with_witnesses',
                    'sla_breached',
                    'avgResolutionTime',
                    'sla_compliance'
                ),
                'statusDistribution' => $statusDistribution,
                'priorityDistribution' => $priorityDistribution,
                'sourceDistribution' => $sourceDistribution,
                'monthlyTrend' => $monthlyTrend,
                'categoryDistribution' => $categoryDistribution,
                'harassmentSubCategoryDistribution' => $harassmentSubCategoryDistribution,
                'topResolvers' => $topResolvers,
                'topWatchers' => $topWatchers,
                'categoryResolutionRates' => $categoryResolutionRates,
                'branchPerformance' => $branchPerformance,
                'userPerformance' => $userPerformance,
            ]);
        } catch (\Exception $e) {
            Log::error('analyticsData failure', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Analytics generation failed'], 500);
        }
    }

    /**
     * Apply analytics filters to a query (shared by view + JSON endpoint)
     * $dateFrom/$dateTo can be null to skip date filters (when building monthly trend second pass)
     */
    protected function applyAnalyticsFilters($query, Request $request, $dateFrom = null, $dateTo = null, $skipDate = false)
    {
        if (!$skipDate && $dateFrom && $dateTo) {
            $query->whereBetween('complaints.created_at', [$dateFrom, $dateTo]);
        }
        $filter = $request->input('filter', []);
        if (!is_array($filter))
            return;

        $like = function ($value) {
            return '%' . $value . '%';
        };

        foreach ($filter as $key => $value) {
            if ($value === '' || $value === null)
                continue;
            switch ($key) {
                case 'id':
                    $query->where('id', $value);
                    break;
                case 'complaint_number':
                    $query->where('complaint_number', 'like', $like($value));
                    break;
                case 'title':
                    $query->where('title', 'like', $like($value));
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'priority':
                    $query->where('priority', $value);
                    break;
                case 'source':
                    $query->where('source', $value);
                    break;
                case 'category':
                    $query->where('category', 'like', $like($value));
                    break;
                case 'branch_id':
                    $query->where('branch_id', $value);
                    break;
                case 'assigned_to':
                    if ($value === 'unassigned') {
                        $query->whereNull('assigned_to');
                    } elseif (is_numeric($value)) {
                        $query->where('assigned_to', $value);
                    }
                    break;
                case 'assigned_by':
                    $query->where('assigned_by', $value);
                    break;
                case 'resolved_by':
                    $query->where('resolved_by', $value);
                    break;
                case 'sla_breached':
                    $query->where('sla_breached', (bool) $value);
                    break;
                case 'region_id':
                    $query->where('region_id', $value);
                    break;
                case 'division_id':
                    $query->where('division_id', $value);
                    break;
                case 'complainant_name':
                    $query->where('complainant_name', 'like', $like($value));
                    break;
                case 'complainant_email':
                    $query->where('complainant_email', 'like', $like($value));
                    break;
                case 'escalated':
                    if ($value === '1') {
                        $query->whereHas('escalations');
                    } elseif ($value === '0') {
                        $query->whereDoesntHave('escalations');
                    }
                    break;
                case 'harassment_only':
                    if (in_array($value, ['1', 'true', 1, true], true)) {
                        $query->whereRaw('LOWER(category) = ?', ['harassment']);
                    }
                    break;
                case 'has_witnesses':
                    if ($value === '1') {
                        $query->whereHas('witnesses');
                    } elseif ($value === '0') {
                        $query->whereDoesntHave('witnesses');
                    }
                    break;
                case 'harassment_confidential':
                    if ($value === '1') {
                        $query->where('harassment_confidential', true);
                    } elseif ($value === '0') {
                        $query->where(function ($q) {
                            $q->where('harassment_confidential', false)->orWhereNull('harassment_confidential');
                        });
                    }
                    break;
                case 'harassment_sub_category':
                    $query->where('harassment_sub_category', 'like', $like($value));
                    break;
                case 'date_from':
                    $query->whereDate('complaints.created_at', '>=', $value);
                    break; // individual overrides (if provided outside main range)
                case 'date_to':
                    $query->whereDate('complaints.created_at', '<=', $value);
                    break;
                case 'assigned_date_from':
                    $query->whereDate('complaints.assigned_at', '>=', $value);
                    break;
                case 'assigned_date_to':
                    $query->whereDate('complaints.assigned_at', '<=', $value);
                    break;
                case 'resolved_date_from':
                    $query->whereDate('complaints.resolved_at', '>=', $value);
                    break;
                case 'resolved_date_to':
                    $query->whereDate('complaints.resolved_at', '<=', $value);
                    break;
            }
        }
    }

    /**
     * Resolve date range with sane defaults
     */
    protected function resolveDateRange(Request $request): array
    {
        if ($request->filled('date_from')) {
            try {
                $dateFrom = Carbon::parse($request->input('date_from'))->startOfDay();
            } catch (\Exception $e) {
                $dateFrom = now()->subMonth()->startOfDay();
            }
        } else {
            $dateFrom = now()->subMonth()->startOfDay();
        }

        if ($request->filled('date_to')) {
            try {
                $dateTo = Carbon::parse($request->input('date_to'))->endOfDay();
            } catch (\Exception $e) {
                $dateTo = now()->endOfDay();
            }
        } else {
            $dateTo = now()->endOfDay();
        }
        return [$dateFrom, $dateTo];
    }

    /**
     * Update customer satisfaction score
     * 
     * @param Request $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSatisfactionScore(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'customer_satisfaction_score' => 'required|numeric|min:1|max:5'
        ]);

        try {
            // Only allow for resolved / closed complaints
            if (!$complaint->isResolved()) {
                return redirect()->back()->with('error', 'Satisfaction score can only be set for resolved or closed complaints.');
            }

            // Ensure metrics record exists
            if ($complaint->metrics) {
                $complaint->metrics->update([
                    'customer_satisfaction_score' => $validated['customer_satisfaction_score']
                ]);
            } else {
                $complaint->metrics()->create([
                    'customer_satisfaction_score' => $validated['customer_satisfaction_score']
                ]);
            }

            // Create history record
            $statusType = ComplaintStatusType::where('code', 'FEEDBACK')->first() ?? ComplaintStatusType::first();

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Feedback', // Added to enum via migration 2025_08_19_000001_*
                    'old_value' => null,
                    'new_value' => $validated['customer_satisfaction_score'] . '/5',
                    'comments' => 'Customer satisfaction score updated',
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'Customer',
                ]);
            }

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Customer satisfaction score updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating satisfaction score', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update satisfaction score. Please try again.');
        }
    }



    /**
     * Handle bulk operations on complaints
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'operation_type' => 'required|in:status_update,assignment,priority_change,branch_transfer,bulk_comment,bulk_delete',
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:complaints,id',
            // Dynamic validation based on operation type
            'status' => 'required_if:operation_type,status_update|in:Open,In Progress,Pending,Resolved,Closed',
            'status_change_reason' => 'nullable|string|max:255',
            'assigned_to' => 'required_if:operation_type,assignment|exists:users,id',
            'assignment_reason' => 'required_if:operation_type,assignment|string|max:255',
            'priority' => 'required_if:operation_type,priority_change|in:Low,Medium,High,Critical',
            'priority_change_reason' => 'required_if:operation_type,priority_change|string|max:255',
            'branch_id' => 'required_if:operation_type,branch_transfer|exists:branches,id',
            'comment_text' => 'required_if:operation_type,bulk_comment|string',
            'comment_type' => 'required_if:operation_type,bulk_comment|in:Internal,Customer,System',
            'is_private' => 'nullable|boolean',
            'deletion_reason' => 'required_if:operation_type,bulk_delete|string',
            'confirm_deletion' => 'required_if:operation_type,bulk_delete|accepted',
        ]);


        DB::beginTransaction();

        try {
            $operationType = $validated['operation_type'];
            $complaintIds = $validated['complaint_ids'];
            $updatedCount = 0;
            $statusType = ComplaintStatusType::first();

            foreach ($complaintIds as $complaintId) {
                $complaint = Complaint::find($complaintId);
                if (!$complaint)
                    continue;

                switch ($operationType) {
                    case 'status_update':
                        $this->handleBulkStatusUpdate($complaint, $validated, $statusType);
                        break;

                    case 'assignment':
                        $this->handleBulkAssignment($complaint, $validated, $statusType);
                        break;

                    case 'priority_change':
                        $this->handleBulkPriorityChange($complaint, $validated, $statusType);
                        break;

                    case 'branch_transfer':
                        $this->handleBulkBranchTransfer($complaint, $validated, $statusType);
                        break;

                    case 'bulk_comment':
                        $this->handleBulkComment($complaint, $validated, $statusType);
                        break;

                    case 'bulk_delete':
                        $this->handleBulkDelete($complaint, $validated, $statusType);
                        break;
                }

                $updatedCount++;
            }

            DB::commit();

            $operationName = str_replace('_', ' ', $operationType);
            return redirect()
                ->route('complaints.index')
                ->with('success', "Successfully performed {$operationName} on {$updatedCount} complaint(s).");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk operation', [
                'operation_type' => $operationType,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'complaint_ids' => $complaintIds
            ]);

            return redirect()->back()
                ->with('error', 'Failed to perform bulk operation. Please try again.');
        }
    }

    /**
     * Handle bulk status update
     */
    private function handleBulkStatusUpdate($complaint, $validated, $statusType)
    {
        if ($complaint->status === $validated['status']) {
            return; // No change needed
        }

        $oldStatus = $complaint->status;
        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'Resolved') {
            $updateData['resolved_by'] = auth()->id();
            $updateData['resolved_at'] = now();
        } elseif ($validated['status'] === 'Closed') {
            $updateData['closed_at'] = now();
        }

        $complaint->update($updateData);

        // Create history record
        if ($statusType) {
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Status Changed',
                'old_value' => $oldStatus,
                'new_value' => $validated['status'],
                'comments' => 'Bulk status update: ' . ($validated['status_change_reason'] ?? 'No reason provided'),
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'Internal',
            ]);
        }

        // Update metrics if resolved
        if ($validated['status'] === 'Resolved' && $oldStatus !== 'Resolved') {
            $this->updateComplaintMetrics($complaint, ['status' => $oldStatus], $updateData);
        }
    }

    /**
     * Handle bulk assignment
     */
    private function handleBulkAssignment($complaint, $validated, $statusType)
    {
        $oldAssignee = $complaint->assigned_to;

        // Update complaint assignment
        $complaint->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        // Deactivate previous assignments
        ComplaintAssignment::where('complaint_id', $complaint->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'unassigned_at' => now()]);

        // Create new assignment record
        ComplaintAssignment::create([
            'complaint_id' => $complaint->id,
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assignment_type' => 'Primary',
            'assigned_at' => now(),
            'reason' => 'Bulk assignment: ' . ($validated['assignment_reason'] ?? 'No reason provided'),
            'is_active' => true,
        ]);

        // Update metrics
        $complaint->metrics()->increment('assignment_count');

        // Create history record
        if ($statusType) {
            $oldAssigneeName = $oldAssignee ? User::find($oldAssignee)->name : 'Unassigned';
            $newAssigneeName = User::find($validated['assigned_to'])->name;

            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Reassigned',
                'old_value' => $oldAssigneeName,
                'new_value' => $newAssigneeName,
                'comments' => 'Bulk assignment: ' . ($validated['assignment_reason'] ?? 'No reason provided'),
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'Internal',
            ]);
        }
    }

    /**
     * Handle bulk priority change
     */
    private function handleBulkPriorityChange($complaint, $validated, $statusType)
    {
        if ($complaint->priority === $validated['priority']) {
            return; // No change needed
        }

        $oldPriority = $complaint->priority;
        $complaint->update(['priority' => $validated['priority']]);

        // Create history record
        if ($statusType) {
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Priority Changed',
                'old_value' => $oldPriority,
                'new_value' => $validated['priority'],
                'comments' => 'Bulk priority change: ' . ($validated['priority_change_reason'] ?? 'No reason provided'),
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'Internal',
            ]);
        }
    }

    /**
     * Handle bulk branch transfer
     */
    private function handleBulkBranchTransfer($complaint, $validated, $statusType)
    {
        if ($complaint->branch_id == $validated['branch_id']) {
            return; // No change needed
        }

        $oldBranch = $complaint->branch ? $complaint->branch->name : 'None';
        $complaint->update(['branch_id' => $validated['branch_id']]);
        $newBranch = Branch::find($validated['branch_id'])->name;

        // Create history record
        if ($statusType) {
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Branch Transfer',
                'old_value' => $oldBranch,
                'new_value' => $newBranch,
                'comments' => 'Bulk branch transfer',
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'Internal',
            ]);
        }
    }

    /**
     * Handle bulk comment addition
     */
    private function handleBulkComment($complaint, $validated, $statusType)
    {
        // Create comment
        ComplaintComment::create([
            'complaint_id' => $complaint->id,
            'comment_text' => $validated['comment_text'],
            'comment_type' => $validated['comment_type'],
            'is_private' => $validated['is_private'] ?? false,
        ]);

        // Create history record
        if ($statusType) {
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Comment Added',
                'old_value' => null,
                'new_value' => $validated['comment_type'] . ' comment',
                'comments' => 'Bulk comment: ' . substr($validated['comment_text'], 0, 100),
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'Internal',
            ]);
        }
    }

    /**
     * Handle bulk deletion
     */
    private function handleBulkDelete($complaint, $validated, $statusType)
    {
        // Create history record before deletion
        if ($statusType) {
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'action_type' => 'Closed',
                'old_value' => $complaint->status,
                'new_value' => 'Deleted',
                'comments' => 'Bulk deletion: ' . $validated['deletion_reason'],
                'status_id' => $statusType->id,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'complaint_type' => 'System',
            ]);
        }

        // Soft delete the complaint
        $complaint->delete();
    }
}