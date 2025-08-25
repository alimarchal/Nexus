<?php

use App\Models\{User, Audit, AuditType, AuditDocument, AuditStatusHistory};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    // Ensure at least one user & type
    $this->user = User::first() ?? User::factory()->create();
    $this->actingAs($this->user);
    $this->type = AuditType::first() ?? AuditType::create(['name' => 'Test Type', 'code' => 'TYP', 'is_active' => true]);
});

it('creates an audit with documents and status history', function () {
    $file1 = UploadedFile::fake()->create('plan.pdf', 120, 'application/pdf');
    $file2 = UploadedFile::fake()->create('evidence.jpg', 200, 'image/jpeg');

    $response = $this->post(route('audits.store'), [
        'audit_type_id' => $this->type->id,
        'title' => 'Quarterly Controls Review',
        'planned_start_date' => now()->addDay()->toDateString(),
        'planned_end_date' => now()->addDays(5)->toDateString(),
        'risk_overall' => 'medium',
        'documents' => [$file1, $file2],
    ]);

    $response->assertRedirect();
    $audit = Audit::latest()->first();
    expect($audit)->not->toBeNull();
    expect($audit->reference_no)->not->toBeNull();
    expect($audit->documents()->count())->toBe(2);
    expect($audit->statusHistories()->count())->toBe(1);
});

it('updates audit status and logs history', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);

    $response = $this->put(route('audits.update', $audit), [
        'status' => 'in_progress'
    ]);

    $response->assertRedirect();
    $audit->refresh();
    expect($audit->status)->toBe('in_progress');
    expect($audit->statusHistories()->where('to_status', 'in_progress')->exists())->toBeTrue();
});

it('adds documents on update', function () {
    $audit = Audit::factory()->create(['audit_type_id' => $this->type->id, 'created_by' => $this->user->id, 'reference_no' => generateUniqueId('audit', 'audits', 'reference_no')]);
    $file = UploadedFile::fake()->create('evidence2.txt', 10, 'text/plain');

    $this->put(route('audits.update', $audit), [
        'documents' => [$file]
    ])->assertRedirect();

    expect($audit->documents()->count())->toBe(1);
});

it('shows the audit details page', function () {
    $audit = Audit::factory()->create([
        'audit_type_id' => $this->type->id,
        'created_by' => $this->user->id,
        'reference_no' => generateUniqueId('audit', 'audits', 'reference_no'),
        'title' => 'Sample Audit Show Test'
    ]);

    $this->get(route('audits.show', $audit))
        ->assertStatus(200)
        ->assertSee($audit->reference_no)
        ->assertSee('Sample Audit Show Test');
});

it('returns full JSON snapshot for audit', function () {
    $audit = Audit::factory()->create([
        'audit_type_id' => $this->type->id,
        'created_by' => $this->user->id,
        'reference_no' => generateUniqueId('audit', 'audits', 'reference_no'),
        'title' => 'Snapshot Test Audit'
    ]);
    $resp = $this->get(route('audits.full', $audit));
    $resp->assertStatus(200)->assertJsonStructure(['audit' => ['id', 'reference_no', 'title'], 'exported_at', 'version']);
});
