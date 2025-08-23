<?php

namespace App\Http\Controllers;

use App\Models\{Audit, AuditAuditor, AuditChecklistItem, AuditItemResponse, AuditFinding, AuditAction, AuditActionUpdate, AuditScope, AuditSchedule, AuditNotification, AuditMetricsCache};
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
                // History log
                \App\Models\AuditStatusHistory::create([
                    'auditable_type' => Audit::class,
                    'auditable_id' => $audit->id,
                    'from_status' => $existing ? $existing->role : null,
                    'to_status' => $role,
                    'changed_by' => auth()->id(),
                    'note' => ($existing ? 'Updated auditor' : 'Added auditor') . ' ' . ($auditor->user?->name ?? 'User ID ' . $auditor->user_id) . ($makePrimary ? ' (primary)' : ''),
                    'metadata' => [
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
                        'to_status' => $auditor->role,
                        'changed_by' => auth()->id(),
                        'note' => 'Added auditor ' . ($auditor->user?->name ?? 'User ID ' . $auditor->user_id) . ($auditor->is_primary ? ' (primary)' : ''),
                        'metadata' => [
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
            'severity' => 'nullable|string|max:50',
            'description' => 'nullable|string'
        ]);
        $data['audit_id'] = $audit->id;
        $data['created_by'] = auth()->id();
        $data['reference_no'] = generateUniqueId('afd', 'audit_findings', 'reference_no');
        AuditFinding::create($data);
        return back()->with('success', 'Finding added.');
    }

    public function addAction(Request $request, Audit $audit, AuditFinding $finding)
    {
        abort_unless($finding->audit_id === $audit->id, 404);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date'
        ]);
        $data['audit_id'] = $audit->id;
        $data['audit_finding_id'] = $finding->id;
        $data['created_by'] = auth()->id();
        $data['status'] = 'open';
        $data['reference_no'] = generateUniqueId('act', 'audit_actions', 'reference_no');
        AuditAction::create($data);
        return back()->with('success', 'Action added.');
    }

    public function addActionUpdate(Request $request, Audit $audit, AuditAction $action)
    {
        abort_unless($action->audit_id === $audit->id, 404);
        $data = $request->validate([
            'update_text' => 'required|string|max:1000',
            'status_after' => 'nullable|string|max:50'
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
            if ($data['status_after'] === 'completed') {
                $action->update(['completed_date' => now()->format('Y-m-d')]);
            } else {
                $action->save();
            }
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
        AuditScope::create($data);
        return back()->with('success', 'Scope item added.');
    }

    public function deleteScope(Audit $audit, AuditScope $scope)
    {
        abort_unless($scope->audit_id === $audit->id, 404);
        $scope->delete();
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
}
