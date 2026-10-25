<?php

use App\Models\AksicApplication;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->ownBranch = Branch::factory()->create(['code' => '0001']);
    $this->otherBranch = Branch::factory()->create(['code' => '0029']);

    $role = Role::firstOrCreate(['name' => 'branch', 'guard_name' => 'web']);
    $role->givePermissionTo('view aksic applications');

    $this->branchUser = User::factory()->create(['branch_id' => $this->ownBranch->id]);
    $this->branchUser->assignRole($role);

    $this->own = AksicApplication::factory()->create(['branch_id' => $this->ownBranch->id]);
    $this->unlinkedOwn = AksicApplication::factory()->create(['branch_id' => null, 'applicant_choosed_branch_code' => '0001']);
    $this->foreign = AksicApplication::factory()->create(['branch_id' => $this->otherBranch->id]);
});

test('a branch user lists only the applications of their own branch', function () {
    $response = $this->actingAs($this->branchUser)->get(route('aksic-applications.index'))->assertSuccessful();

    $response->assertSee($this->own->application_no)
        ->assertSee($this->unlinkedOwn->application_no)
        ->assertDontSee($this->foreign->application_no);
});

test('a branch user cannot open or download the application of another branch', function () {
    $this->actingAs($this->branchUser)->get(route('aksic-applications.show', $this->own))->assertSuccessful();
    $this->actingAs($this->branchUser)->get(route('aksic-applications.show', $this->foreign))->assertNotFound();
    $this->actingAs($this->branchUser)->get(route('aksic-applications.pdf', $this->foreign))->assertNotFound();
});

test('a user with the view all permission sees every branch', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view aksic applications', 'view all aksic cases']);

    $this->actingAs($user)->get(route('aksic-applications.index'))
        ->assertSuccessful()
        ->assertSee($this->foreign->application_no);
});

test('the application status accepts In Progress', function () {
    $this->own->update(['status' => 'In Progress']);

    expect($this->own->fresh()->status)->toBe('In Progress');
});

test('a sync links each application to its branch by code and reports unknown codes', function () {
    config(['app.aksic_api_token' => 'test-token', 'app.aksic_download_images' => false]);

    $application = fn (int $id, ?string $branchCode, array $educations = []) => [
        'id' => $id, 'name' => 'Applicant '.$id, 'fatherName' => 'Father', 'cnic' => '3520000000'.$id,
        'application_no' => 'AKSIC-'.$id, 'cnic_issue_date' => null, 'dob' => null, 'phone' => null,
        'businessName' => null, 'businessType' => null, 'quota' => null, 'businessAddress' => null,
        'permanentAddress' => null, 'business_category_id' => null, 'business_sub_category_id' => null,
        'tier' => null, 'amount' => null, 'district_id' => null, 'tehsil_id' => null,
        'applicant_choosed_branch' => null, 'branch_id' => null, 'challan_branch_id' => null,
        'challan_fee' => null, 'challan_image' => null, 'cnic_front' => null, 'cnic_back' => null,
        'fee_status' => 'unpaid', 'status' => 'In Progress', 'bank_status' => null,
        'applicant_choosed_branch_code' => $branchCode, 'challan_branch_code' => null,
        'educations' => $educations, 'status_logs' => [],
    ];
    $education = ['education_level' => 'BS', 'degree_title' => 'CS', 'institute' => 'AJK', 'passing_year' => '2020', 'grade_or_percentage' => 'A'];

    Http::fake(fn () => Http::response([
        'success' => true,
        'data' => ['count' => 2, 'applications' => [
            $application(901, '0029', [$education, $education]),
            $application(902, '0088'),
        ]],
    ]));

    $user = User::factory()->create();
    $user->givePermissionTo('sync aksic applications');

    $this->actingAs($user)->post(route('aksic-applications.sync'))
        ->assertSuccessful()
        ->assertJsonPath('results.created', 2)
        ->assertJsonPath('results.unlinked_branches.0.branch_code', '0088');

    $linked = AksicApplication::where('applicant_id', 901)->first();
    expect($linked->branch_id)->toBe($this->otherBranch->id)
        ->and($linked->status)->toBe('In Progress')
        ->and($linked->educations()->count())->toBe(1)
        ->and(AksicApplication::where('applicant_id', 902)->value('branch_id'))->toBeNull();
});
