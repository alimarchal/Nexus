<?php

use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\CustomerCategory;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view account openings', 'create account openings', 'edit account openings', 'delete account openings', 'approve account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());
    Role::firstOrCreate(['name' => 'branch'])->givePermissionTo(['view account openings', 'create account openings', 'edit account openings']);

    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->branchA = Branch::factory()->create();
    $this->branchB = Branch::factory()->create();
});

/**
 * Start an AOF through the real endpoint and optionally force its status.
 */
function makeAof(mixed $test, Branch $branch, string $status = 'draft', string $category = 'INDIVIDUAL'): AccountOpeningRequest
{
    $response = $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code($category)->firstOrFail()->id,
        'request_date' => now()->toDateString(),
    ])->assertRedirect();

    preg_match('/account-openings\/([0-9a-f-]{36})\//', $response->headers->get('Location'), $matches);
    $request = AccountOpeningRequest::findOrFail($matches[1]);
    $request->update(['status' => $status]);

    return $request;
}

test('the list shows the status counts as cards and tabs', function () {
    makeAof($this, $this->branchA, 'draft');
    makeAof($this, $this->branchA, 'submitted');
    makeAof($this, $this->branchA, 'under_review');
    makeAof($this, $this->branchA, 'approved');
    makeAof($this, $this->branchA, 'approved');

    $response = $this->actingAs($this->admin)->get(route('account-openings.index'))->assertSuccessful();

    expect($response->viewData('stats'))->toMatchArray(['total' => 5, 'awaiting' => 2])
        ->and($response->viewData('stats')['by_status'])->toBe(['draft' => 1, 'submitted' => 1, 'under_review' => 1, 'approved' => 2, 'rejected' => 0]);

    $response->assertSee('Awaiting approval')->assertSee('Under review')->assertSee('Rejected');
});

test('a status tab lists only that status while the tab counts ignore the tab itself', function () {
    $draft = makeAof($this, $this->branchA, 'draft');
    $approved = makeAof($this, $this->branchA, 'approved');

    $response = $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['status' => 'approved']]))->assertSuccessful();

    $response->assertSee($approved->request_number)->assertDontSee($draft->request_number);
    expect($response->viewData('stats')['total'])->toBe(2);
});

test('one search box finds a request by number, CIF or customer name', function () {
    $first = makeAof($this, $this->branchA);
    $second = makeAof($this, $this->branchB);
    $first->customer->individual()->create(['full_name' => 'Zainab Fatima', 'date_of_birth' => '1990-01-01']);

    $byNumber = $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => $second->request_number]]));
    $byNumber->assertSee($second->request_number)->assertDontSee($first->request_number);

    $byCif = $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => $first->customer->cif_number]]));
    $byCif->assertSee($first->request_number)->assertDontSee($second->request_number);

    $byName = $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['search' => 'Zainab']]));
    $byName->assertSee($first->request_number)->assertDontSee($second->request_number);
});

test('active filters appear as removable chips', function () {
    makeAof($this, $this->branchA);

    $this->actingAs($this->admin)
        ->get(route('account-openings.index', ['filter' => ['search' => 'abc', 'aof_form_type' => 'entity']]))
        ->assertSee('Filtered by')
        ->assertSee('Remove filter Search', false)
        ->assertSee('Remove filter Form', false)
        ->assertSee('Clear all')
        ->assertSee('No requests match these filters');
});

test('rows per page and sorting are applied', function () {
    foreach (range(1, 12) as $i) {
        makeAof($this, $this->branchA);
    }

    $response = $this->actingAs($this->admin)->get(route('account-openings.index', ['per_page' => 10, 'sort' => 'request_number']))->assertSuccessful();

    expect($response->viewData('accountOpeningRequests')->perPage())->toBe(10)
        ->and($response->viewData('accountOpeningRequests')->total())->toBe(12)
        ->and($response->viewData('accountOpeningRequests')->first()->request_number)
        ->toBe(AccountOpeningRequest::orderBy('request_number')->first()->request_number);

    $fallback = $this->actingAs($this->admin)->get(route('account-openings.index', ['per_page' => 7]));
    expect($fallback->viewData('perPage'))->toBe(25);
});

test('a branch user sees only their own branch and the counts follow', function () {
    $mine = makeAof($this, $this->branchA, 'draft');
    $theirs = makeAof($this, $this->branchB, 'draft');

    $user = User::factory()->create(['branch_id' => $this->branchA->id]);
    $user->assignRole('branch');

    // The "request started" flash from creating the rows would otherwise be echoed on this page.
    $this->flushSession();

    $response = $this->actingAs($user)->get(route('account-openings.index'))->assertSuccessful();

    $response->assertSee($mine->request_number)->assertDontSee($theirs->request_number);
    expect($response->viewData('stats')['total'])->toBe(1);
});

test('row actions follow the permissions and the empty state offers a first request', function () {
    $user = User::factory()->create(['branch_id' => $this->branchA->id]);
    $user->assignRole('branch');

    $this->actingAs($user)->get(route('account-openings.index'))
        ->assertSuccessful()
        ->assertSee('No account opening requests yet')
        ->assertSee('New account opening');

    $request = makeAof($this, $this->branchA);

    $this->actingAs($user)->get(route('account-openings.index'))
        ->assertSee(route('account-openings.print', $request), false)
        ->assertSee(route('account-openings.steps.edit', [$request, 'cif']), false)
        ->assertDontSee('Delete '.$request->request_number);

    $this->actingAs($this->admin)->get(route('account-openings.index'))
        ->assertSee('Delete '.$request->request_number);
});
