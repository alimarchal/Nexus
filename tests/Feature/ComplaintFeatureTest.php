<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Division;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\ComplaintComment;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintEscalation;
use App\Models\ComplaintWatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    
    // Create test users
    $this->admin = User::factory()->create(['name' => 'Admin User']);
    $this->user = User::factory()->create(['name' => 'Regular User']);
    $this->assignee = User::factory()->create(['name' => 'Assignee User']);
    
    // Create organizational structure
    $this->region = Region::factory()->create(['name' => 'Test Region']);
    $this->division = Division::factory()->create(['name' => 'Test Division']);
    $this->branch = Branch::factory()->create([
        'name' => 'Test Branch',
        'region_id' => $this->region->id
    ]);
});

describe('Complaint Controller - CRUD Operations', function () {
    it('lists all complaints with filtering', function () {
        // Create test complaints
        $complaint1 = Complaint::factory()->create([
            'title' => 'Service Issue',
            'status' => 'Open',
            'priority' => 'High',
            'category' => 'Service Quality',
            'branch_id' => $this->branch->id,
        ]);
        
        $complaint2 = Complaint::factory()->create([
            'title' => 'Billing Problem',
            'status' => 'In Progress',
            'priority' => 'Medium',
            'category' => 'Billing',
        ]);
        
        $this->actingAs($this->admin);
        
        // Test basic index
        $response = $this->get(route('complaints.index'));
        $response->assertStatus(200);
        
        // Test filtering by status
        $response = $this->get(route('complaints.index', ['filter[status]' => 'Open']));
        $response->assertStatus(200);
        
        // Test filtering by priority
        $response = $this->get(route('complaints.index', ['filter[priority]' => 'High']));
        $response->assertStatus(200);
        
        // Test filtering by category
        $response = $this->get(route('complaints.index', ['filter[category]' => 'Service Quality']));
        $response->assertStatus(200);
        
        // Test filtering by branch
        $response = $this->get(route('complaints.index', ['filter[branch_id]' => $this->branch->id]));
        $response->assertStatus(200);
    });

    it('shows a single complaint with details', function () {
        $complaint = Complaint::factory()->create([
            'title' => 'Test Complaint Details',
            'description' => 'Detailed description of the complaint',
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->assignee->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->get(route('complaints.show', $complaint));
        $response->assertStatus(200);
    });

    it('creates a new complaint with all required fields', function () {
        $this->actingAs($this->admin);
        
        $complaintData = [
            'title' => 'New Service Issue',
            'description' => 'Detailed description of the service issue',
            'category' => 'Service Quality',
            'priority' => 'High',
            'source' => 'Email',
            'complainant_name' => 'John Doe',
            'complainant_email' => 'john.doe@example.com',
            'complainant_phone' => '+1-555-123-4567',
            'complainant_account_number' => 'ACC-12345678',
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->assignee->id,
            'expected_resolution_date' => now()->addDays(7)->toDateString(),
        ];
        
        $response = $this->post(route('complaints.store'), $complaintData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('complaints', [
            'title' => 'New Service Issue',
            'category' => 'Service Quality',
            'priority' => 'High',
            'status' => 'Open', // Default status
            'complainant_name' => 'John Doe',
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->assignee->id,
            'assigned_by' => $this->admin->id,
        ]);
        
        // Verify complaint number was auto-generated
        $complaint = Complaint::where('title', 'New Service Issue')->first();
        expect($complaint->complaint_number)->not->toBeNull();
        expect($complaint->assigned_at)->not->toBeNull();
    });

    it('creates a harassment complaint with specific fields', function () {
        $this->actingAs($this->admin);
        
        $harassmentData = [
            'title' => 'Workplace Harassment Report',
            'description' => 'Detailed harassment incident description',
            'category' => 'Harassment',
            'priority' => 'Critical',
            'source' => 'Walk-in',
            'complainant_name' => 'Jane Smith',
            'complainant_email' => 'jane.smith@example.com',
            // Harassment specific fields
            'harassment_incident_date' => now()->subDays(3)->toDateString(),
            'harassment_location' => 'Office Building A, Floor 3',
            'harassment_witnesses' => 'Mike Johnson, Sarah Wilson',
            'harassment_reported_to' => 'HR Manager',
            'harassment_details' => 'Detailed harassment incident description',
            'harassment_confidential' => true,
            'harassment_sub_category' => 'Verbal',
            'harassment_employee_number' => 'EMP-9876',
            'harassment_abuser_name' => 'Accused Person',
            'harassment_abuser_relationship' => 'Supervisor',
        ];
        
        $response = $this->post(route('complaints.store'), $harassmentData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('complaints', [
            'title' => 'Workplace Harassment Report',
            'category' => 'Harassment',
            'harassment_confidential' => true,
            'harassment_sub_category' => 'Verbal',
            'harassment_employee_number' => 'EMP-9876',
        ]);
    });

    it('updates an existing complaint', function () {
        $complaint = Complaint::factory()->create([
            'title' => 'Original Title',
            'status' => 'Open',
            'priority' => 'Medium',
        ]);
        
        $this->actingAs($this->admin);
        
        $updateData = [
            'title' => 'Updated Title',
            'status' => 'In Progress',
            'priority' => 'High',
            'resolution' => 'Partial resolution details',
        ];
        
        $response = $this->put(route('complaints.update', $complaint), $updateData);
        
        $response->assertRedirect();
        
        $complaint->refresh();
        expect($complaint->title)->toBe('Updated Title');
        expect($complaint->status)->toBe('In Progress');
        expect($complaint->priority)->toBe('High');
        expect($complaint->resolution)->toBe('Partial resolution details');
    });

    it('deletes a complaint', function () {
        $complaint = Complaint::factory()->create();
        
        $this->actingAs($this->admin);
        
        $response = $this->delete(route('complaints.destroy', $complaint));
        
        $response->assertRedirect();
        
        // Should be soft deleted
        $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
    });
});

describe('Complaint Controller - File Attachments', function () {
    it('adds attachments to a complaint', function () {
        $complaint = Complaint::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.add-attachments', $complaint), [
            'attachments' => [$file]
        ]);
        
        $response->assertRedirect();
        expect($complaint->attachments()->count())->toBe(1);
    });

    it('downloads an attachment', function () {
        $complaint = Complaint::factory()->create();
        $attachment = $complaint->attachments()->create([
            'original_name' => 'test.pdf',
            'file_path' => 'test/path.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->admin->id,
        ]);
        
        Storage::put($attachment->file_path, 'test content');
        
        $this->actingAs($this->admin);
        
        $response = $this->get(route('complaints.download-attachment', $attachment));
        $response->assertStatus(200);
    });

    it('deletes an attachment', function () {
        $complaint = Complaint::factory()->create();
        $attachment = $complaint->attachments()->create([
            'original_name' => 'test.pdf',
            'file_path' => 'test/path.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $this->admin->id,
        ]);
        
        $this->actingAs($this->admin);
        
        $response = $this->delete(route('complaints.delete-attachment', $attachment));
        $response->assertRedirect();
        
        $this->assertDatabaseMissing('complaint_attachments', ['id' => $attachment->id]);
    });
});

describe('Complaint Controller - Comments', function () {
    it('adds a comment to a complaint', function () {
        $complaint = Complaint::factory()->create();
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.add-comment', $complaint), [
            'comment' => 'This is a test comment'
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('complaint_comments', [
            'complaint_id' => $complaint->id,
            'comment' => 'This is a test comment',
            'commented_by' => $this->admin->id,
        ]);
    });
});

describe('Complaint Controller - Bulk Operations', function () {
    it('performs bulk status update', function () {
        $complaint1 = Complaint::factory()->create(['status' => 'Open']);
        $complaint2 = Complaint::factory()->create(['status' => 'Open']);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.bulk-update'), [
            'operation_type' => 'status_update',
            'complaint_ids' => [$complaint1->id, $complaint2->id],
            'status' => 'In Progress',
        ]);
        
        $response->assertRedirect();
        
        $complaint1->refresh();
        $complaint2->refresh();
        
        expect($complaint1->status)->toBe('In Progress');
        expect($complaint2->status)->toBe('In Progress');
    });

    it('performs bulk assignment', function () {
        $complaint1 = Complaint::factory()->create(['assigned_to' => null]);
        $complaint2 = Complaint::factory()->create(['assigned_to' => null]);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.bulk-update'), [
            'operation_type' => 'assignment',
            'complaint_ids' => [$complaint1->id, $complaint2->id],
            'assigned_to' => $this->assignee->id,
        ]);
        
        $response->assertRedirect();
        
        $complaint1->refresh();
        $complaint2->refresh();
        
        expect($complaint1->assigned_to)->toBe($this->assignee->id);
        expect($complaint2->assigned_to)->toBe($this->assignee->id);
        expect($complaint1->assigned_at)->not->toBeNull();
        expect($complaint2->assigned_at)->not->toBeNull();
    });
});

