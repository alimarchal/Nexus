<?php

use App\Models\User;
use App\Models\Audit;
use App\Models\AuditType;
use App\Models\AuditChecklistItem;
use App\Models\AuditDocument;
use App\Models\AuditStatusHistory;
use App\Models\AuditFinding;
use App\Models\AuditAction;
use App\Models\AuditItemResponse;
use App\Models\AuditAuditor;
use App\Models\AuditTag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    
    // Create test users
    $this->admin = User::factory()->create(['name' => 'Admin User']);
    $this->user = User::factory()->create(['name' => 'Regular User']);
    $this->auditor = User::factory()->create(['name' => 'Lead Auditor']);
    $this->auditee = User::factory()->create(['name' => 'Auditee User']);
    
    // Create audit type with checklist items
    $this->auditType = AuditType::factory()->create([
        'name' => 'Internal Controls Review',
        'code' => 'ICR',
        'description' => 'Standard internal controls audit',
        'is_active' => true,
    ]);
    
    // Create checklist items for the audit type
    $this->checklistItem1 = AuditChecklistItem::factory()->create([
        'audit_type_id' => $this->auditType->id,
        'title' => 'Review documentation',
        'description' => 'Check if all required documents are present',
        'display_order' => 1,
        'max_score' => 10,
        'is_required' => true,
    ]);
    
    $this->checklistItem2 = AuditChecklistItem::factory()->create([
        'audit_type_id' => $this->auditType->id,
        'title' => 'Verify compliance',
        'description' => 'Ensure compliance with policies',
        'display_order' => 2,
        'max_score' => 15,
        'is_required' => true,
    ]);
    
    // Create audit tags
    $this->tag1 = AuditTag::factory()->create(['name' => 'High Risk']);
    $this->tag2 = AuditTag::factory()->create(['name' => 'Financial']);
});

describe('Audit Controller - CRUD Operations', function () {
    it('lists all audits with filtering', function () {
        // Create test audits
        $audit1 = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'title' => 'Financial Review',
            'status' => 'in_progress',
            'risk_overall' => 'high',
            'created_by' => $this->admin->id,
            'lead_auditor_id' => $this->auditor->id,
        ]);
        
        $audit2 = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'title' => 'Compliance Check',
            'status' => 'planned',
            'risk_overall' => 'medium',
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        // Test basic index
        $response = $this->get(route('audits.index'));
        $response->assertStatus(200);
        
        // Test filtering by status
        $response = $this->get(route('audits.index', ['filter[status]' => 'in_progress']));
        $response->assertStatus(200);
        
        // Test filtering by risk level
        $response = $this->get(route('audits.index', ['filter[risk_overall]' => 'high']));
        $response->assertStatus(200);
        
        // Test filtering by audit type
        $response = $this->get(route('audits.index', ['filter[audit_type_id]' => $this->auditType->id]));
        $response->assertStatus(200);
    });

    it('shows a single audit with details', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'title' => 'Test Audit Details',
            'description' => 'Detailed description of the audit',
            'created_by' => $this->admin->id,
            'lead_auditor_id' => $this->auditor->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->get(route('audits.show', $audit));
        $response->assertStatus(200);
    });

    it('creates a new audit with all required fields', function () {
        $this->actingAs($this->admin);
        
        $auditData = [
            'audit_type_id' => $this->auditType->id,
            'title' => 'New Financial Audit',
            'description' => 'Comprehensive financial controls review',
            'scope_summary' => 'Review all financial processes and controls',
            'planned_start_date' => now()->addDays(7)->toDateString(),
            'planned_end_date' => now()->addDays(14)->toDateString(),
            'risk_overall' => 'medium',
            'lead_auditor_id' => $this->auditor->id,
            'auditee_user_id' => $this->auditee->id,
            'tag_ids' => [$this->tag1->id, $this->tag2->id],
        ];
        
        $response = $this->post(route('audits.store'), $auditData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('audits', [
            'title' => 'New Financial Audit',
            'audit_type_id' => $this->auditType->id,
            'status' => 'planned', // Default status
            'risk_overall' => 'medium',
            'lead_auditor_id' => $this->auditor->id,
            'created_by' => $this->admin->id,
        ]);
        
        // Verify reference number was auto-generated
        $audit = Audit::where('title', 'New Financial Audit')->first();
        expect($audit->reference_no)->not->toBeNull();
        
        // Verify tags were attached
        expect($audit->tags)->toHaveCount(2);
        
        // Verify initial status history was created
        $this->assertDatabaseHas('audit_status_histories', [
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => 'planned',
            'changed_by' => $this->admin->id,
        ]);
    });

    it('updates an existing audit', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'title' => 'Original Title',
            'status' => 'planned',
            'risk_overall' => 'low',
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'risk_overall' => 'high',
            'lead_auditor_id' => $this->auditor->id,
        ];
        
        $response = $this->put(route('audits.update', $audit), $updateData);
        
        $response->assertRedirect();
        
        $audit->refresh();
        expect($audit->title)->toBe('Updated Title');
        expect($audit->description)->toBe('Updated description');
        expect($audit->risk_overall)->toBe('high');
        expect($audit->lead_auditor_id)->toBe($this->auditor->id);
    });

    it('deletes an audit', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->delete(route('audits.destroy', $audit));
        
        $response->assertRedirect();
        
        // Should be soft deleted
        $this->assertSoftDeleted('audits', ['id' => $audit->id]);
    });
});

