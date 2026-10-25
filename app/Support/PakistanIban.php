<?php

namespace App\Support;

/**
 * Pakistan IBAN (24 characters): "PK" + 2 check digits + 4 letter bank code + 16 digit account number.
 * The check digits follow ISO 13616 (mod 97).
 */
final class PakistanIban
{
    public const BANK_CODE = 'BAJK';

    public static function make(string $accountNumber): string
    {
        $accountNumber = str_pad(preg_replace('/\D/', '', $accountNumber), 16, '0', STR_PAD_LEFT);
        $bban = self::BANK_CODE.substr($accountNumber, -16);

        return 'PK'.str_pad((string) (98 - self::mod97($bban.'PK00')), 2, '0', STR_PAD_LEFT).$bban;
    }

    public static function isValid(string $iban): bool
    {
        return preg_match('/^PK\d{2}[A-Z]{4}\d{16}$/', $iban) === 1
            && self::mod97(substr($iban, 4).substr($iban, 0, 4)) === 1;
    }

    /**
     * The 16 digit account number: 4 digit branch code followed by a 12 digit serial.
     */
    public static function accountNumber(string $branchCode, int $serial): string
    {
        return str_pad(substr(preg_replace('/\D/', '', $branchCode), -4), 4, '0', STR_PAD_LEFT).str_pad((string) $serial, 12, '0', STR_PAD_LEFT);
    }

    private static function mod97(string $value): int
    {
        $digits = '';

        foreach (str_split(strtoupper($value)) as $character) {
            $digits .= ctype_alpha($character) ? (string) (ord($character) - 55) : $character;
        }

        $remainder = 0;

        foreach (str_split($digits, 7) as $chunk) {
            $remainder = (int) ($remainder.$chunk) % 97;
        }

        return $remainder;
    }
}
