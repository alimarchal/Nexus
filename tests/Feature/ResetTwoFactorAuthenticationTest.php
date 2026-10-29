<?php

use App\Models\User;

test('auth:reset-two-factor clears every two factor secret', function (): void {
    $withTwoFactor = User::factory()->create([
        'two_factor_secret' => 'unreadable-after-key-change',
        'two_factor_recovery_codes' => 'codes',
        'two_factor_confirmed_at' => now(),
    ]);
    $without = User::factory()->create();

    $this->artisan('auth:reset-two-factor')
        ->expectsOutput('Two-factor authentication cleared for 1 user(s).')
        ->assertSuccessful();

    $withTwoFactor->refresh();
    expect($withTwoFactor->two_factor_secret)->toBeNull()
        ->and($withTwoFactor->two_factor_recovery_codes)->toBeNull()
        ->and($withTwoFactor->two_factor_confirmed_at)->toBeNull()
        ->and($without->fresh()->two_factor_secret)->toBeNull();
});
