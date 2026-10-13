<?php

use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\CustomerCategory;
use App\Models\User;
use Database\Seeders\BajkAccountOpeningSeeder;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['view account openings', 'create account openings', 'edit account openings', 'delete account openings', 'approve account openings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super-admin'])->givePermissionTo(Permission::all());
    Role::firstOrCreate(['name' => 'auditor'])->givePermissionTo(['view account openings']);
    $this->seed(BajkAccountOpeningSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->branch = Branch::factory()->create();
});

function startAofEdition(mixed $test, ?string $edition): TestResponse
{
    return $test->actingAs($test->admin)->post(route('account-openings.store'), [
        'branch_id' => $test->branch->id,
        'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL,
        'customer_category_id' => CustomerCategory::code('INDIVIDUAL')->firstOrFail()->id,
        'request_date' => now()->toDateString(),
        ...($edition === null ? [] : ['form_edition' => $edition]),
    ]);
}

test('the list has an Old account opening button next to the New one', function () {
    $this->actingAs($this->admin)->get(route('account-openings.index'))
        ->assertSuccessful()
        ->assertSee('New account opening')
        ->assertSee('Old account opening')
        ->assertSee(route('account-openings.create', ['edition' => 'old']), false);
});

test('a user who cannot create account openings sees neither button', function () {
    $auditor = User::factory()->create();
    $auditor->assignRole('auditor');

    $this->actingAs($auditor)->get(route('account-openings.index'))
        ->assertSuccessful()
        ->assertDontSee('New account opening')
        ->assertDontSee('Old account opening');

    $this->actingAs($auditor)->get(route('account-openings.create', ['edition' => 'old']))->assertForbidden();
});

test('the create screen follows the edition in the link', function () {
    $this->actingAs($this->admin)->get(route('account-openings.create'))
        ->assertSuccessful()
        ->assertSee('New Account Opening')
        ->assertSee('name="form_edition" value="new"', false);

    $this->actingAs($this->admin)->get(route('account-openings.create', ['edition' => 'old']))
        ->assertSuccessful()
        ->assertSee('Old Account Opening (BAJK paper form)')
        ->assertSee('name="form_edition" value="old"', false);
});

test('a request remembers which form it was started with', function () {
    startAofEdition($this, 'old')->assertSessionHasNoErrors();
    startAofEdition($this, 'new')->assertSessionHasNoErrors();
    startAofEdition($this, null)->assertSessionHasNoErrors();

    expect(AccountOpeningRequest::pluck('form_edition')->sort()->values()->all())->toBe(['new', 'new', 'old']);

    startAofEdition($this, 'ancient')->assertSessionHasErrors('form_edition');
});

test('the list and the request page show whether a request is old or new and can filter by it', function () {
    startAofEdition($this, 'old');
    startAofEdition($this, 'new');
    $old = AccountOpeningRequest::where('form_edition', 'old')->firstOrFail();
    $new = AccountOpeningRequest::where('form_edition', 'new')->firstOrFail();

    $this->actingAs($this->admin)->get(route('account-openings.index'))
        ->assertSee('(Old form)')
        ->assertSee('(New form)');

    $this->actingAs($this->admin)->get(route('account-openings.index', ['filter' => ['form_edition' => 'old']]))
        ->assertSee($old->request_number)
        ->assertDontSee($new->request_number);

    $this->actingAs($this->admin)->get(route('account-openings.show', $old))->assertSee('Old form');
    $this->actingAs($this->admin)->get(route('account-openings.print', $old))->assertSee('Old form');
});
