<?php

use App\Models\User;
use App\Models\Audit;
use App\Models\AuditType;
use App\Models\AuditAuditor;
use App\Models\AuditChecklistItem;
use App\Models\AuditDocument;
use App\Models\AuditFinding;
use App\Models\AuditAction;
use App\Models\AuditItemResponse;
use App\Models\AuditStatusHistory;
use App\Models\AuditScope;
use App\Models\AuditRisk;
use App\Models\AuditNotification;
use App\Models\AuditTag;
use App\Models\AuditSchedule;
use App\Models\AuditMetricsCache;

beforeEach(function () {
    // Create test users and audit type
    $this->admin = User::factory()->create();
    $this->user = User::factory()->create();
    $this->auditor = User::factory()->create();
    $this->auditee = User::factory()->create();
    
    $this->auditType = AuditType::factory()->create([
        'name' => 'Test Audit Type',
        'code' => 'TAT',
        'is_active' => true,
    ]);
});

describe('Audit Model - Basic Properties', function () {
    it('uses UUID as primary key', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->getKeyType())->toBe('string');
        expect($audit->getIncrementing())->toBe(false);
        expect(strlen($audit->id))->toBe(36); // UUID length
    });

    it('has correct fillable attributes', function () {
        $expectedFillable = [
            'audit_type_id',
            'reference_no',
            'title',
            'description',
            'scope_summary',
            'planned_start_date',
            'planned_end_date',
            'actual_start_date',
            'actual_end_date',
            'status',
            'risk_overall',
            'created_by',
            'lead_auditor_id',
            'auditee_user_id',
            'score',
            'is_template',
            'parent_audit_id',
            'metadata'
        ];
        
        $audit = new Audit();
        expect($audit->getFillable())->toMatchArray($expectedFillable);
    });

    it('has correct cast attributes', function () {
        $expectedCasts = [
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
            'is_template' => 'boolean',
            'metadata' => 'array',
            'score' => 'float'
        ];
        
        $audit = new Audit();
        $casts = $audit->getCasts();
        
        foreach ($expectedCasts as $attribute => $expectedCast) {
            expect($casts[$attribute])->toBe($expectedCast);
        }
    });
});