describe('Complaint Controller - Escalations', function () {
    it('escalates a complaint', function () {
        $complaint = Complaint::factory()->create();
        $supervisor = User::factory()->create(['name' => 'Supervisor']);
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.escalate', $complaint), [
            'escalated_to' => $supervisor->id,
            'escalation_reason' => 'Needs supervisor attention',
            'escalation_level' => 1,
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('complaint_escalations', [
            'complaint_id' => $complaint->id,
            'escalated_to' => $supervisor->id,
            'escalation_reason' => 'Needs supervisor attention',
            'escalation_level' => 1,
            'escalated_by' => $this->admin->id,
        ]);
    });
});

describe('Complaint Controller - Watchers', function () {
    it('updates watchers for a complaint', function () {
        $complaint = Complaint::factory()->create();
        $watcher1 = User::factory()->create();
        $watcher2 = User::factory()->create();
        
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.update-watchers', $complaint), [
            'watcher_ids' => [$watcher1->id, $watcher2->id]
        ]);
        
        $response->assertRedirect();
        
        expect($complaint->watchers()->count())->toBe(2);
        $this->assertDatabaseHas('complaint_watchers', [
            'complaint_id' => $complaint->id,
            'user_id' => $watcher1->id,
        ]);
        $this->assertDatabaseHas('complaint_watchers', [
            'complaint_id' => $complaint->id,
            'user_id' => $watcher2->id,
        ]);
    });
});

