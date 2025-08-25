<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Division;
use App\Models\District;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\ComplaintComment;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintEscalation;
use App\Models\ComplaintWatcher;
use App\Models\ComplaintMetric;
use App\Models\ComplaintWitness;

beforeEach(function () {
    // Create test users and organizational structure
    $this->admin = User::factory()->create();
    $this->user = User::factory()->create();
    $this->assignee = User::factory()->create();
    
    $this->region = Region::factory()->create();
    $this->district = District::factory()->create(['region_id' => $this->region->id]);
    $this->division = Division::factory()->create();
    $this->branch = Branch::factory()->create([
        'region_id' => $this->region->id,
        'district_id' => $this->district->id
    ]);
});

describe('Complaint Model - Basic Properties', function () {
    it('uses UUID as primary key', function () {
        $complaint = Complaint::factory()->create();
        
        expect($complaint->getKeyType())->toBe('string');
        expect($complaint->getIncrementing())->toBe(false);
        expect(strlen($complaint->id))->toBe(36); // UUID length
    });

    it('auto-generates complaint number on creation', function () {
        $complaint = Complaint::factory()->create(['complaint_number' => null]);
        
        expect($complaint->complaint_number)->not->toBeNull();
        expect($complaint->complaint_number)->toMatch('/^[A-Z0-9\-]+$/');
    });

    it('preserves provided complaint number', function () {
        $customNumber = 'CUSTOM-123';
        $complaint = Complaint::factory()->create(['complaint_number' => $customNumber]);
        
        expect($complaint->complaint_number)->toBe($customNumber);
    });

    it('has correct fillable attributes', function () {
        $expectedFillable = [
            'complaint_number',
            'title',
            'description',
            'category',
            'priority',
            'status',
            'source',
            'complainant_name',
            'complainant_email',
            'complainant_phone',
            'complainant_account_number',
            'branch_id',
            'region_id',
            'division_id',
            'assigned_to',
            'assigned_by',
            'assigned_at',
            'resolution',
            'resolved_by',
            'resolved_at',
            'closed_at',
            'expected_resolution_date',
            'sla_breached',
            'reopen_reason',
            'priority_change_reason',
            'status_change_reason',
            // Harassment specific fields
            'harassment_incident_date',
            'harassment_location',
            'harassment_witnesses',
            'harassment_reported_to',
            'harassment_details',
            'harassment_confidential',
            'harassment_sub_category',
            'harassment_employee_number',
            'harassment_employee_phone',
            'harassment_abuser_employee_number',
            'harassment_abuser_name',
            'harassment_abuser_phone',
            'harassment_abuser_email',
            'harassment_abuser_relationship',
            // Grievance specific fields
            'grievance_employee_id',
            'grievance_department_position',
            'grievance_supervisor_name',
            'grievance_employment_start_date',
            'grievance_type',
            'grievance_policy_violated',
            'grievance_previous_attempts',
            'grievance_previous_attempts_details',
            'grievance_desired_outcome',
            'grievance_subject_name',
            'grievance_subject_position',
            'grievance_subject_relationship',
            'grievance_union_representation',
            'grievance_anonymous',
            'grievance_acknowledgment',
            'grievance_first_occurred_date',
            'grievance_most_recent_date',
            'grievance_pattern_frequency',
            'grievance_performance_effect',
        ];
        
        $complaint = new Complaint();
        expect($complaint->getFillable())->toMatchArray($expectedFillable);
    });

    it('has correct cast attributes', function () {
        $expectedCasts = [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'expected_resolution_date' => 'datetime',
            'sla_breached' => 'boolean',
            'harassment_incident_date' => 'datetime',
            'harassment_confidential' => 'boolean',
            'grievance_union_representation' => 'boolean',
            'grievance_anonymous' => 'boolean',
            'grievance_acknowledgment' => 'boolean',
            'grievance_employment_start_date' => 'date',
            'grievance_first_occurred_date' => 'date',
            'grievance_most_recent_date' => 'date',
        ];
        
        $complaint = new Complaint();
        $casts = $complaint->getCasts();
        
        foreach ($expectedCasts as $attribute => $expectedCast) {
            expect($casts[$attribute])->toBe($expectedCast);
        }
    });
});

