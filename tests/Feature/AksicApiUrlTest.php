<?php

use App\Models\AksicApplication;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
    config(['app.aksic_api_token' => 'test-token']);
});

test('the AKSIC API address defaults to the current government address and is read from .env', function () {
    expect(config('app.aksic_api_url'))->toBe('https://pmylp.admin.gok.pk/api/bank')
        ->and(file_get_contents(base_path('.env.example')))->toContain('AKSIC_API_URL=');
});

test('sync calls the configured AKSIC address', function () {
    Http::fake(['*' => Http::response([], 404)]);

    $this->actingAs($this->superAdmin)->post(route('aksic-applications.sync'))->assertSuccessful();
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://pmylp.admin.gok.pk/api/bank/applications');

    config(['app.aksic_api_url' => 'https://aksic.example.test/v2/']);
    Http::fake(['*' => Http::response([], 404)]);
    $this->actingAs($this->superAdmin)->post(route('aksic-applications.sync'));

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://aksic.example.test/v2/applications');
});

test('a status update calls the configured AKSIC address', function () {
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    $application = AksicApplication::factory()->create();

    $this->actingAs($this->superAdmin)->post(route('aksic-applications.update-status', $application), [
        'old_status' => 'Pending', 'new_status' => 'Approved', 'applicant_id' => 77,
    ]);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://pmylp.admin.gok.pk/api/bank/applications/77/status-update');
});
