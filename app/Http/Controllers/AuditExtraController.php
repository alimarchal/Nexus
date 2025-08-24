<?php

namespace App\Http\Controllers;

use App\Models\{Audit, AuditAuditor, AuditChecklistItem, AuditItemResponse, AuditFinding, AuditAction, AuditActionUpdate, AuditScope, AuditSchedule, AuditNotification, AuditMetricsCache, AuditFindingAttachment};
use App\Models\AuditDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AuditExtraController extends Controller
{
    public function assignAuditors(Request $request, Audit $audit)
    {
        // Dual mode: bulk replace (user_ids[]) or single add/update (user_id, role, is_primary)
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'role' => 'nullable|string|in:lead,member,observer',
            'is_primary' => 'nullable|boolean',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        DB::transaction(function () use ($audit, $data) {
            if (!empty($data['user_id'])) {
                $role = $data['role'] ?? 'member';
                $makePrimary = (bool) ($data['is_primary'] ?? false);
                $existing = AuditAuditor::where('audit_id', $audit->id)->where('user_id', $data['user_id'])->first();
                if ($makePrimary) {
                    $audit->auditors()->update(['is_primary' => false]);
                }
                $auditor = AuditAuditor::updateOrCreate([
                    'audit_id' => $audit->id,
                    'user_id' => $data['user_id']
                ], [
                    'role' => $role,
                    'is_primary' => $makePrimary
                ]);
                // History log (use lifecycle-neutral placeholder so enum constraint not violated)
                \App\Models\AuditStatusHistory::create([
                    'auditable_type' => Audit::class,
                    'auditable_id' => $audit->id,
                    'from_status' => null,
                    'to_status' => $audit->status ?? 'planned',
                    'changed_by' => auth()->id(),
                    'note' => ($existing ? 'Updated auditor' : 'Added auditor') . ' ' . ($auditor->user?->name ?? 'User ID ' . $auditor->user_id) . ' role=' . $role . ($makePrimary ? ' (primary)' : ''),
                    'metadata' => [
                        'event' => $existing ? 'auditor_updated' : 'auditor_added',
                        'auditor_id' => $auditor->id,
                        'user_id' => $auditor->user_id,
                        'role' => $role,
                        'is_primary' => $makePrimary
                    ],
                    'changed_at' => now(),
                ]);
            } elseif (!empty($data['user_ids'])) {
                // Legacy replace
                $audit->auditors()->delete();
                foreach ($data['user_ids'] as $idx => $uid) {
                    $auditor = AuditAuditor::create([
                        'audit_id' => $audit->id,
                        'user_id' => $uid,
                        'role' => $data['role'] ?? 'member',
                        'is_primary' => $idx === 0,
                    ]);
                    \App\Models\AuditStatusHistory::create([
                        'auditable_type' => Audit::class,
                        'auditable_id' => $audit->id,
                        'from_status' => null,
                        'to_status' => $audit->status ?? 'planned',
                        'changed_by' => auth()->id(),
                        'note' => 'Added auditor ' . ($auditor->user?->name ?? 'User ID ' . $auditor->user_id) . ' role=' . $auditor->role . ($auditor->is_primary ? ' (primary)' : ''),
                        'metadata' => [
                            'event' => 'auditor_added',
                            'auditor_id' => $auditor->id,
                            'user_id' => $auditor->user_id,
                            'role' => $auditor->role,
                            'is_primary' => $auditor->is_primary
                        ],
                        'changed_at' => now(),
                    ]);
                }
            }
        });
        return back()->with('success', (!empty($data['user_id']) ? 'Auditor saved.' : 'Auditors updated.'));
    }

    public function removeAuditor(Audit $audit, AuditAuditor $auditor)
    {
        abort_unless($auditor->audit_id === $audit->id, 404);
        $name = $auditor->user?->name ?? ('User ID ' . $auditor->user_id);
        DB::transaction(function () use ($audit, $auditor, $name) {
            $metadata = [
                'event' => 'auditor_removed',
                'auditor_id' => $auditor->id,
                'user_id' => $auditor->user_id,
                'role' => $auditor->role,
                'was_primary' => $auditor->is_primary,
            ];
            $auditor->delete();
            \App\Models\AuditStatusHistory::create([
                'auditable_type' => Audit::class,
                'auditable_id' => $audit->id,
                'from_status' => null,
                'to_status' => $audit->status ?? 'planned',
                'changed_by' => auth()->id(),
                'note' => 'Removed auditor ' . $name,
                'metadata' => $metadata,
                'changed_at' => now(),
            ]);
        });
        return back()->with('success', 'Auditor removed.');
    }

    public function updateAuditor(Request $request, Audit $audit, AuditAuditor $auditor)
    {
        abort_unless($auditor->audit_id === $audit->id, 404);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:lead,member,observer',
            'is_primary' => 'nullable|boolean'
        ]);
        if (
            AuditAuditor::where('audit_id', $audit->id)
                ->where('user_id', $data['user_id'])
                ->where('id', '!=', $auditor->id)->exists()
        ) {
            return back()->with('error', 'Another auditor already uses that user.');
        }
        DB::transaction(function () use ($audit, $auditor, $data) {
            if (!empty($data['is_primary'])) {
                $audit->auditors()->where('id', '!=', $auditor->id)->update(['is_primary' => false]);
            }
            $auditor->update([
                'user_id' => $data['user_id'],
                'role' => $data['role'],
                'is_primary' => (bool) ($data['is_primary'] ?? false),
            ]);
        });
        return back()->with('success', 'Auditor updated.');
    }

    public function saveResponses(Request $request, Audit $audit)
    {
        $responses = $request->input('responses', []);
        $userId = auth()->id();
        $totalScore = 0;
        $totalMax = 0;
        DB::transaction(function () use ($responses, $audit, $userId, &$totalScore, &$totalMax) {
            foreach ($responses as $itemId => $payload) {
                if (!isset($payload['response_value'])) {
                    continue;
                }
                $item = AuditChecklistItem::find($itemId);
                if (!$item || $item->audit_type_id !== $audit->audit_type_id) {
                    continue;
                }
                $score = isset($payload['score']) ? (float) $payload['score'] : null;
                if ($score !== null) {
                    $totalScore += $score;
                    $totalMax += (float) ($item->max_score ?? 0);
                }
                AuditItemResponse::updateOrCreate([
                    'audit_id' => $audit->id,
                    'audit_checklist_item_id' => $itemId,
                ], [
                    'responded_by' => $userId,
                    'response_value' => $payload['response_value'],
                    'comment' => $payload['comment'] ?? null,
                    'score' => $score,
                    'responded_at' => now()
                ]);
            }
            if ($totalMax > 0) {
                $audit->score = round(($totalScore / $totalMax) * 100, 2);
                $audit->save();
            }
        });
        return back()->with('success', 'Responses saved.');
    }

    public function addFinding(Request $request, Audit $audit)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|in:process,compliance,safety,financial,operational,other',
            'severity' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,in_progress,implemented,verified,closed,void',
            'description' => 'nullable|string',
            'risk_description' => 'nullable|string',
            'root_cause' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'target_closure_date' => 'nullable|date',
            'actual_closure_date' => 'nullable|date',
            'owner_user_id' => 'nullable|exists:users,id',
            'attachments.*' => 'sometimes|file|max:20480'
        ]);
        $data['audit_id'] = $audit->id;
        $data['created_by'] = auth()->id();
        $data['reference_no'] = generateUniqueId('afd', 'audit_findings', 'reference_no');
        $finding = AuditFinding::create($data);
        // Handle attachments (store under Complaints path per requirement for consistency)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $storedPath = \App\Helpers\FileStorageHelper::storeSinglePrivateFile($file, 'Complaints/' . $audit->reference_no . '/findings');
                    AuditFindingAttachment::create([
                        'audit_finding_id' => $finding->id,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_name' => basename($storedPath),
                        'mime_type' => substr((string) $file->getMimeType(), 0, 150),
                        'size_bytes' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                        'metadata' => null,
                    ]);
                }
            }
        }
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Added finding: ' . $finding->title,
            'metadata' => [
                'event' => 'finding_added',
                'finding_id' => $finding->id,
                'severity' => $finding->severity,
                'status' => $finding->status,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Finding added.');
    }

    public function updateFinding(Request $request, Audit $audit, AuditFinding $finding)
    {
        abort_unless($finding->audit_id === $audit->id, 404);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|in:process,compliance,safety,financial,operational,other',
            'severity' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,in_progress,implemented,verified,closed,void',
            'description' => 'nullable|string',
            'risk_description' => 'nullable|string',
            'root_cause' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'target_closure_date' => 'nullable|date',
            'actual_closure_date' => 'nullable|date',
            'owner_user_id' => 'nullable|exists:users,id'
        ]);
        $finding->update($data);
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Updated finding: ' . $finding->title,
            'metadata' => [
                'event' => 'finding_updated',
                'finding_id' => $finding->id,
                'severity' => $finding->severity,
                'status' => $finding->status,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Finding updated.');
    }

    public function deleteFinding(Audit $audit, AuditFinding $finding)
    {
        abort_unless($finding->audit_id === $audit->id, 404);
        $snapshot = $finding->replicate();
        $finding->delete();
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Deleted finding: ' . $snapshot->title,
            'metadata' => [
                'event' => 'finding_deleted',
                'finding_id' => $snapshot->id,
                'severity' => $snapshot->severity,
                'status' => $snapshot->status,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Finding deleted.');
    }

    public function addFindingAttachment(Request $request, Audit $audit, AuditFinding $finding)
    {
        abort_unless($finding->audit_id === $audit->id, 404);
        $data = $request->validate([
            'file' => 'required|file|max:20480', // 20MB
        ]);
        $file = $data['file'];
        if ($file->isValid()) {
            $storedPath = \App\Helpers\FileStorageHelper::storeSinglePrivateFile($file, 'Complaints/' . $audit->reference_no . '/findings');
            $att = AuditFindingAttachment::create([
                'audit_finding_id' => $finding->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => basename($storedPath),
                'mime_type' => substr((string) $file->getMimeType(), 0, 150),
                'size_bytes' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'metadata' => null,
            ]);
            \App\Models\AuditStatusHistory::create([
                'auditable_type' => Audit::class,
                'auditable_id' => $audit->id,
                'from_status' => null,
                'to_status' => $audit->status ?? 'planned',
                'changed_by' => auth()->id(),
                'note' => 'Added finding attachment: ' . $att->original_name,
                'metadata' => [
                    'event' => 'finding_attachment_added',
                    'finding_id' => $finding->id,
                    'attachment_id' => $att->id,
                ],
                'changed_at' => now(),
            ]);
        }
        return back()->with('success', 'Attachment uploaded.');
    }

    public function deleteFindingAttachment(Audit $audit, AuditFinding $finding, AuditFindingAttachment $attachment)
    {
        abort_unless($finding->audit_id === $audit->id && $attachment->audit_finding_id === $finding->id, 404);
        $snapshot = $attachment->replicate();
        $attachment->delete();
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Deleted finding attachment: ' . $snapshot->original_name,
            'metadata' => [
                'event' => 'finding_attachment_deleted',
                'finding_id' => $finding->id,
                'attachment_id' => $snapshot->id,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Attachment deleted.');
    }

    public function downloadFindingAttachment(Audit $audit, AuditFinding $finding, AuditFindingAttachment $attachment)
    {
        abort_unless($finding->audit_id === $audit->id && $attachment->audit_finding_id === $finding->id, 404);
        // Attachments for findings were moved to the "Complaints/<ref>/findings" folder for consistency.
        // Older records may still exist under "Audits/<ref>/findings". Try new path first, then legacy.
        $candidates = [];
        // 1. Exact path under Complaints (current storage pattern)
        $candidates[] = storage_path('app/private/Complaints/' . $audit->reference_no . '/findings/' . $attachment->stored_name); // current path with 'private' disk root
        $candidates[] = storage_path('app/Complaints/' . $audit->reference_no . '/findings/' . $attachment->stored_name); // fallback without private
        // 2. Legacy Audits path
        $candidates[] = storage_path('app/private/Audits/' . $audit->reference_no . '/findings/' . $attachment->stored_name);
        $candidates[] = storage_path('app/Audits/' . $audit->reference_no . '/findings/' . $attachment->stored_name);
        // 3. If stored_name accidentally already includes the directory (full relative path)
        $candidates[] = storage_path('app/' . ltrim($attachment->stored_name, '/'));

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return response()->download($path, $attachment->original_name);
            }
        }
        \Log::warning('Finding attachment file not found', [
            'audit_id' => $audit->id,
            'finding_id' => $finding->id,
            'attachment_id' => $attachment->id,
            'stored_name' => $attachment->stored_name,
            'tried' => $candidates,
        ]);
        return back()->with('error', 'File not found.');
    }

    public function addAction(Request $request, Audit $audit, AuditFinding $finding)
    {
        abort_unless($finding->audit_id === $audit->id, 404);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_type' => 'nullable|in:corrective,preventive,remediation,improvement',
            'priority' => 'nullable|in:low,medium,high,critical',
            'owner_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date'
        ]);
        $data['audit_id'] = $audit->id;
        $data['audit_finding_id'] = $finding->id;
        $data['created_by'] = auth()->id();
        $data['status'] = 'open';
        $data['reference_no'] = generateUniqueId('act', 'audit_actions', 'reference_no');
        // Defaults if not provided
        $data['action_type'] = $data['action_type'] ?? 'corrective';
        $data['priority'] = $data['priority'] ?? 'medium';
        AuditAction::create($data);
        return back()->with('success', 'Action added.');
    }

    public function addActionUpdate(Request $request, Audit $audit, AuditAction $action)
    {
        abort_unless($action->audit_id === $audit->id, 404);
        $data = $request->validate([
            'update_text' => 'required|string|max:1000',
            'status_after' => 'nullable|in:open,in_progress,implemented,verified,closed,cancelled'
        ]);
        AuditActionUpdate::create([
            'audit_action_id' => $action->id,
            'created_by' => auth()->id(),
            'update_text' => $data['update_text'],
            'status_after' => $data['status_after'] ?? null,
            'is_system_generated' => false,
        ]);
        if (!empty($data['status_after'])) {
            $action->status = $data['status_after'];
            // Set completed_date when moving into a terminal/implemented state; clear otherwise
            $finalStates = ['implemented', 'verified', 'closed'];
            if (in_array($data['status_after'], $finalStates, true)) {
                if (!$action->completed_date) {
                    $action->forceFill(['completed_date' => now()]);
                }
            } else {
                $action->completed_date = null; // ensure cleared if reverting
            }
            $action->save();
        }
        return back()->with('success', 'Action update added.');
    }

    public function addScope(Request $request, Audit $audit)
    {
        $data = $request->validate([
            'scope_item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_in_scope' => 'nullable|boolean'
        ]);
        $data['audit_id'] = $audit->id;
        $data['is_in_scope'] = (bool) ($data['is_in_scope'] ?? true);
        $scope = AuditScope::create($data);
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Added scope item: ' . $scope->scope_item . ($scope->is_in_scope ? ' (in scope)' : ' (out of scope)'),
            'metadata' => [
                'event' => 'scope_added',
                'scope_id' => $scope->id,
                'scope_item' => $scope->scope_item,
                'is_in_scope' => $scope->is_in_scope,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Scope item added.');
    }

    public function deleteScope(Audit $audit, AuditScope $scope)
    {
        abort_unless($scope->audit_id === $audit->id, 404);
        $snapshot = $scope->replicate();
        $scope->delete();
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Removed scope item: ' . $snapshot->scope_item,
            'metadata' => [
                'event' => 'scope_removed',
                'scope_id' => $snapshot->id,
                'scope_item' => $snapshot->scope_item,
                'was_in_scope' => $snapshot->is_in_scope,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Scope item removed.');
    }

    public function addSchedule(Request $request, Audit $audit)
    {
        $data = $request->validate([
            'frequency' => 'required|string|max:50',
            'scheduled_date' => 'required|date'
        ]);
        $data['audit_id'] = $audit->id;
        $data['created_by'] = auth()->id();
        $data['next_run_date'] = $data['scheduled_date'];
        AuditSchedule::create($data);
        return back()->with('success', 'Schedule added.');
    }

    public function addNotification(Request $request, Audit $audit)
    {
        $data = $request->validate([
            'channel' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string'
        ]);
        $data['audit_id'] = $audit->id;
        $data['template'] = 'manual';
        // Enum allowed values: pending,sent,failed (migration). Use 'pending' for newly queued notifications.
        $data['status'] = 'pending';
        $data['notifiable_type'] = \App\Models\User::class;
        $data['notifiable_id'] = auth()->id();
        AuditNotification::create($data);
        return back()->with('success', 'Notification queued.');
    }

    public function recalcMetrics(Audit $audit)
    {
        $metrics = [
            'findings_total' => $audit->findings()->count(),
            'actions_open' => $audit->actions()->where('status', '!=', 'completed')->count(),
            'risks_total' => $audit->risks()->count(),
        ];
        foreach ($metrics as $key => $val) {
            AuditMetricsCache::updateOrCreate([
                'audit_id' => $audit->id,
                'metric_key' => $key,
            ], [
                'metric_value' => $val,
                'numeric_value' => (int) $val,
                'payload' => null,
                'calculated_at' => now(),
                'ttl_seconds' => 3600,
            ]);
        }
        return back()->with('success', 'Metrics recalculated.');
    }

    // Documents ---------------------------------------------------------------
    public function addDocument(Request $request, Audit $audit)
    {
        // Support old single 'file' field or new 'files[]' multiple
        if ($request->hasFile('file') && !$request->hasFile('files')) {
            $request->merge(['files' => [$request->file('file')]]);
        }
        $data = $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:51200', // 50MB per file
            'category' => 'nullable|string|max:100'
        ]);

        $stored = 0;
        foreach ($data['files'] as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            try {
                $storedPath = \App\Helpers\FileStorageHelper::storeSinglePrivateFile($file, 'Complaints/' . $audit->reference_no . '/documents');
                AuditDocument::create([
                    'audit_id' => $audit->id,
                    'audit_finding_id' => null,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($storedPath),
                    'mime_type' => substr((string) $file->getMimeType(), 0, 150),
                    'size_bytes' => $file->getSize(),
                    'category' => $data['category'] ?? null,
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                    'metadata' => null,
                ]);
                $stored++;
            } catch (\Throwable $e) {
                Log::warning('Audit document upload failed', [
                    'audit_id' => $audit->id,
                    'name' => $file?->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
            }
        }
        return back()->with($stored ? 'success' : 'error', $stored ? ($stored . ' document(s) uploaded.') : 'Upload failed.');
    }

    public function downloadDocument(Audit $audit, AuditDocument $document)
    {
        abort_unless($document->audit_id === $audit->id, 404);
        $candidates = [];
        $candidates[] = storage_path('app/private/Complaints/' . $audit->reference_no . '/documents/' . $document->stored_name);
        $candidates[] = storage_path('app/Complaints/' . $audit->reference_no . '/documents/' . $document->stored_name);
        // Raw stored name if it already contains path
        $candidates[] = storage_path('app/' . ltrim($document->stored_name, '/'));
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return response()->download($path, $document->original_name);
            }
        }
        Log::warning('Audit document not found', [
            'audit_id' => $audit->id,
            'document_id' => $document->id,
            'stored_name' => $document->stored_name,
            'tried' => $candidates,
        ]);
        return back()->with('error', 'File not found.');
    }

    public function updateDocument(Request $request, Audit $audit, AuditDocument $document)
    {
        abort_unless($document->audit_id === $audit->id, 404);
        $data = $request->validate([
            'category' => 'nullable|string|max:100'
        ]);
        $document->update(['category' => $data['category'] ?? null]);
        return back()->with('success', 'Document updated.');
    }

    public function deleteDocument(Audit $audit, AuditDocument $document)
    {
        abort_unless($document->audit_id === $audit->id, 404);
        try {
            $document->delete();
            return back()->with('success', 'Document deleted.');
        } catch (\Exception $e) {
            Log::error('Doc delete failed', ['id' => $document->id, 'e' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete document.');
        }
    }

    // Inline (audit-local) checklist items (not tied to audit type) -----------------
    public function addInlineChecklistItem(Request $request, Audit $audit)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'response_type' => 'nullable|in:yes_no,compliant_noncompliant,rating,text,numeric,evidence',
            'criteria' => 'nullable|string',
            'guidance' => 'nullable|string',
            'max_score' => 'nullable|integer|min:0|max:100'
        ]);
        $item = AuditChecklistItem::create([
            'audit_type_id' => null,
            'parent_id' => null,
            'reference_code' => null,
            'title' => $data['title'],
            'criteria' => $data['criteria'] ?? null,
            'guidance' => $data['guidance'] ?? null,
            'response_type' => $data['response_type'] ?? 'yes_no',
            'max_score' => $data['max_score'] ?? null,
            'display_order' => 0,
            'is_active' => true,
            'metadata' => ['inline_for_audit' => $audit->id],
        ]);
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Added inline item: ' . $item->title,
            'metadata' => [
                'event' => 'inline_item_added',
                'item_id' => $item->id,
                'title' => $item->title,
                'response_type' => $item->response_type,
                'max_score' => $item->max_score,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Assessment item added.');
    }

    public function deleteInlineChecklistItem(Audit $audit, AuditChecklistItem $item)
    {
        abort_unless(optional($item->metadata)['inline_for_audit'] === $audit->id, 404);
        $snapshot = $item->replicate();
        $item->delete();
        \App\Models\AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => $audit->status ?? 'planned',
            'changed_by' => auth()->id(),
            'note' => 'Removed inline item: ' . $snapshot->title,
            'metadata' => [
                'event' => 'inline_item_removed',
                'item_id' => $snapshot->id,
                'title' => $snapshot->title,
            ],
            'changed_at' => now(),
        ]);
        return back()->with('success', 'Assessment item removed.');
    }
}
