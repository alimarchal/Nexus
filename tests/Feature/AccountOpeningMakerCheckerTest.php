<?php

use App\Models\AccountOpeningRequest;
use App\Models\AccountProduct;
use App\Models\AccountPurpose;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\CustomerCategory;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\IncomeSource;
use App\Models\OperatingInstruction;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->branch = Branch::factory()->create();
    $this->maker = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->maker->assignRole('branch');
    $this->checker = User::factory()->create();
    $this->checker->assignRole('head-office');
    $this->otherChecker = User::factory()->create();
    $this->otherChecker->assignRole('head-office');
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
});

/**
 * Switch the acting user. Jetstream's AuthenticateSession compares the session's password hash with
 * the current user's, so a second user in the same test needs a fresh session.
 */
function actAs(mixed $test, User $user): mixed
{
    $test->flushSession();

    return $test->actingAs($user);
}

function pngFile(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
}

/**
 * Start an AOF as $user and fill all six steps through the real endpoints.
 */
function completeAof(mixed $test, User $user): AccountOpeningRequest
{
    $response = actAs($test, $user)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ])->assertRedirect();

    preg_match('/account-openings\/([0-9a-f-]{36})\//', $response->headers->get('Location'), $matches);
    $request = AccountOpeningRequest::findOrFail($matches[1]);

    $put = fn (string $step, array $data) => actAs($test, $user)
        ->put(route('account-openings.steps.update', [$request, $step]), $data)
        ->assertSessionHasNoErrors();

    $put('cif', ['full_name' => 'Ali Raza', 'date_of_birth' => '1990-05-04', 'gender_id' => Gender::code('MALE')->firstOrFail()->id]);
    $put('identification', [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id, 'document_number' => '81303-1234567-1']],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3008169924']]],
    ]);
    $put('compliance', ['is_pep' => 0]);
    $put('account', [
        'title_of_account' => 'Ali Raza', 'opening_date' => now()->toDateString(),
        'account_product_id' => AccountProduct::code('PLS')->firstOrFail()->id,
        'currency_id' => Currency::code('PKR')->firstOrFail()->id,
        'operating_instruction_id' => OperatingInstruction::code('SINGLY')->firstOrFail()->id,
        'terms_and_conditions_accepted' => 1,
    ]);
    $put('holders', ['holders' => [[
        'customer_id' => $request->fresh()->customer_id, 'holder_role' => 'primary', 'is_signatory' => 1,
        'signature_image' => pngFile('sig.png'), 'thumb_image' => pngFile('thumb.png'),
    ]]]);
    $put('cdd', [
        'income_sources' => [IncomeSource::code('SALARIED')->firstOrFail()->id],
        'account_purposes' => [AccountPurpose::code('SAVING')->firstOrFail()->id],
    ]);

    return $request->fresh();
}

function submitAs(mixed $test, User $user, AccountOpeningRequest $request): void
{
    actAs($test, $user)->post(route('account-openings.submit', $request))->assertRedirect(route('account-openings.show', $request));
}

test('the maker submits a complete request and it is recorded', function () {
    $request = completeAof($this, $this->maker);

    submitAs($this, $this->maker, $request);

    $request->refresh();
    expect($request->status)->toBe('submitted')
        ->and($request->submitted_by)->toBe($this->maker->id)
        ->and($request->submitted_at)->not->toBeNull()
        ->and($request->isLocked())->toBeFalse()
        ->and($request->histories->pluck('event')->unique()->values()->all())->toBe(['created', 'step_saved', 'submitted']);
});

test('a user without the submit permission cannot submit', function () {
    $request = completeAof($this, $this->maker);
    $this->maker->revokePermissionTo('submit account openings');
    $this->maker->roles->each->revokePermissionTo('submit account openings');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    actAs($this, $this->maker)->post(route('account-openings.submit', $request))->assertForbidden();

    expect($request->fresh()->status)->toBe('draft');
});

test('the maker cannot approve or reject their own request, not even a super admin', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);

    $this->maker->givePermissionTo('approve account openings');
    actAs($this, $this->maker)->post(route('account-openings.approve', $request))
        ->assertRedirect()->assertSessionHas('error');
    actAs($this, $this->maker)->post(route('account-openings.reject', $request), ['reason' => 'No'])->assertSessionHas('error');

    $adminRequest = completeAof($this, $this->superAdmin);
    submitAs($this, $this->superAdmin, $adminRequest);
    actAs($this, $this->superAdmin)->post(route('account-openings.approve', $adminRequest))->assertSessionHas('error');

    expect($request->fresh()->status)->toBe('submitted')->and($adminRequest->fresh()->status)->toBe('submitted');
});