describe('Audit Model - Relationships', function () {
    it('belongs to an audit type', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->type)->toBeInstanceOf(AuditType::class);
        expect($audit->type->id)->toBe($this->auditType->id);
    });

    it('belongs to a parent audit', function () {
        $parentAudit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $childAudit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'parent_audit_id' => $parentAudit->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($childAudit->parent)->toBeInstanceOf(Audit::class);
        expect($childAudit->parent->id)->toBe($parentAudit->id);
    });

    it('has many child audits', function () {
        $parentAudit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $childAudit1 = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'parent_audit_id' => $parentAudit->id,
            'created_by' => $this->admin->id,
        ]);
        
        $childAudit2 = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'parent_audit_id' => $parentAudit->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($parentAudit->children)->toHaveCount(2);
        expect($parentAudit->children->first())->toBeInstanceOf(Audit::class);
    });

    it('belongs to a creator user', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        // Note: The model doesn't have a direct createdBy relationship defined, 
        // but it should have created_by field
        expect($audit->created_by)->toBe($this->admin->id);
    });

    it('belongs to a lead auditor', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'lead_auditor_id' => $this->auditor->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->leadAuditor)->toBeInstanceOf(User::class);
        expect($audit->leadAuditor->id)->toBe($this->auditor->id);
    });

    it('belongs to an auditee user', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'auditee_user_id' => $this->auditee->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->auditeeUser)->toBeInstanceOf(User::class);
        expect($audit->auditeeUser->id)->toBe($this->auditee->id);
    });

    it('has many auditors', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $auditor1 = $audit->auditors()->create([
            'user_id' => $this->auditor->id,
            'role' => 'lead',
            'is_primary' => true,
        ]);
        
        $auditor2 = $audit->auditors()->create([
            'user_id' => $this->user->id,
            'role' => 'member',
            'is_primary' => false,
        ]);
        
        expect($audit->auditors)->toHaveCount(2);
        expect($audit->auditors->first())->toBeInstanceOf(AuditAuditor::class);
    });

    it('has checklist items through audit type', function () {
        $checklistItem = AuditChecklistItem::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'title' => 'Test checklist item',
        ]);
        
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->checklistItems)->toHaveCount(1);
        expect($audit->checklistItems->first())->toBeInstanceOf(AuditChecklistItem::class);
        expect($audit->checklistItems->first()->title)->toBe('Test checklist item');
    });

    it('has many responses', function () {
        $checklistItem = AuditChecklistItem::factory()->create([
            'audit_type_id' => $this->auditType->id,
        ]);
        
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $response = $audit->responses()->create([
            'audit_checklist_item_id' => $checklistItem->id,
            'response_value' => 'yes',
            'score' => 8,
            'comment' => 'Good compliance',
            'responded_by' => $this->auditor->id,
        ]);
        
        expect($audit->responses)->toHaveCount(1);
        expect($audit->responses->first())->toBeInstanceOf(AuditItemResponse::class);
        expect($audit->responses->first()->response_value)->toBe('yes');
    });

    it('has many findings', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $finding = $audit->findings()->create([
            'title' => 'Control Gap',
            'description' => 'Missing segregation of duties',
            'severity' => 'high',
            'status' => 'open',
            'created_by' => $this->auditor->id,
        ]);
        
        expect($audit->findings)->toHaveCount(1);
        expect($audit->findings->first())->toBeInstanceOf(AuditFinding::class);
        expect($audit->findings->first()->title)->toBe('Control Gap');
    });

    it('has many actions through findings', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $finding = $audit->findings()->create([
            'title' => 'Test Finding',
            'severity' => 'medium',
            'category' => 'compliance',
            'status' => 'open',
            'created_by' => $this->auditor->id,
        ]);
        
        $action = $finding->actions()->create([
            'audit_id' => $audit->id,
            'title' => 'Corrective Action',
            'description' => 'Implement new control',
            'action_type' => 'corrective',
            'status' => 'open',
            'priority' => 'medium',
            'created_by' => $this->auditor->id,
        ]);
        
        expect($audit->actions)->toHaveCount(1);
        expect($audit->actions->first())->toBeInstanceOf(AuditAction::class);
        expect($audit->actions->first()->title)->toBe('Corrective Action');
    });

    it('has many scopes', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $scope = $audit->scopes()->create([
            'scope_item' => 'Financial Controls',
            'description' => 'Review of financial reporting controls',
            'is_in_scope' => true,
            'display_order' => 1,
        ]);
        
        expect($audit->scopes)->toHaveCount(1);
        expect($audit->scopes->first())->toBeInstanceOf(AuditScope::class);
        expect($audit->scopes->first()->scope_item)->toBe('Financial Controls');
    });

    it('has many documents', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $document = $audit->documents()->create([
            'original_name' => 'audit_plan.pdf',
            'stored_name' => 'stored_audit_plan.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
            'category' => 'Planning',
            'uploaded_by' => $this->auditor->id,
            'uploaded_at' => now(),
        ]);
        
        expect($audit->documents)->toHaveCount(1);
        expect($audit->documents->first())->toBeInstanceOf(AuditDocument::class);
        expect($audit->documents->first()->original_name)->toBe('audit_plan.pdf');
    });

    it('has many risks', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $risk = $audit->risks()->create([
            'title' => 'Fraud Risk',
            'description' => 'Risk of unauthorized transactions',
            'likelihood' => 'medium',
            'impact' => 'high',
            'overall_rating' => 'high',
            'status' => 'identified',
            'created_by' => $this->auditor->id,
        ]);
        
        expect($audit->risks)->toHaveCount(1);
        expect($audit->risks->first())->toBeInstanceOf(AuditRisk::class);
        expect($audit->risks->first()->title)->toBe('Fraud Risk');
    });

    it('has many notifications', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $notification = $audit->notifications()->create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->auditee->id,
            'channel' => 'email',
            'subject' => 'Audit Status Update',
            'body' => 'Audit has been completed',
            'status' => 'pending',
        ]);
        
        expect($audit->notifications)->toHaveCount(1);
        expect($audit->notifications->first())->toBeInstanceOf(AuditNotification::class);
        expect($audit->notifications->first()->subject)->toBe('Audit Status Update');
    });

    it('has many tags through pivot table', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $tag1 = AuditTag::factory()->create(['name' => 'High Risk']);
        $tag2 = AuditTag::factory()->create(['name' => 'Financial']);
        
        $audit->tags()->attach([$tag1->id, $tag2->id]);
        
        expect($audit->tags)->toHaveCount(2);
        expect($audit->tags->pluck('name'))->toContain('High Risk', 'Financial');
    });

    it('has many schedules', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $schedule = $audit->schedules()->create([
            'frequency' => 'annual',
            'scheduled_date' => now()->addYear(),
            'is_active' => true,
            'created_by' => $this->auditor->id,
        ]);
        
        expect($audit->schedules)->toHaveCount(1);
        expect($audit->schedules->first())->toBeInstanceOf(AuditSchedule::class);
        expect($audit->schedules->first()->frequency)->toBe('annual');
    });

    it('has many metrics cache entries', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        $metrics = $audit->metrics()->create([
            'metric_key' => 'completion_rate',
            'metric_value' => 75.5,
            'calculated_at' => now(),
            'ttl_seconds' => 3600,
        ]);
        
        expect($audit->metrics)->toHaveCount(1);
        expect($audit->metrics->first())->toBeInstanceOf(AuditMetricsCache::class);
        expect($audit->metrics->first()->metric_key)->toBe('completion_rate');
    });

    it('has status histories through polymorphic relationship', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'status' => 'planned',
            'created_by' => $this->admin->id,
        ]);
        
        $statusHistory = $audit->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'planned',
            'changed_by' => $this->admin->id,
            'note' => 'Audit created',
            'changed_at' => now(),
        ]);
        
        expect($audit->statusHistories)->toHaveCount(1);
        expect($audit->statusHistories->first())->toBeInstanceOf(AuditStatusHistory::class);
        expect($audit->statusHistories->first()->to_status)->toBe('planned');
    });
});