describe('Complaint Controller - Authorization', function () {
    it('requires authentication for complaint operations', function () {
        $complaint = Complaint::factory()->create();
        
        // Test unauthenticated access
        $this->get(route('complaints.index'))->assertRedirect();
        $this->get(route('complaints.show', $complaint))->assertRedirect();
        $this->post(route('complaints.store'), [])->assertRedirect();
        $this->put(route('complaints.update', $complaint), [])->assertRedirect();
        $this->delete(route('complaints.destroy', $complaint))->assertRedirect();
    });
});

describe('Complaint Controller - Validation', function () {
    it('validates required fields when creating complaint', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.store'), []);
        
        $response->assertSessionHasErrors();
    });

    it('validates email format in complainant_email', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.store'), [
            'title' => 'Test Complaint',
            'complainant_email' => 'invalid-email',
        ]);
        
        $response->assertSessionHasErrors(['complainant_email']);
    });

    it('validates date format in expected_resolution_date', function () {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('complaints.store'), [
            'title' => 'Test Complaint',
            'expected_resolution_date' => 'invalid-date',
        ]);
        
        $response->assertSessionHasErrors(['expected_resolution_date']);
    });

    it('validates status enum values', function () {
        $complaint = Complaint::factory()->create();
        
        $this->actingAs($this->admin);
        
        $response = $this->put(route('complaints.update', $complaint), [
            'status' => 'InvalidStatus',
        ]);
        
        $response->assertSessionHasErrors(['status']);
    });

    it('validates priority enum values', function () {
        $complaint = Complaint::factory()->create();
        
        $this->actingAs($this->admin);
        
        $response = $this->put(route('complaints.update', $complaint), [
            'priority' => 'InvalidPriority',
        ]);
        
        $response->assertSessionHasErrors(['priority']);
    });
});