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
use Carbon\Carbon;

/**
 * ComplaintController handles comprehensive CRUD operations for complaints
 * Manages file uploads, assignments, escalations, histories, and all related entities
 */
class ComplaintController extends Controller
{
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
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::exact('assigned_by'),
                AllowedFilter::exact('resolved_by'),
                AllowedFilter::exact('sla_breached'),
                AllowedFilter::partial('complainant_name'),
                AllowedFilter::partial('complainant_email'),
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

        return view('complaints.index', compact('complaints', 'branches', 'users', 'statusTypes', 'statistics'));
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

        return view('complaints.create', compact('branches', 'users', 'categories', 'templates'));
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

            // Create complaint record
            $complaint = Complaint::create($validated);

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

                        ComplaintAttachment::create([
                            'complaint_id' => $complaint->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_size' => $file->getSize(),
                            'file_type' => $file->getMimeType(),
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

            // Create complaint category if provided
            if ($request->filled('category_id')) {
                $categoryData = ComplaintCategory::find($request->category_id);
                if ($categoryData) {
                    ComplaintCategory::create([
                        'complaint_id' => $complaint->id,
                        'category_name' => $categoryData->category_name,
                        'parent_category_id' => $categoryData->parent_category_id,
                        'description' => $categoryData->description,
                        'default_priority' => $categoryData->default_priority,
                        'sla_hours' => $categoryData->sla_hours,
                        'is_active' => true,
                    ]);
                }
            }

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

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create complaint. Please try again.');
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
        // Load all relationships
        $complaint->load([
            'branch',
            'assignedTo',
            'assignedBy',
            'resolvedBy',
            'histories' => function ($query) {
                $query->with(['status', 'performedBy'])->latest();
            },
            'comments' => function ($query) {
                $query->with('creator')->latest();
            },
            'attachments' => function ($query) {
                $query->with('creator')->latest();
            },
            'categories' => function ($query) {
                $query->with(['parent', 'creator'])->latest();
            },
            'assignments' => function ($query) {
                $query->with(['assignedTo', 'assignedBy'])->latest();
            },
            'escalations' => function ($query) {
                $query->with(['escalatedFrom', 'escalatedTo'])->latest();
            },
            'watchers' => function ($query) {
                $query->with('user');
            },
            'metrics'
        ]);

        // Get additional data for forms
        $users = User::orderBy('name')->get();
        $statusTypes = ComplaintStatusType::orderBy('name')->get();
        $templates = ComplaintTemplate::orderBy('template_name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('complaints.show', compact('complaint', 'users', 'statusTypes', 'templates', 'branches'));
    }

    /**
     * Show form to edit existing complaint
     * 
     * @param Complaint $complaint
     * @return \Illuminate\View\View
     */
    public function edit(Complaint $complaint)
    {
        $branches = Branch::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $categories = ComplaintCategory::topLevel()->orderBy('category_name')->get();
        $templates = ComplaintTemplate::active()->orderBy('template_name')->get();
        $divisions = \App\Models\Division::orderBy('name')->get();
        $statuses = ComplaintStatusType::orderBy('name')->get();

        return view('complaints.edit', compact('complaint', 'branches', 'users', 'categories', 'templates', 'divisions', 'statuses'));
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
                        $filePath = FileStorageHelper::storeSinglePrivateFile(
                            $file,
                            $folderName
                        );

                        ComplaintAttachment::create([
                            'complaint_id' => $complaint->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_size' => $file->getSize(),
                            'file_type' => $file->getMimeType(),
                        ]);
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

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', "Complaint '{$complaint->title}' updated successfully.");

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
        DB::beginTransaction();

        try {
            // Create history record for deletion
            $statusType = ComplaintStatusType::where('code', 'DELETED')->first()
                ?? ComplaintStatusType::first();

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Closed',
                    'old_value' => $complaint->status,
                    'new_value' => 'Deleted',
                    'comments' => 'Complaint deleted by user',
                    'status_id' => $statusType->id,
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'complaint_type' => 'System',
                ]);
            }

            // Soft delete the complaint (this will cascade to related records due to SoftDeletes)
            $complaint->delete();

            DB::commit();

            return redirect()
                ->route('complaints.index')
                ->with('success', "Complaint '{$complaint->title}' has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting complaint', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete complaint. Please try again.');
        }
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
            'category' => 'Category Changed'
        ];

