<?php

use App\Models\Country;
use App\Models\CustomerCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('gives a fresh install the default super admin and the account opening reference data', function () {
    $this->seed(DatabaseSeeder::class);

    $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();

    expect($superAdmin->hasRole('super-admin'))->toBeTrue()
        ->and(Hash::check('password', $superAdmin->password))->toBeTrue()
        ->and(CustomerCategory::count())->toBeGreaterThan(0)
        ->and(Country::count())->toBeGreaterThan(0);

    $this->actingAs($superAdmin)
        ->get(route('account-openings.create'))
        ->assertSuccessful()
        ->assertSee('Individual');
});