test('a different checker approves, the account becomes active and the request locks', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);

    actAs($this, $this->checker)->post(route('account-openings.approve', $request))->assertSessionHas('success');

    $request->refresh();
    expect($request->status)->toBe('approved')
        ->and($request->approved_by)->toBe($this->checker->id)
        ->and($request->isLocked())->toBeTrue()
        ->and($request->account->status)->toBe('active')
        ->and($request->customer->status)->toBe('active')
        ->and($request->histories->last()->event)->toBe('approved')
        ->and($request->histories->last()->user_id)->toBe($this->checker->id);
});

test('an approved request is locked for every user: no edit, no save, no delete', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);
    actAs($this, $this->checker)->post(route('account-openings.approve', $request));

    $nameBefore = $request->fresh()->customer->individual->full_name;

    foreach ([$this->maker, $this->checker, $this->superAdmin] as $user) {
        actAs($this, $user)->get(route('account-openings.steps.edit', [$request, 'cif']))
            ->assertRedirect(route('account-openings.show', $request))->assertSessionHas('error');

        actAs($this, $user)->put(route('account-openings.steps.update', [$request, 'cif']), ['full_name' => 'Hacked Name', 'date_of_birth' => '1990-05-04'])
            ->assertRedirect(route('account-openings.show', $request))->assertSessionHas('error');
    }

    actAs($this, $this->superAdmin)->delete(route('account-openings.destroy', $request))->assertSessionHas('error');
    actAs($this, $this->checker)->post(route('account-openings.submit', $request))->assertSessionHas('error');

    expect($request->fresh()->customer->individual->full_name)->toBe($nameBefore)
        ->and(AccountOpeningRequest::whereKey($request->id)->exists())->toBeTrue();
});

test('only a super admin can unlock, with a reason, and only that request', function () {
    $request = completeAof($this, $this->maker);
    $other = completeAof($this, $this->maker);
    foreach ([$request, $other] as $r) {
        submitAs($this, $this->maker, $r);
        actAs($this, $this->checker)->post(route('account-openings.approve', $r));
    }

    $this->checker->givePermissionTo('unlock account openings');
    actAs($this, $this->checker)->post(route('account-openings.unlock', $request), ['reason' => 'Fix spelling'])->assertSessionHas('error');
    actAs($this, $this->maker)->post(route('account-openings.unlock', $request), ['reason' => 'Fix spelling'])->assertForbidden();

    actAs($this, $this->superAdmin)->post(route('account-openings.unlock', $request), ['reason' => ''])->assertSessionHasErrors('reason');
    expect($request->fresh()->isLocked())->toBeTrue();

    actAs($this, $this->superAdmin)->post(route('account-openings.unlock', $request), ['reason' => 'Fix spelling of the name'])
        ->assertSessionHas('success');

    $request->refresh();
    expect($request->isLocked())->toBeFalse()
        ->and($request->status)->toBe('draft')
        ->and($request->unlocked_by)->toBe($this->superAdmin->id)
        ->and($request->unlock_reason)->toBe('Fix spelling of the name')
        ->and($other->fresh()->isLocked())->toBeTrue()
        ->and($request->histories->last()->event)->toBe('unlocked')
        ->and($request->histories->last()->note)->toBe('Fix spelling of the name');
});

test('after an unlock the maker edits, the change is tracked, and approval locks it again', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);
    actAs($this, $this->checker)->post(route('account-openings.approve', $request));
    actAs($this, $this->superAdmin)->post(route('account-openings.unlock', $request), ['reason' => 'Mobile number was wrong']);

    actAs($this, $this->maker)->put(route('account-openings.steps.update', [$request, 'identification']), [
        'identifications' => [['identification_document_type_id' => IdentificationDocumentType::code('CNIC')->firstOrFail()->id, 'document_number' => '81303-1234567-1']],
        'addresses' => ['permanent_residential' => ['city' => 'Muzaffarabad']],
        'contacts' => ['mobile' => [1 => ['country_code' => '+92', 'value' => '3111111111']]],
    ])->assertSessionHasNoErrors();

    $entry = $request->fresh()->histories->where('event', 'step_saved')->last();
    expect($entry->step)->toBe('identification')
        ->and($entry->user_id)->toBe($this->maker->id)
        ->and($entry->changes['Contact (Mobile 1) > Value'])->toBe(['old' => '3008169924', 'new' => '3111111111']);

    submitAs($this, $this->maker, $request);
    actAs($this, $this->otherChecker)->post(route('account-openings.approve', $request));

    expect($request->fresh()->isLocked())->toBeTrue()->and($request->fresh()->approved_by)->toBe($this->otherChecker->id);
});

