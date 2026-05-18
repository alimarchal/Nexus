<?php

use Alimarchal\IdGenerator\IdGenerator;

it('can instantiate IdGenerator', function () {
    $generator = new IdGenerator;
    expect($generator)->toBeInstanceOf(IdGenerator::class);
});

it('has working helper functions', function () {
    expect(function_exists('generateUniqueId'))->toBeTrue();
    expect(function_exists('generateUniqueIdWithPrefix'))->toBeTrue();
});
