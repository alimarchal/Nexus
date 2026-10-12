<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Length and character rules for the mobile number typed next to a country code.
 *
 * Pakistan (+92) numbers are written without the country code and without the
 * leading 0, so they are exactly 10 digits (3008169924). Other countries use the
 * international E.164 limit: digits only, 4 to 15 digits.
 */
final class MobileNumber
{
    public const PAKISTAN_CODE = '+92';

    public const PAKISTAN_LENGTH = 10;

    /** Normalise "92", "0092" and " +92 " to "+92". */
    public static function normaliseCountryCode(?string $countryCode): string
    {
        $digits = preg_replace('/\D+/', '', (string) $countryCode) ?? '';
        $digits = ltrim($digits, '0');

        return $digits === '' ? '' : '+'.$digits;
    }

    public static function isPakistan(?string $countryCode): bool
    {
        return self::normaliseCountryCode($countryCode) === self::PAKISTAN_CODE;
    }

    /** The longest number allowed for this country code (digits only). */
    public static function maxLength(?string $countryCode): int
    {
        return self::isPakistan($countryCode) ? self::PAKISTAN_LENGTH : 15;
    }

    /**
     * A user-facing reason the number is not acceptable, or null when it is fine.
     */
    public static function problem(?string $countryCode, ?string $number): ?string
    {
        $number = trim((string) $number);

        if ($number === '') {
            return null;
        }

        if (! ctype_digit($number)) {
            return 'The mobile number can contain digits only. Remove letters, spaces and symbols.';
        }

        if (self::isPakistan($countryCode)) {
            if (strlen($number) !== self::PAKISTAN_LENGTH) {
                return 'For +92 the mobile number must contain exactly 10 digits, written without the country code or a leading 0 (for example 3008169924).';
            }

            return null;
        }

        if (strlen($number) < 4 || strlen($number) > 15) {
            return 'The mobile number must be between 4 and 15 digits for this country code.';
        }

        return null;
    }
}
