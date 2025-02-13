<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintStatusType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * Display a listing of complaints.
     */

public function index(Request $request)
{
    $statusTypes = ComplaintStatusType::all();
    $users = User::all(); // Fetch users

    $complaints = Complaint::query();

    if ($request->has('filter.status')) {
        $complaints->where('status_id', $request->input('filter.status'));
    }
    if ($request->has('filter.assigned_to')) {
        $complaints->where('assigned_to', $request->input('filter.assigned_to'));
    }

    $complaints = $complaints->paginate(10);

    return view('complaints.index', compact('complaints', 'statusTypes', 'users'));
}

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        $users = User::all();
        $statuses = ComplaintStatusType::all();
      // In create() method
$submitStatusId = ComplaintStatusType::where('name', 'Submitted')->value('id')
?? ComplaintStatusType::first()?->id
?? 1;

        return view('complaints.create', compact('users', 'statuses', 'submitStatusId'));
    }

    /**
     * Store a newly created complaint.
     */
    public function store(StoreComplaintRequest $request)
    {
        DB::beginTransaction();
        try {
            Log::info('Storing new complaint', ['data' => $request->all()]);
            Log::info('Received Status ID:', ['status_id' => $request->status_id]);

            // Ensure status_id exists
            $request->validate([
                'status_id' => 'required|exists:complaint_status_types,id',


            ]);

            // Generate unique reference number
            $referenceNumber = $this->generateReferenceNumber();

            $complaintData = [
                'reference_number' => $referenceNumber,
                'subject' => $request->subject,
                'description' => $request->description,
                'status_id' => $request->status_id,
                'created_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'due_date' => $request->due_date,
                'priority' => $request->priority ?? 'medium',
                'meta_data' => json_encode([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'created_at' => now()->toIso8601String()
                ])
            ];

            $complaint = Complaint::create($complaintData);
            Log::info('Complaint created successfully', ['id' => $complaint->id]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $this->storeAttachments($request->file('attachments'), $complaint);
            }

            DB::commit();

            return redirect()
                ->route('complaints.index')
                ->with('success', "Complaint created successfully! Reference Number: {$referenceNumber}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing complaint', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create complaint. Please try again.');
        }
    }

    /**
     * Display the specified complaint.
     */
    public function show(Complaint $complaint)
    {
        $complaint->load(['status', 'createdBy', 'assignedTo', 'attachments']);
        return view('complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint.
     */
    public function edit(Complaint $complaint)
    {
        $statuses = ComplaintStatusType::all();
        $users = User::all();
        return view('complaints.edit', compact('complaint', 'statuses', 'users'));
    }

    /**
     * Update the specified complaint.
     */
/**
 * Update the specified complaint.
 */
/**
 * Update the specified complaint.
 */
public function update(UpdateComplaintRequest $request, Complaint $complaint)
{
    DB::beginTransaction();
    try {
        Log::info('Updating complaint', ['id' => $complaint->id, 'data' => $request->all()]);

        // Validate request
        $validated = $request->validated();

        // Update core fields
        $updatedData = [
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status_id' => $validated['status_id'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'] ?? 'medium',
        ];

        Log::info('Updating with data:', $updatedData);

        $complaint->update($updatedData);

        // Update meta_data
        $metaData = json_decode($complaint->meta_data, true) ?? [];
        $metaData['updated_at'] = now()->toIso8601String();
        $complaint->update(['meta_data' => json_encode($metaData)]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            Log::info('Storing new attachments for complaint', ['id' => $complaint->id]);
            $this->storeAttachments($request->file('attachments'), $complaint);
        }

        DB::commit();

        return redirect()->route('complaints.show', $complaint)->with('success', 'Complaint updated successfully.');
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
}