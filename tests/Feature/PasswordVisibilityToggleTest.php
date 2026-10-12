<?php

use Illuminate\Support\Facades\Blade;

it('adds a hidden-by-default show/hide toggle to password inputs', function () {
    $html = Blade::render('<x-input id="password" type="password" name="password" class="mt-1 block w-full" required />');

    expect($html)
        ->toContain('data-password-toggle')
        ->toContain('x-data="{ shown: false }"')
        ->toContain(":type=\"shown ? 'text' : 'password'\"")
        ->toContain('type="password"')
        ->toContain('name="password"')
        ->toContain('required');
});

it('keeps layout classes on the wrapper so the eye sits at the field edge', function () {
    $html = Blade::render('<x-input type="password" class="mt-1 block w-3/4" />');

    expect($html)->toMatch('/<div class="relative mt-1 block w-3\/4"/');
});

it('forwards livewire bindings to the real input', function () {
    $html = Blade::render('<x-input type="password" wire:model="state.password" />');

    expect($html)->toContain('wire:model="state.password"');
});

it('does not touch non-password inputs', function () {
    $html = Blade::render('<x-input type="email" name="email" />');

    expect($html)->not->toContain('data-password-toggle')->not->toContain('x-data');
});

it('shows the toggle on the login and reset password screens', function () {
    $this->get(route('login'))->assertSuccessful()->assertSee('data-password-toggle', false);
    $this->get(route('password.reset', 'token'))->assertSuccessful()->assertSee('data-password-toggle', false);
});