describe('Audit Extra Controller - Status Management', function () {
    it('updates audit status with history tracking', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'status' => 'planned',
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->patch(route('audits.status.update', $audit), [
            'status' => 'in_progress',
            'note' => 'Audit kickoff meeting completed',
        ]);
        
        $response->assertRedirect();
        
        $audit->refresh();
        expect($audit->status)->toBe('in_progress');
        
        // Verify status history was created
        $this->assertDatabaseHas('audit_status_histories', [
            'auditable_id' => $audit->id,
            'from_status' => 'planned',
            'to_status' => 'in_progress',
            'changed_by' => $this->admin->id,
        ]);
    });

    it('updates basic audit information', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->patch(route('audits.basic.update', $audit), [
            'title' => 'Updated Audit Title',
            'description' => 'Updated description',
            'scope_summary' => 'Updated scope',
            'planned_start_date' => now()->addDays(5)->toDateString(),
            'planned_end_date' => now()->addDays(10)->toDateString(),
        ]);
        
        $response->assertRedirect();
        
        $audit->refresh();
        expect($audit->title)->toBe('Updated Audit Title');
        expect($audit->description)->toBe('Updated description');
        expect($audit->scope_summary)->toBe('Updated scope');
    });
});

describe('Audit Extra Controller - Checklist Responses', function () {
    it('saves checklist item responses and calculates score', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.save-responses', $audit), [
            'responses' => [
                $this->checklistItem1->id => [
                    'response_value' => 'Yes',
                    'score' => 8,
                    'comment' => 'Good documentation found',
                ],
                $this->checklistItem2->id => [
                    'response_value' => 'Partial',
                    'score' => 10,
                    'comment' => 'Some gaps identified',
                ]
            ]
        ]);
        
        $response->assertRedirect();
        
        // Verify responses were saved
        $this->assertDatabaseHas('audit_item_responses', [
            'audit_id' => $audit->id,
            'audit_checklist_item_id' => $this->checklistItem1->id,
            'response_value' => 'Yes',
            'score' => 8,
            'responded_by' => $this->admin->id,
        ]);
        
        $this->assertDatabaseHas('audit_item_responses', [
            'audit_id' => $audit->id,
            'audit_checklist_item_id' => $this->checklistItem2->id,
            'response_value' => 'Partial',
            'score' => 10,
            'responded_by' => $this->admin->id,
        ]);
        
        // Verify audit score was calculated
        $audit->refresh();
        expect($audit->score)->not->toBeNull();
        expect($audit->score)->toBeGreaterThan(0);
    });
});