describe('Complaint Model - Relationships', function () {
    it('belongs to a branch', function () {
        $complaint = Complaint::factory()->create(['branch_id' => $this->branch->id]);
        
        expect($complaint->branch)->toBeInstanceOf(Branch::class);
        expect($complaint->branch->id)->toBe($this->branch->id);
    });

    it('belongs to a region', function () {
        $complaint = Complaint::factory()->create(['region_id' => $this->region->id]);
        
        expect($complaint->region)->toBeInstanceOf(Region::class);
        expect($complaint->region->id)->toBe($this->region->id);
    });

    it('belongs to a division', function () {
        $complaint = Complaint::factory()->create(['division_id' => $this->division->id]);
        
        expect($complaint->division)->toBeInstanceOf(Division::class);
        expect($complaint->division->id)->toBe($this->division->id);
    });

    it('belongs to assigned user', function () {
        $complaint = Complaint::factory()->create(['assigned_to' => $this->assignee->id]);
        
        expect($complaint->assignedTo)->toBeInstanceOf(User::class);
        expect($complaint->assignedTo->id)->toBe($this->assignee->id);
    });

    it('belongs to assigned by user', function () {
        $complaint = Complaint::factory()->create(['assigned_by' => $this->admin->id]);
        
        expect($complaint->assignedBy)->toBeInstanceOf(User::class);
        expect($complaint->assignedBy->id)->toBe($this->admin->id);
    });

    it('belongs to resolved by user', function () {
        $complaint = Complaint::factory()->create([
            'resolved_by' => $this->user->id,
            'resolved_at' => now(),
        ]);
        
        expect($complaint->resolvedBy)->toBeInstanceOf(User::class);
        expect($complaint->resolvedBy->id)->toBe($this->user->id);
    });

    it('has many histories', function () {
        $complaint = Complaint::factory()->create();
        
        // Create a complaint status type first
        $statusType = \App\Models\ComplaintStatusType::factory()->create([
            'name' => 'Status Change',
            'code' => 'SC',
            'is_active' => true,
        ]);
        
        $history = $complaint->histories()->create([
            'action_type' => 'Status Changed',
            'old_value' => 'Open',
            'new_value' => 'In Progress',
            'status_id' => $statusType->id,
            'performed_by' => $this->admin->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        
        expect($complaint->histories)->toHaveCount(1);
        expect($complaint->histories->first())->toBeInstanceOf(ComplaintHistory::class);
        expect($complaint->histories->first()->action_type)->toBe('Status Changed');
    });

    it('has many comments', function () {
        $complaint = Complaint::factory()->create();
        
        $comment = $complaint->comments()->create([
            'comment_text' => 'Test comment',
            'comment_type' => 'Internal',
            'is_private' => false,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        
        expect($complaint->comments)->toHaveCount(1);
        expect($complaint->comments->first())->toBeInstanceOf(ComplaintComment::class);
        expect($complaint->comments->first()->comment_text)->toBe('Test comment');
    });

    it('has many attachments', function () {
        $complaint = Complaint::factory()->create();
        
        $attachment = $complaint->attachments()->create([
            'file_name' => 'test.pdf',
            'file_path' => 'complaints/test.pdf',
            'file_size' => 1024,
            'file_type' => 'application/pdf',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        
        expect($complaint->attachments)->toHaveCount(1);
        expect($complaint->attachments->first())->toBeInstanceOf(ComplaintAttachment::class);
        expect($complaint->attachments->first()->file_name)->toBe('test.pdf');
    });

    it('has many escalations', function () {
        $complaint = Complaint::factory()->create();
        
        $escalation = $complaint->escalations()->create([
            'escalated_from' => $this->user->id,
            'escalated_to' => $this->admin->id,
            'escalation_level' => 1,
            'escalation_reason' => 'Urgent review needed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        
        expect($complaint->escalations)->toHaveCount(1);
        expect($complaint->escalations->first())->toBeInstanceOf(ComplaintEscalation::class);
        expect($complaint->escalations->first()->escalation_reason)->toBe('Urgent review needed');
    });

    it('has many watchers', function () {
        $complaint = Complaint::factory()->create();
        
        $watcher = $complaint->watchers()->create([
            'user_id' => $this->admin->id,
            'added_by' => $this->user->id,
        ]);
        
        expect($complaint->watchers)->toHaveCount(1);
        expect($complaint->watchers->first())->toBeInstanceOf(ComplaintWatcher::class);
        expect($complaint->watchers->first()->user_id)->toBe($this->admin->id);
    });

    it('has one metrics', function () {
        $complaint = Complaint::factory()->create();
        
        $metrics = $complaint->metrics()->create([
            'time_to_first_response' => 60, // 1 hour in minutes
            'time_to_resolution' => 1440, // 24 hours in minutes
            'escalation_count' => 1,
            'assignment_count' => 3,
            'reopened_count' => 2,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        
        expect($complaint->metrics)->toBeInstanceOf(ComplaintMetric::class);
        expect($complaint->metrics->time_to_first_response)->toBe(60);
    });

    it('has many witnesses', function () {
        $complaint = Complaint::factory()->create();
        
        $witness = $complaint->witnesses()->create([
            'name' => 'John Witness',
            'email' => 'john.witness@example.com',
            'phone' => '+1-555-987-6543',
            'statement' => 'I witnessed the incident',
            'added_by' => $this->admin->id,
        ]);
        
        expect($complaint->witnesses)->toHaveCount(1);
        expect($complaint->witnesses->first())->toBeInstanceOf(ComplaintWitness::class);
        expect($complaint->witnesses->first()->name)->toBe('John Witness');
    });
});

describe('Complaint Model - Scopes', function () {
    it('filters complaints created between dates', function () {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);
        
        $complaint1 = Complaint::factory()->create(['created_at' => $startDate->addDays(2)]);
        $complaint2 = Complaint::factory()->create(['created_at' => now()->subDays(15)]);
        $complaint3 = Complaint::factory()->create(['created_at' => now()]);
        
        $results = Complaint::createdBetween([$startDate, $endDate])->get();
        
        expect($results)->toHaveCount(1);
        expect($results->first()->id)->toBe($complaint1->id);
    });

    it('filters complaints resolved between dates', function () {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);
        
        $complaint1 = Complaint::factory()->create(['resolved_at' => $startDate->addDays(2)]);
        $complaint2 = Complaint::factory()->create(['resolved_at' => now()->subDays(15)]);
        $complaint3 = Complaint::factory()->create(['resolved_at' => null]);
        
        $results = Complaint::resolvedBetween([$startDate, $endDate])->get();
        
        expect($results)->toHaveCount(1);
        expect($results->first()->id)->toBe($complaint1->id);
    });

    it('filters complaints assigned between dates', function () {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);
        
        $complaint1 = Complaint::factory()->create(['assigned_at' => $startDate->addDays(2)]);
        $complaint2 = Complaint::factory()->create(['assigned_at' => now()->subDays(15)]);
        $complaint3 = Complaint::factory()->create(['assigned_at' => null]);
        
        $results = Complaint::assignedBetween([$startDate, $endDate])->get();
        
        expect($results)->toHaveCount(1);
        expect($results->first()->id)->toBe($complaint1->id);
    });
});

describe('Complaint Model - Helper Methods', function () {
    it('detects overdue complaints', function () {
        $overdueComplaint = Complaint::factory()->create([
            'expected_resolution_date' => now()->subDays(5),
            'resolved_at' => null,
        ]);
        
        $onTimeComplaint = Complaint::factory()->create([
            'expected_resolution_date' => now()->addDays(5),
            'resolved_at' => null,
        ]);
        
        $resolvedComplaint = Complaint::factory()->create([
            'expected_resolution_date' => now()->subDays(5),
            'resolved_at' => now()->subDays(2),
        ]);
        
        expect($overdueComplaint->isOverdue())->toBe(true);
        expect($onTimeComplaint->isOverdue())->toBe(false);
        expect($resolvedComplaint->isOverdue())->toBe(false);
    });

    it('detects resolved complaints', function () {
        $openComplaint = Complaint::factory()->create(['status' => 'Open']);
        $inProgressComplaint = Complaint::factory()->create(['status' => 'In Progress']);
        $resolvedComplaint = Complaint::factory()->create(['status' => 'Resolved']);
        $closedComplaint = Complaint::factory()->create(['status' => 'Closed']);
        
        expect($openComplaint->isResolved())->toBe(false);
        expect($inProgressComplaint->isResolved())->toBe(false);
        expect($resolvedComplaint->isResolved())->toBe(true);
        expect($closedComplaint->isResolved())->toBe(true);
    });
});

describe('Complaint Model - Factory States', function () {
    it('creates harassment complaint with factory', function () {
        $complaint = Complaint::factory()->harassment()->create();
        
        expect($complaint->category)->toBe('Harassment');
        expect($complaint->harassment_incident_date)->not->toBeNull();
        expect($complaint->harassment_location)->not->toBeNull();
        expect($complaint->harassment_sub_category)->not->toBeNull();
    });

    it('creates grievance complaint with factory', function () {
        $complaint = Complaint::factory()->grievance()->create();
        
        expect($complaint->category)->toBe('Grievance');
        expect($complaint->grievance_employee_id)->not->toBeNull();
        expect($complaint->grievance_type)->not->toBeNull();
        expect($complaint->grievance_acknowledgment)->toBe(true);
    });

    it('creates assigned complaint with factory', function () {
        $complaint = Complaint::factory()->assigned()->create();
        
        expect($complaint->assigned_at)->not->toBeNull();
    });

    it('creates overdue complaint with factory', function () {
        $complaint = Complaint::factory()->overdue()->create();
        
        expect($complaint->status)->toBe('In Progress');
        expect($complaint->expected_resolution_date)->toBeLessThan(now());
        expect($complaint->resolved_at)->toBeNull();
        expect($complaint->sla_breached)->toBe(true);
    });

    it('creates critical priority complaint with factory', function () {
        $complaint = Complaint::factory()->critical()->create();
        
        expect($complaint->priority)->toBe('Critical');
        expect($complaint->expected_resolution_date)->toBeGreaterThan(now());
    });

    it('creates resolved complaint with factory', function () {
        $complaint = Complaint::factory()->resolved()->create();
        
        expect($complaint->status)->toBe('Resolved');
        expect($complaint->resolution)->not->toBeNull();
        expect($complaint->resolved_at)->not->toBeNull();
    });

    it('creates closed complaint with factory', function () {
        $complaint = Complaint::factory()->closed()->create();
        
        expect($complaint->status)->toBe('Closed');
        expect($complaint->resolution)->not->toBeNull();
        expect($complaint->resolved_at)->not->toBeNull();
        expect($complaint->closed_at)->not->toBeNull();
    });
});

describe('Complaint Model - Database Constraints', function () {
    it('enforces unique complaint number', function () {
        $complaint1 = Complaint::factory()->create(['complaint_number' => 'UNIQUE-123']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        Complaint::factory()->create(['complaint_number' => 'UNIQUE-123']);
    });

    it('allows null values for optional fields', function () {
        $complaint = Complaint::factory()->create([
            'complainant_name' => null,
            'complainant_email' => null,
            'complainant_phone' => null,
            'branch_id' => null,
            'assigned_to' => null,
            'resolved_at' => null,
        ]);
        
        expect($complaint)->toBeInstanceOf(Complaint::class);
        expect($complaint->complainant_name)->toBeNull();
        expect($complaint->branch_id)->toBeNull();
        expect($complaint->assigned_to)->toBeNull();
    });

    it('handles foreign key constraints properly', function () {
        // Valid foreign keys should work
        $complaint = Complaint::factory()->create([
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->user->id,
        ]);
        
        expect($complaint->branch_id)->toBe($this->branch->id);
        expect($complaint->assigned_to)->toBe($this->user->id);
    });
});