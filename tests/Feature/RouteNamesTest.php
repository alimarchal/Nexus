<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
 * `php artisan optimize` (route:cache) fails when two routes share a name.
 */
test('every named route has a unique name so routes can be cached', function (): void {
    $duplicates = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route) => $route->getName())
        ->filter()
        ->countBy()
        ->filter(fn (int $count) => $count > 1)
        ->keys()
        ->all();

    expect($duplicates)->toBe([]);
});

test('the old users list url redirects to the users list', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/settings/users')
        ->assertRedirect(route('users.index'));
});

test('the old roles and permissions list urls redirect to their lists', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/roles')->assertRedirect(route('roles.index'));
    $this->get('/settings/permissions')->assertRedirect(route('permissions.index'));
});
