<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintStatusType;
use App\Models\Manager;
use App\Models\User;
use App\Models\ComplaintHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Division;
use App\Helpers\FileStorageHelper;


class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $statusTypes = ComplaintStatusType::all();
        $divisions = Division::all(); // Changed from users to divisions

        $complaints = Complaint::with([
            'assignedTo' => function ($query) {
                $query->latest(); // Fetch the latest assigned user
            },
            'assignedDivision' => function ($query) {
                $query->latest(); // Fetch the latest assigned division
            }
        ]);

        if ($request->has('filter.status')) {
            $complaints->where('status_id', $request->input('filter.status'));
        }
        if ($request->has('filter.assigned_to')) {
            $complaints->where('assigned_to', $request->input('filter.assigned_to'));
        }

        $complaints = $complaints->orderBy('complaints.created_at', 'DESC')->paginate(10);

        return view('complaints.index', compact('complaints', 'statusTypes', 'divisions'));
    }

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        $divisions = Division::all(); // Changed from users to divisions
        $statuses = ComplaintStatusType::all();
        $submitStatusId = ComplaintStatusType::where('name', 'Submitted')->value('id')
            ?? ComplaintStatusType::first()?->id
            ?? 1;

        return view('complaints.create', compact('divisions', 'statuses', 'submitStatusId'));
    }

    /**
     * Store a newly created complaint.
     */
    public function store(StoreComplaintRequest $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'status_id' => 'required|exists:complaint_status_types,id',
                'assigned_to' => 'required|exists:divisions,id',
                'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(7)->toDateString()],
            ]);

            // Generate unique reference number
            $referenceNumber = generateUniqueId('complaint', 'complaints', 'reference_number');

            $complaintData = [
                'reference_number' => $referenceNumber,
                'subject' => $request->subject,
                'status_id' => $request->status_id,
                'assigned_to' => $request->assigned_to,
                'due_date' => $request->due_date,
                'priority' => $request->priority ?? 'medium',
                'meta_data' => json_encode([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'created_at' => now()->toIso8601String()
                ])
            ];

            $manager = Manager::where('division_id', $request->assigned_to)->first();

            if (!$manager) {
                throw new \Exception('Failed to create complaint. Please ask the division to assign a manager for this complaint.');
            }

            $complaintData['assigned_to'] = $manager->manager_user_id;

            $complaint = Complaint::create($complaintData);

            // Handle attachments using FileStorageHelper
            if ($request->hasFile('attachments')) {
                FileStorageHelper::storeFiles(
                    files: $request->file('attachments'),
                    modelClass: ComplaintAttachment::class,
                    folderName: 'complaints',
                    relationData: ['complaint_id' => $complaint->id],
                    subFolder: $complaint->reference_number
                );
            }

            DB::commit();

            return redirect()
                ->route('complaints.index')
                ->with('success', "Complaint created successfully! Reference Number: {$referenceNumber}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create complaint. Please try again. Please ask the division to assign a manager for this complaint.');
        }
    }


    /**
     * Display the specified complaint.
     */
    public function show(Complaint $complaint)
    {
        $complaint->load([
            'status',
            'creator',
            'assignedTo',
            'histories.status',
            'histories.changedBy',
            'attachments'
        ]);

        $statuses = ComplaintStatusType::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();

        return view('complaints.show', compact('complaint', 'statuses', 'users'));
    }


    /**
     * Show the form for editing the specified complaint.
     */
    /**
     * Show the form for editing the specified complaint.
     */
    public function edit(Complaint $complaint)
    {
        $statuses = ComplaintStatusType::all();
        $divisions = Division::all(); // Changed from users to divisions
        return view('complaints.edit', compact('complaint', 'statuses', 'divisions'));
    }

    /**
     * Update the specified complaint.
     */
    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        DB::beginTransaction();
        try {
            Log::info('Updating complaint', ['id' => $complaint->id, 'data' => $request->all()]);

            // Validate all required fields
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'status_id' => 'required|exists:complaint_status_types,id',
                'assigned_to' => 'required|exists:divisions,id',
                // 'priority' => 'required|in:low,medium,high',
                // 'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(7)->toDateString()],
            ]);

            // Update complaint with validated data
            $complaint->update([
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'status_id' => $validated['status_id'],
                'assigned_to' => $validated['assigned_to'],
                // 'priority' => $validated['priority'],
                // 'due_date' => $validated['due_date'],
            ]);

            // Create history record for the update
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'status_id' => $validated['status_id'],
                'changed_by' => auth()->id(),
                'comments' => 'Complaint updated',
                'changes' => json_encode($validated),
            ]);

            // Handle attachments if any
            if ($request->hasFile('attachments')) {
                Log::info('Storing new attachments for complaint', ['id' => $complaint->id]);
                $this->storeAttachments($request->file('attachments'), $complaint);
            }

            DB::commit();
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Complaint updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating complaint', [
                'id' => $complaint->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to update complaint. Please try again.');
        }
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy(Complaint $complaint)
    {
        try {
            Log::info('Deleting complaint', ['id' => $complaint->id]);

            $complaint->delete();
            return redirect()
                ->route('complaints.index')
                ->with('success', 'Complaint deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting complaint', ['id' => $complaint->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete complaint. Please try again.');
        }
    }

    /**
     * Generate a unique reference number for complaints.
     */
    private function generateReferenceNumber()
    {
        $prefix = 'COMP-' . date('Y');
        $lastComplaint = Complaint::withTrashed()
            ->whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastComplaint ?
            (int) substr($lastComplaint->reference_number, strrpos($lastComplaint->reference_number, '-') + 1) : 0;

        return $prefix . '-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Store multiple attachments for a complaint.
     */
    private function storeAttachments($files, Complaint $complaint)
    {
        foreach ($files as $file) {
            if ($file->isValid()) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('complaints/' . $complaint->reference_number, $filename, 'public');

                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id()
                ]);
            }
        }
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        // Validate request data
        $validated = $request->validate([
            'status_id' => 'required|exists:complaint_status_types,id',
            'comments' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust as needed
        ]);

        // Track changes
        $changes = [];
        if ($complaint->status_id != $validated['status_id']) {
            $changes['status_id'] = [
                'old' => $complaint->status_id,
                'new' => $validated['status_id']
            ];
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentName = time() . '.' . $attachment->getClientOriginalExtension();

            // Store file in 'public' disk under 'complaints' directory
            $attachmentPath = $attachment->storeAs(
                'complaints',
                $attachmentName,
                'public'
            );

            Log::info('Attachment Stored: ' . $attachmentPath);
        } else {
            Log::info('No Attachment Uploaded.');
        }

        // Prepare history record
        $historyData = [
            'complaint_id' => $complaint->id,
            'status_id' => $validated['status_id'],
            'changed_by' => auth()->id(),
            'comments' => $validated['comments'],
            'changes' => !empty($changes) ? json_encode($changes) : null,
            'attachment' => $attachmentPath, // Store attachment path in the database
        ];

        // Log history data before saving
        Log::info('History Data:', $historyData);

        // Save to database
        $history = ComplaintHistory::create($historyData);

        if (!$history->attachment) {
            Log::error('Attachment not saved in database.');
        } else {
            Log::info('Attachment saved successfully in database.');
        }

        // Update complaint status
        $complaint->update(['status_id' => $validated['status_id']]);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint status updated successfully.');
    }
}