test('saving a step without changing anything adds no history entry', function () {
    $request = completeAof($this, $this->maker);
    $count = $request->histories()->count();

    actAs($this, $this->maker)->put(route('account-openings.steps.update', [$request, 'compliance']), ['is_pep' => 0])->assertSessionHasNoErrors();

    expect($request->histories()->count())->toBe($count);
});

test('a checker rejects with a reason, the maker corrects and resubmits', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);

    actAs($this, $this->checker)->post(route('account-openings.reject', $request), ['reason' => ''])->assertSessionHasErrors('reason');
    actAs($this, $this->checker)->post(route('account-openings.reject', $request), ['reason' => 'CNIC copy is not attested'])->assertSessionHas('success');

    $request->refresh();
    expect($request->status)->toBe('rejected')
        ->and($request->rejection_reason)->toBe('CNIC copy is not attested')
        ->and($request->rejected_by)->toBe($this->checker->id)
        ->and($request->isEditable())->toBeTrue()
        ->and($request->isLocked())->toBeFalse();

    actAs($this, $this->maker)->get(route('account-openings.show', $request))->assertSee('CNIC copy is not attested');

    submitAs($this, $this->maker, $request);
    expect($request->fresh()->status)->toBe('submitted');
});

test('a submitted request cannot be edited by the maker while it waits for the checker', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);

    actAs($this, $this->maker)->put(route('account-openings.steps.update', [$request, 'cif']), ['full_name' => 'Changed', 'date_of_birth' => '1990-05-04'])
        ->assertRedirect(route('account-openings.show', $request))->assertSessionHas('error');

    expect($request->fresh()->customer->individual->full_name)->toBe('Ali Raza');
});

test('with maker-checker switched off the same person may approve', function () {
    config(['account_opening.maker_checker.enabled' => false]);
    $request = completeAof($this, $this->checker);
    submitAs($this, $this->checker, $request);

    actAs($this, $this->checker)->post(route('account-openings.approve', $request))->assertSessionHas('success');

    expect($request->fresh()->status)->toBe('approved');
});

test('self approval can be allowed explicitly while maker-checker stays on', function () {
    config(['account_opening.maker_checker.allow_self_approval' => true]);
    $request = completeAof($this, $this->checker);
    submitAs($this, $this->checker, $request);

    actAs($this, $this->checker)->post(route('account-openings.approve', $request))->assertSessionHas('success');

    expect($request->fresh()->status)->toBe('approved');
});

test('the approval lock can be switched off', function () {
    config(['account_opening.lock_after_approval' => false]);
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);
    actAs($this, $this->checker)->post(route('account-openings.approve', $request));

    expect($request->fresh()->status)->toBe('approved')->and($request->fresh()->isLocked())->toBeFalse();
});

test('the request page shows the right actions to each role and the full history', function () {
    $request = completeAof($this, $this->maker);
    submitAs($this, $this->maker, $request);

    actAs($this, $this->maker)->get(route('account-openings.show', $request))
        ->assertSuccessful()
        ->assertDontSee('Approve &amp; Activate Account', false)
        ->assertSee('a different user has to approve or reject it')
        ->assertSee('History')
        ->assertSee($this->maker->name);

    actAs($this, $this->checker)->get(route('account-openings.show', $request))
        ->assertSee('Approve &amp; Activate Account', false)
        ->assertSee('Reject this request');

    actAs($this, $this->checker)->post(route('account-openings.approve', $request));

    actAs($this, $this->checker)->get(route('account-openings.show', $request))
        ->assertSee('data-locked', false)
        ->assertDontSee('Unlock this request for changes');

    actAs($this, $this->superAdmin)->get(route('account-openings.show', $request))
        ->assertSee('Unlock this request for changes')
        ->assertSee($this->checker->name);
});

test('the checker and unlock permissions are seeded on the right roles only', function () {
    expect($this->maker->can('submit account openings'))->toBeTrue()
        ->and($this->maker->can('approve account openings'))->toBeFalse()
        ->and($this->maker->can('unlock account openings'))->toBeFalse()
        ->and($this->checker->can('approve account openings'))->toBeTrue()
        ->and($this->checker->can('unlock account openings'))->toBeFalse()
        ->and($this->superAdmin->can('unlock account openings'))->toBeTrue();
});