        foreach ($trackableFields as $field => $actionType) {
            // Only create a history record when the incoming update explicitly contains the field
            // and the value actually changed compared to the original.
            $oldVal = $originalValues[$field] ?? null;
            if (array_key_exists($field, $newValues) && $oldVal != $newValues[$field]) {
                $newVal = $newValues[$field];
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
            $timeToResponse = $complaint->created_at->diffInMinutes(now());
            $metrics->update(['time_to_first_response' => $timeToResponse]);
        }

        // Calculate time to resolution
        if ($newStatus === 'Resolved' && $origStatus !== 'Resolved') {
            $timeToResolution = $complaint->created_at->diffInMinutes(now());
            $metrics->update(['time_to_resolution' => $timeToResolution]);
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

            return redirect()->back()
                ->with('error', 'Failed to export complaints. Please try again.');
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
            // Date range filter (ensure Carbon instances)
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

            // Basic statistics
            $totalComplaints = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])->count();
            $resolvedComplaints = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('status', ['Resolved', 'Closed'])->count();
            $openComplaints = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('status', ['Open', 'In Progress', 'Pending'])->count();
            $overdueComplaints = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('expected_resolution_date', '<', now())
                ->whereNotIn('status', ['Resolved', 'Closed'])->count();

            // Status distribution
            $statusDistribution = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            // Priority distribution
            $priorityDistribution = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get();

            // Source distribution
            $sourceDistribution = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->get();

            // Branch performance
            $branchPerformance = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->join('branches', 'complaints.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name as branch_name, COUNT(*) as total_complaints,
                    SUM(CASE WHEN complaints.status IN ("Resolved", "Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('branches.id', 'branches.name')
                ->get();

            // User performance
            $userPerformance = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->join('users', 'complaints.assigned_to', '=', 'users.id')
                ->selectRaw('users.name as user_name, COUNT(*) as assigned_complaints,
                    SUM(CASE WHEN complaints.status IN ("Resolved", "Closed") THEN 1 ELSE 0 END) as resolved_complaints')
                ->groupBy('users.id', 'users.name')
                ->get();

            // Average resolution time
            $avgResolutionTime = ComplaintMetric::join('complaints', 'complaint_metrics.complaint_id', '=', 'complaints.id')
                ->whereBetween('complaints.created_at', [$dateFrom, $dateTo])
                ->whereNotNull('time_to_resolution')
                ->avg('time_to_resolution');

            // SLA performance
            $slaBreached = Complaint::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('sla_breached', true)->count();
            $slaCompliance = $totalComplaints > 0 ? (($totalComplaints - $slaBreached) / $totalComplaints) * 100 : 100;

            // Monthly trend (last 12 months)
            $monthlyTrend = Complaint::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            return view('complaints.analytics', compact(
                'totalComplaints',
                'resolvedComplaints',
                'openComplaints',
                'overdueComplaints',
                'statusDistribution',
                'priorityDistribution',
                'sourceDistribution',
                'branchPerformance',
                'userPerformance',
                'avgResolutionTime',
                'slaCompliance',
                'monthlyTrend',
                'dateFrom',
                'dateTo'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating complaint analytics', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate analytics. Please try again.');
        }
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
            $complaint->metrics()->update([
                'customer_satisfaction_score' => $validated['customer_satisfaction_score']
            ]);

            // Create history record
            $statusType = ComplaintStatusType::where('code', 'FEEDBACK')->first()
                ?? ComplaintStatusType::first();

            if ($statusType) {
                ComplaintHistory::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'Feedback',
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