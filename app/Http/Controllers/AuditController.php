<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuditRequest;
use App\Http\Requests\UpdateAuditRequest;
use App\Models\Audit;
use App\Models\AuditType;
use App\Models\User;
use App\Models\AuditDocument;
use App\Models\AuditStatusHistory;
use App\Models\AuditTag;
use App\Models\AuditRisk;
use App\Models\AuditChecklistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\FileStorageHelper;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AuditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $audits = QueryBuilder::for(Audit::query()->with(['type', 'auditors.user', 'tags']))
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('reference_no'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('risk_overall'),
                AllowedFilter::exact('audit_type_id'),
                AllowedFilter::exact('lead_auditor_id'),
                AllowedFilter::exact('created_by'),
                // Tag (relation) filter
                AllowedFilter::callback('audit_tag_id', function ($q, $v) {
                    $q->whereHas('tags', fn($t) => $t->where('audit_tags.id', $v));
                }),
                // Created date range
                AllowedFilter::callback('date_from', function ($q, $v) {
                    $q->whereDate('created_at', '>=', $v); }),
                AllowedFilter::callback('date_to', function ($q, $v) {
                    $q->whereDate('created_at', '<=', $v); }),
                // Planned date ranges
                AllowedFilter::callback('planned_start_from', function ($q, $v) {
                    $q->whereDate('planned_start_date', '>=', $v); }),
                AllowedFilter::callback('planned_start_to', function ($q, $v) {
                    $q->whereDate('planned_start_date', '<=', $v); }),
                AllowedFilter::callback('planned_end_from', function ($q, $v) {
                    $q->whereDate('planned_end_date', '>=', $v); }),
                AllowedFilter::callback('planned_end_to', function ($q, $v) {
                    $q->whereDate('planned_end_date', '<=', $v); }),
                // Score range
                AllowedFilter::callback('score_min', function ($q, $v) {
                    if (is_numeric($v))
                        $q->where('score', '>=', $v); }),
                AllowedFilter::callback('score_max', function ($q, $v) {
                    if (is_numeric($v))
                        $q->where('score', '<=', $v); }),
            ])
            ->allowedSorts(['id', 'reference_no', 'title', 'status', 'risk_overall', 'planned_start_date', 'planned_end_date', 'score', 'created_at'])
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        $auditTypes = AuditType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $tags = \App\Models\AuditTag::orderBy('name')->get();

        // Statistics (simple aggregated counts for dashboard cards)
        $statusCounts = Audit::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->pluck('c', 'status');
        $riskCounts = Audit::select('risk_overall', DB::raw('count(*) as c'))
            ->whereNotNull('risk_overall')
            ->groupBy('risk_overall')->pluck('c', 'risk_overall');
        $statistics = [
            'total_audits' => Audit::count(),
            'status' => $statusCounts,
            'risk' => $riskCounts,
            'avg_score' => round((float) Audit::whereNotNull('score')->avg('score'), 2),
        ];

        return view('audits.index', compact('audits', 'auditTypes', 'users', 'tags', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auditTypes = AuditType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $tags = AuditTag::where('is_active', true)->orderBy('name')->get();
        $parentAudits = Audit::select('id', 'reference_no', 'title')->orderByDesc('id')->limit(50)->get();
        $checklistItems = AuditChecklistItem::where('audit_type_id', $auditTypes->first()?->id)->orderBy('display_order')->get();
        return view('audits.create', compact('auditTypes', 'users', 'parentAudits', 'tags', 'checklistItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuditRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Generate reference similar to complaints/circulars pattern
            $validated['reference_no'] = generateUniqueId('audit', 'audits', 'reference_no');
            $validated['status'] = 'planned';
            $validated['created_by'] = auth()->id();

            $tagIds = $request->input('tag_ids', []);
            $audit = Audit::create($validated);
            if ($tagIds) {
                $audit->tags()->sync($tagIds);
            }

            // Initial status history
            AuditStatusHistory::create([
                'auditable_type' => Audit::class,
                'auditable_id' => $audit->id,
                'from_status' => null,
                'to_status' => 'planned',
                'changed_by' => auth()->id(),
                'note' => 'Audit created',
                'changed_at' => now(),
            ]);

            // Optional quick risk creation (decoupled)
            if ($request->filled('risk.title')) {
                AuditRisk::create([
                    'audit_id' => $audit->id,
                    'title' => $request->input('risk.title'),
                    'description' => $request->input('risk.description'),
                    'likelihood' => $request->input('risk.likelihood', 'low'),
                    'impact' => $request->input('risk.impact', 'low'),
                    'risk_level' => $request->input('risk.risk_level', 'low'),
                    'status' => 'open',
                    'created_by' => auth()->id(),
                ]);
            }

            // Handle documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    if ($file->isValid()) {
                        $storedPath = FileStorageHelper::storeSinglePrivateFile($file, 'Audits/' . $audit->reference_no);
                        AuditDocument::create([
                            'audit_id' => $audit->id,
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => basename($storedPath),
                            'mime_type' => substr((string) $file->getMimeType(), 0, 150),
                            'size_bytes' => $file->getSize(),
                            'category' => 'general',
                            'uploaded_by' => auth()->id(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('audits.show', $audit)->with('success', 'Audit created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Audit create failed', ['error' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(), 0, 500)]);
            return redirect()->back()->withInput()->with('error', 'Failed to create audit.' . (app()->environment('local') ? ' ' . $e->getMessage() : ''));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Audit $audit)
    {
        $audit->load([
            'type',
            'auditors.user',
            'documents.uploader',
            'findings.actions.updates',
            'findings.owner',
            'actions.updates',
            'leadAuditor',
            'tags',
            'risks',
            'scopes',
            'schedules',
            'children',
            'notifications'
        ]);
        $statusHistory = $audit->statusHistories()->latest('changed_at')->limit(20)->get();
        $checklistItems = AuditChecklistItem::where('audit_type_id', $audit->audit_type_id)->orderBy('display_order')->get();
        $availableTags = AuditTag::where('is_active', true)->orderBy('name')->get();
        return view('audits.show', compact('audit', 'statusHistory', 'checklistItems', 'availableTags'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Audit $audit)
    {
        $audit->load(['type', 'leadAuditor', 'auditeeUser', 'tags']);
        $auditTypes = AuditType::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $tags = AuditTag::where('is_active', true)->orderBy('name')->get();
        $parentAudits = Audit::select('id', 'reference_no', 'title')->where('id', '!=', $audit->id)->orderByDesc('id')->limit(50)->get();
        $checklistItems = AuditChecklistItem::where('audit_type_id', $audit->audit_type_id)->orderBy('display_order')->get();
        return view('audits.edit', compact('audit', 'auditTypes', 'users', 'parentAudits', 'tags', 'checklistItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuditRequest $request, Audit $audit)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $originalStatus = $audit->status;
            $tagIds = $request->input('tag_ids', []);
            $audit->update($validated);
            if ($tagIds) {
                $audit->tags()->sync($tagIds);
            }

            // Status history
            if (array_key_exists('status', $validated) && $validated['status'] !== $originalStatus) {
                AuditStatusHistory::create([
                    'auditable_type' => Audit::class,
                    'auditable_id' => $audit->id,
                    'from_status' => $originalStatus,
                    'to_status' => $validated['status'],
                    'changed_by' => auth()->id(),
                    'note' => 'Status updated',
                    'changed_at' => now(),
                ]);
            }

            // Quick risk create on update (decoupled from documents)
            if ($request->filled('risk.title')) {
                AuditRisk::create([
                    'audit_id' => $audit->id,
                    'title' => $request->input('risk.title'),
                    'description' => $request->input('risk.description'),
                    'likelihood' => $request->input('risk.likelihood', 'low'),
                    'impact' => $request->input('risk.impact', 'low'),
                    'risk_level' => $request->input('risk.risk_level', 'low'),
                    'status' => 'open',
                    'created_by' => auth()->id(),
                ]);
            }

            // Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    if ($file->isValid()) {
                        $storedPath = FileStorageHelper::storeSinglePrivateFile($file, 'Audits/' . $audit->reference_no);
                        AuditDocument::create([
                            'audit_id' => $audit->id,
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => basename($storedPath),
                            'mime_type' => substr((string) $file->getMimeType(), 0, 150),
                            'size_bytes' => $file->getSize(),
                            'category' => 'general',
                            'uploaded_by' => auth()->id(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('audits.show', $audit)->with('success', 'Audit updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Audit update failed', ['audit_id' => $audit->id, 'error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update audit.' . (app()->environment('local') ? ' ' . $e->getMessage() : ''));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Audit $audit)
    {
        //
    }
}