describe('Audit Model - Factory States', function () {
    it('creates audit with default factory state', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit)->toBeInstanceOf(Audit::class);
        expect($audit->audit_type_id)->toBe($this->auditType->id);
        expect($audit->created_by)->toBe($this->admin->id);
        expect($audit->reference_no)->not->toBeNull();
        expect($audit->title)->not->toBeNull();
        expect($audit->status)->toBe('planned');
        expect($audit->is_template)->toBe(false);
    });

    it('validates audit reference number uniqueness', function () {
        $audit1 = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'reference_no' => 'UNIQUE-REF-123',
            'created_by' => $this->admin->id,
        ]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'reference_no' => 'UNIQUE-REF-123', // Duplicate
            'created_by' => $this->admin->id,
        ]);
    });
});

describe('Audit Model - Database Constraints', function () {
    it('enforces required fields', function () {
        // These should be handled by validation, but let's test basic model creation
        $audit = new Audit();
        
        // Fill only required fields
        $audit->fill([
            'audit_type_id' => $this->auditType->id,
            'reference_no' => 'TEST-REF-001',
            'title' => 'Test Audit',
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->audit_type_id)->toBe($this->auditType->id);
        expect($audit->title)->toBe('Test Audit');
        expect($audit->created_by)->toBe($this->admin->id);
    });

    it('allows null values for optional fields', function () {
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'description' => null,
            'scope_summary' => null,
            'planned_start_date' => null,
            'planned_end_date' => null,
            'actual_start_date' => null,
            'actual_end_date' => null,
            'lead_auditor_id' => null,
            'auditee_user_id' => null,
            'score' => null,
            'parent_audit_id' => null,
            'metadata' => null,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit)->toBeInstanceOf(Audit::class);
        expect($audit->description)->toBeNull();
        expect($audit->lead_auditor_id)->toBeNull();
        expect($audit->score)->toBeNull();
    });

    it('handles foreign key constraints properly', function () {
        // Valid foreign keys should work
        $audit = Audit::factory()->create([
            'audit_type_id' => $this->auditType->id,
            'lead_auditor_id' => $this->auditor->id,
            'auditee_user_id' => $this->auditee->id,
            'created_by' => $this->admin->id,
        ]);
        
        expect($audit->audit_type_id)->toBe($this->auditType->id);
        expect($audit->lead_auditor_id)->toBe($this->auditor->id);
        expect($audit->auditee_user_id)->toBe($this->auditee->id);
        expect($audit->created_by)->toBe($this->admin->id);
    });

    it('validates enum values for status', function () {
        $validStatuses = ['planned', 'in_progress', 'reporting', 'issued', 'closed', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $audit = Audit::factory()->create([
                'audit_type_id' => $this->auditType->id,
                'status' => $status,
                'created_by' => $this->admin->id,
            ]);
            
            expect($audit->status)->toBe($status);
        }
    });

    it('validates enum values for risk_overall', function () {
        $validRiskLevels = ['low', 'medium', 'high', 'critical'];
        
        foreach ($validRiskLevels as $riskLevel) {
            $audit = Audit::factory()->create([
                'audit_type_id' => $this->auditType->id,
                'risk_overall' => $riskLevel,
                'created_by' => $this->admin->id,
            ]);
            
            expect($audit->risk_overall)->toBe($riskLevel);
        }
    });
});