describe('Audit Extra Controller - Findings Management', function () {
    it('adds a finding to an audit', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.findings.add', $audit), [
            'title' => 'Control Gap Identified',
            'description' => 'Missing segregation of duties',
            'severity' => 'high',
            'category' => 'Internal Controls',
            'impact_assessment' => 'Could lead to fraud',
            'root_cause' => 'Lack of proper procedures',
            'recommendation' => 'Implement segregation controls',
            'target_closure_date' => now()->addDays(30)->toDateString(),
            'owner_user_id' => $this->auditee->id,
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('audit_findings', [
            'audit_id' => $audit->id,
            'title' => 'Control Gap Identified',
            'severity' => 'high',
            'created_by' => $this->admin->id,
            'owner_user_id' => $this->auditee->id,
        ]);
        
        // Verify reference number was auto-generated
        $finding = AuditFinding::where('title', 'Control Gap Identified')->first();
        expect($finding->reference_no)->not->toBeNull();
    });

    it('adds an action to a finding', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $finding = AuditFinding::factory()->create([
            'audit_id' => $audit->id,
            'title' => 'Test Finding',
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.actions.add', [$audit, 'finding' => $finding->id]), [
            'title' => 'Implement New Control',
            'description' => 'Create segregation of duties policy',
            'planned_completion_date' => now()->addDays(45)->toDateString(),
            'responsible_user_id' => $this->auditee->id,
            'status' => 'planned',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('audit_actions', [
            'audit_finding_id' => $finding->id,
            'title' => 'Implement New Control',
            'status' => 'planned',
            'responsible_user_id' => $this->auditee->id,
            'created_by' => $this->admin->id,
        ]);
    });

    it('adds an update to an action', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $finding = AuditFinding::factory()->create([
            'audit_id' => $audit->id,
            'created_by' => $this->admin->id,
        ]);
        
        $action = AuditAction::factory()->create([
            'audit_finding_id' => $finding->id,
            'status' => 'in_progress',
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.actions.updates.add', [$audit, 'action' => $action->id]), [
            'update_text' => 'Policy draft completed and under review',
            'status_after' => 'in_progress',
            'completion_percentage' => 75,
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('audit_action_updates', [
            'audit_action_id' => $action->id,
            'update_text' => 'Policy draft completed and under review',
            'status_after' => 'in_progress',
            'completion_percentage' => 75,
            'updated_by' => $this->admin->id,
        ]);
    });
});

describe('Audit Extra Controller - Document Management', function () {
    it('adds documents to an audit', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $file = UploadedFile::fake()->create('audit_plan.pdf', 100, 'application/pdf');
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.documents.add', $audit), [
            'documents' => [$file],
            'category' => 'Planning',
            'description' => 'Initial audit plan document',
        ]);
        
        $response->assertRedirect();
        
        expect($audit->documents()->count())->toBe(1);
        
        $document = $audit->documents()->first();
        expect($document->original_name)->toBe('audit_plan.pdf');
        expect($document->category)->toBe('Planning');
        expect($document->uploaded_by)->toBe($this->admin->id);
    });
});

describe('Audit Extra Controller - Auditor Management', function () {
    it('manages audit team members', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $auditor2 = User::factory()->create(['name' => 'Second Auditor']);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.auditors.manage', $audit), [
            'user_ids' => [$this->auditor->id, $auditor2->id],
            'role' => 'auditor',
        ]);
        
        $response->assertRedirect();
        
        expect($audit->auditors()->count())->toBe(2);
        
        $this->assertDatabaseHas('audit_auditors', [
            'audit_id' => $audit->id,
            'user_id' => $this->auditor->id,
            'role' => 'auditor',
            'is_primary' => true, // First one should be primary
        ]);
        
        $this->assertDatabaseHas('audit_auditors', [
            'audit_id' => $audit->id,
            'user_id' => $auditor2->id,
            'role' => 'auditor',
            'is_primary' => false,
        ]);
    });
});

describe('Audit Controller - Authorization', function () {
    it('requires authentication for audit operations', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        // Test unauthenticated access
        $this->get(route('audits.index'))->assertRedirect();
        $this->get(route('audits.show', $audit))->assertRedirect();
        $this->post(route('audits.store'), [])->assertRedirect();
        $this->put(route('audits.update', $audit), [])->assertRedirect();
        $this->delete(route('audits.destroy', $audit))->assertRedirect();
    });
});

describe('Audit Controller - Validation', function () {
    it('validates required fields when creating audit', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.store'), []);
        
        $response->assertSessionHasErrors();
    });

    it('validates date format and logic', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.store'), [
            'audit_type_id' => $this->auditType->id,
            'title' => 'Test Audit',
            'planned_start_date' => 'invalid-date',
            'planned_end_date' => 'invalid-date',
        ]);
        
        $response->assertSessionHasErrors(['planned_start_date', 'planned_end_date']);
    });

    it('validates end date is after start date', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('audits.store'), [
            'audit_type_id' => $this->auditType->id,
            'title' => 'Test Audit',
            'planned_start_date' => '2024-12-31',
            'planned_end_date' => '2024-12-01', // Before start date
        ]);
        
        $response->assertSessionHasErrors(['planned_end_date']);
    });

    it('validates status enum values', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->patch(route('audits.status.update', $audit), [
            'status' => 'InvalidStatus',
        ]);
        
        $response->assertSessionHasErrors(['status']);
    });

    it('validates risk level enum values', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->put(route('audits.update', $audit), [
            'risk_overall' => 'InvalidRisk',
        ]);
        
        $response->assertSessionHasErrors(['risk_overall']);
    });
});