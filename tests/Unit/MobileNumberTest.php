<?php

use App\Support\MobileNumber;

it('accepts exactly ten digits after +92', function () {
    expect(MobileNumber::problem('+92', '3008169924'))->toBeNull()
        ->and(MobileNumber::problem('92', '3008169924'))->toBeNull()
        ->and(MobileNumber::problem('0092', '3008169924'))->toBeNull();
});

it('rejects more or fewer than ten digits after +92', function (string $number) {
    expect(MobileNumber::problem('+92', $number))->toContain('exactly 10 digits');
})->with(['03008169924', '30081699245', '300816992', '1']);

it('rejects letters and symbols for any country code', function (string $code, string $number) {
    expect(MobileNumber::problem($code, $number))->toContain('digits only');
})->with([
    ['+92', '30081699ab'],
    ['+92', '300-816-9924'],
    ['+44', '7911 123456'],
    ['+1', '555ABC1234'],
]);

it('applies the ten digit rule to +92 only; other codes use the international limit', function () {
    expect(MobileNumber::problem('+44', '7911123456'))->toBeNull()
        ->and(MobileNumber::problem('+971', '501234567'))->toBeNull()
        ->and(MobileNumber::problem('+1', '2025550123'))->toBeNull()
        ->and(MobileNumber::problem('+44', '12'))->toContain('between 4 and 15')
        ->and(MobileNumber::problem('+44', '1234567890123456'))->toContain('between 4 and 15');
});

it('leaves an empty number to the required rule', function () {
    expect(MobileNumber::problem('+92', ''))->toBeNull()
        ->and(MobileNumber::problem('+92', null))->toBeNull();
});

it('reports the longest accepted length per country code', function () {
    expect(MobileNumber::maxLength('+92'))->toBe(10)
        ->and(MobileNumber::maxLength('+44'))->toBe(15)
        ->and(MobileNumber::maxLength(null))->toBe(15);
});
