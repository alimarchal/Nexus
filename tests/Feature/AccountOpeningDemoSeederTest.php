<?php

use App\Models\Account;
use App\Models\AccountOpeningRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\AccountOpeningDemoSeeder;
use Database\Seeders\BajkAccountOpeningSeeder;

beforeEach(function () {
    $this->seed(BajkAccountOpeningSeeder::class);
    Branch::factory()->count(3)->create();
    User::factory()->count(2)->create();
});

afterEach(function () {
    putenv('ACCOUNT_OPENING_DEMO_COUNT');
    putenv('SEED_ACCOUNT_OPENING_DEMO');
    unset($_ENV['ACCOUNT_OPENING_DEMO_COUNT'], $_SERVER['ACCOUNT_OPENING_DEMO_COUNT'], $_ENV['SEED_ACCOUNT_OPENING_DEMO'], $_SERVER['SEED_ACCOUNT_OPENING_DEMO']);
});

function seedAccountOpeningDemo(int $count): void
{
    putenv("ACCOUNT_OPENING_DEMO_COUNT={$count}");
    $_ENV['ACCOUNT_OPENING_DEMO_COUNT'] = $_SERVER['ACCOUNT_OPENING_DEMO_COUNT'] = (string) $count;

    (new AccountOpeningDemoSeeder)->run();
}

test('the seeder creates demo requests of both forms and editions in every workflow status, each with its history', function () {
    seedAccountOpeningDemo(16);

    $requests = AccountOpeningRequest::with(['histories', 'customer', 'account'])->get();

    expect($requests)->toHaveCount(16)
        ->and($requests->every(fn ($request) => str_starts_with($request->request_number, 'DEMO-')))->toBeTrue()
        ->and($requests->pluck('status')->unique()->sort()->values()->all())->toBe(['approved', 'draft', 'rejected', 'submitted'])
        ->and($requests->pluck('aof_form_type')->unique()->sort()->values()->all())->toBe([AccountOpeningRequest::FORM_ENTITY, AccountOpeningRequest::FORM_INDIVIDUAL])
        ->and($requests->pluck('form_edition')->unique()->sort()->values()->all())->toBe(['new', 'old'])
        ->and($requests->pluck('branch_id')->unique()->count())->toBeGreaterThan(1)
        ->and($requests->every(fn ($request) => $request->histories->isNotEmpty()))->toBeTrue();

    foreach (['submitted', 'approved', 'rejected'] as $status) {
        expect($requests->firstWhere('status', $status)->histories->pluck('event')->all())->toContain('submitted');
    }
    expect($requests->where('status', 'approved')->every(fn ($request) => $request->histories->pluck('event')->contains('approved') && $request->isLocked()))->toBeTrue()
        ->and($requests->where('status', 'rejected')->every(fn ($request) => filled($request->rejection_reason)))->toBeTrue();
});

test('a new demo request carries the complete form data', function () {
    seedAccountOpeningDemo(16);

    $individual = AccountOpeningRequest::where('form_edition', 'new')->where('aof_form_type', AccountOpeningRequest::FORM_INDIVIDUAL)->where('status', 'approved')->with([
        'customer.individual', 'customer.identifications', 'customer.addresses', 'customer.contacts', 'customer.nextOfKin', 'customer.fatcaDetail',
        'account.product', 'account.holders', 'account.dueDiligences.incomeSources', 'account.dueDiligences.creditModes',
    ])->firstOrFail();
    $entity = AccountOpeningRequest::where('form_edition', 'new')->where('aof_form_type', AccountOpeningRequest::FORM_ENTITY)->with(['customer.organization', 'customer.fatcaDetail', 'account.holders'])->firstOrFail();

    expect($individual->customer->status)->toBe('active')
        ->and($individual->customer->individual->full_name)->toStartWith('DEMO ')
        ->and($individual->customer->identifications)->toHaveCount(1)
        ->and($individual->customer->addresses)->toHaveCount(2)
        ->and($individual->customer->contacts->count())->toBeGreaterThanOrEqual(2)
        ->and($individual->customer->nextOfKin)->toHaveCount(1)
        ->and($individual->customer->fatcaDetail)->not->toBeNull()
        ->and($individual->account->status)->toBe('active')
        ->and($individual->account->holders)->not->toBeEmpty()
        ->and($individual->account->dueDiligences->first()->incomeSources)->not->toBeEmpty()
        ->and($individual->account->dueDiligences->first()->creditModes)->not->toBeEmpty()
        ->and($entity->customer->organization->business_name)->toStartWith('DEMO ')
        ->and($entity->customer->fatcaDetail->nfe_type)->toBe('active')
        ->and($entity->account->holders->pluck('holder_role')->all())->toContain('primary', 'authorized_signatory');
});

test('an old demo request holds a filled paper form and an approved one has its customer and account', function () {
    seedAccountOpeningDemo(16);

    $old = AccountOpeningRequest::where('form_edition', 'old')->with('paperForm')->get();
    $approved = $old->firstWhere('status', 'approved');

    expect($old->every(fn ($request) => $request->paperForm && filled($request->paperForm->data['account_nature'] ?? null)))->toBeTrue()
        ->and($old->where('aof_form_type', AccountOpeningRequest::FORM_ENTITY)->every(fn ($request) => $request->paperForm->data['account_nature'] === 'business'))->toBeTrue()
        ->and($approved->customer)->not->toBeNull()
        ->and($approved->customer->cif_number)->toStartWith('DEMO-')
        ->and($approved->account->account_number)->toStartWith('DEMO-');
});

test('running the seeder again replaces the demo data and the count can be set', function () {
    seedAccountOpeningDemo(8);
    $first = AccountOpeningRequest::pluck('request_number')->all();
    seedAccountOpeningDemo(12);

    expect(AccountOpeningRequest::count())->toBe(12)->and(AccountOpeningRequest::pluck('request_number')->all())->toContain(...$first);
});

test('a count of zero removes the demo data and leaves the real data alone', function () {
    $branch = Branch::first();
    $real = AccountOpeningRequest::create(['request_number' => '0001-REAL-0001', 'aof_form_type' => AccountOpeningRequest::FORM_INDIVIDUAL, 'form_edition' => 'new', 'branch_id' => $branch->id, 'request_date' => now(), 'status' => 'draft']);

    seedAccountOpeningDemo(8);
    expect(AccountOpeningRequest::count())->toBe(9)->and(Customer::where('cif_number', 'like', 'DEMO-%')->count())->toBeGreaterThan(0);

    seedAccountOpeningDemo(0);

    expect(AccountOpeningRequest::pluck('id')->all())->toBe([$real->id])
        ->and(Customer::where('cif_number', 'like', 'DEMO-%')->count())->toBe(0)
        ->and(Account::where('account_number', 'like', 'DEMO-%')->count())->toBe(0);
});

test('the demo seeder is off unless SEED_ACCOUNT_OPENING_DEMO is true', function () {
    expect(filter_var(env('SEED_ACCOUNT_OPENING_DEMO', false), FILTER_VALIDATE_BOOLEAN))->toBeFalse();
    expect(file_get_contents(base_path('.env.example')))->toContain('SEED_ACCOUNT_OPENING_DEMO=false');
});
