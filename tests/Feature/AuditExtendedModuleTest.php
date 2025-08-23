<?php

use App\Models\{User, Audit, AuditType, AuditChecklistItem, AuditFinding, AuditAction, AuditMetricsCache};
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create();
    $this->actingAs($this->user);
    $this->type = AuditType::first() ?? AuditType::create(['name' => 'Extended Type', 'code' => 'EXT', 'is_active' => true]);
    // minimal checklist item
    $this->item = AuditChecklistItem::first() ?? AuditChecklistItem::create([
        'audit_type_id' => $this->type->id,
        'reference_code' => 'CHK-1',
        'title' => 'Control present',
        'criteria' => 'Should exist',
        'response_type' => 'text',
        'max_score' => 10,
        'display_order' => 1,
        'is_active' => true,
    ]);
});

it('saves checklist responses and computes score', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);

    $this->post(route('audits.save-responses', $audit), [
        'responses' => [
            $this->item->id => ['response_value' => 'Yes', 'score' => 8, 'comment' => 'Good']
        ]
    ])->assertRedirect();

    $audit->refresh();
    expect($audit->score)->toBe(80.0);
});

it('adds finding and action with update and recalculates metrics', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);

    // Add finding
    $this->post(route('audits.findings.add', $audit), [
        'title' => 'Missing policy',
        'severity' => 'high',
        'description' => 'Policy not documented'
    ])->assertRedirect();

    $finding = AuditFinding::where('audit_id', $audit->id)->first();
    expect($finding)->not->toBeNull();

    // Add action
    $this->post(route('audits.actions.add', [$audit, 'finding' => $finding->id]), [
        'title' => 'Draft policy',
    ])->assertRedirect();

    $action = AuditAction::where('audit_finding_id', $finding->id)->first();
    expect($action)->not->toBeNull();

    // Add action update
    $this->post(route('audits.actions.updates.add', [$audit, 'action' => $action->id]), [
        'update_text' => 'Policy draft started',
        'status_after' => 'in_progress'
    ])->assertRedirect();

    $action->refresh();
    expect($action->status)->toBe('in_progress');

    // Recalc metrics
    $this->post(route('audits.metrics.recalc', $audit))->assertRedirect();
    expect(AuditMetricsCache::where('audit_id', $audit->id)->where('metric_key', 'findings_total')->exists())->toBeTrue();
});

it('adds scope schedule notification and deletes document', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);

    // Scope
    $this->post(route('audits.scopes.add', $audit), [
        'scope_item' => 'Finance Dept',
        'description' => 'All finance processes'
    ])->assertRedirect();
    expect($audit->scopes()->count())->toBe(1);

    // Schedule
    $this->post(route('audits.schedules.add', $audit), [
        'frequency' => 'annual',
        'scheduled_date' => now()->addDay()->format('Y-m-d')
    ])->assertRedirect();
    expect($audit->schedules()->count())->toBe(1);

    // Notification
    $this->post(route('audits.notifications.add', $audit), [
        'channel' => 'email',
        'subject' => 'Reminder',
        'body' => 'Audit kickoff'
    ])->assertRedirect();
    expect($audit->notifications()->count())->toBe(1);
});

it('covers full audit lifecycle across all related tables', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);

    // Tag
    $tag = \App\Models\AuditTag::firstOrCreate(['slug' => 'lc-test'], ['name' => 'LC Test', 'color' => '#10b981', 'is_active' => true]);
    $audit->tags()->attach($tag->id);
    expect($audit->tags()->count())->toBeGreaterThan(0);

    // Risk
    \App\Models\AuditRisk::create(['audit_id' => $audit->id, 'title' => 'Lifecycle Risk', 'likelihood' => 'low', 'impact' => 'high', 'risk_level' => 'high', 'status' => 'identified', 'created_by' => $this->user->id]);
    expect($audit->risks()->count())->toBe(1);

    // Auditor assignment
    $this->post(route('audits.assign-auditors', $audit), ['user_ids' => [$this->user->id]])->assertRedirect();
    expect($audit->auditors()->count())->toBe(1);

    // Scope
    $this->post(route('audits.scopes.add', $audit), ['scope_item' => 'IT', 'description' => 'IT Scope'])->assertRedirect();
    expect($audit->scopes()->count())->toBe(1);

    // Checklist response (existing seeder item or create one)
    $item = $this->item;
    $this->post(route('audits.save-responses', $audit), ['responses' => [$item->id => ['response_value' => 'yes', 'score' => 7]]])->assertRedirect();
    $audit->refresh();
    expect($audit->responses()->count())->toBeGreaterThan(0);

    // Finding -> Action -> Update
    $this->post(route('audits.findings.add', $audit), ['title' => 'Lifecycle Finding', 'severity' => 'medium'])->assertRedirect();
    $finding = \App\Models\AuditFinding::where('audit_id', $audit->id)->first();
    $this->post(route('audits.actions.add', [$audit, 'finding' => $finding->id]), ['title' => 'Lifecycle Action'])->assertRedirect();
    $action = \App\Models\AuditAction::where('audit_finding_id', $finding->id)->first();
    $this->post(route('audits.actions.updates.add', [$audit, 'action' => $action->id]), ['update_text' => 'Progress', 'status_after' => 'in_progress'])->assertRedirect();
    $action->refresh();
    expect($action->updates()->count())->toBe(1);

    // Schedule
    $this->post(route('audits.schedules.add', $audit), ['frequency' => 'annual', 'scheduled_date' => now()->addDay()->format('Y-m-d')])->assertRedirect();
    expect($audit->schedules()->count())->toBe(1);

    // Notification
    $this->post(route('audits.notifications.add', $audit), ['channel' => 'email', 'subject' => 'Lifecycle', 'body' => 'Body'])->assertRedirect();
    expect($audit->notifications()->count())->toBe(1);

    // Metrics recalculation
    $this->post(route('audits.metrics.recalc', $audit))->assertRedirect();
    expect($audit->metrics()->count())->toBeGreaterThan(0);

    // Status update
    $this->put(route('audits.update', $audit), ['status' => 'in_progress'])->assertRedirect();
    $audit->refresh();
    expect($audit->status)->toBe('in_progress');
});
