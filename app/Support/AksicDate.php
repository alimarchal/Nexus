<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * AKSIC date handling (Portal Change Requirement #7).
 *
 * Users read and type every AKSIC date as D.M.Y (e.g. 25.05.2026) in the portal
 * and in the Excel template; the database always stores Y-m-d. This class is
 * the single converter used by the form requests and the Excel import so both
 * paths read a date the same way.
 */
final class AksicDate
{
    public const DISPLAY = 'd.m.Y';

    public const PLACEHOLDER = 'dd.mm.yyyy';

    /**
     * Convert user / Excel input to a database date (Y-m-d).
     *
     * Accepts, in this order: DateTime objects, Excel serial numbers,
     * ISO Y-m-d, and day-first D.M.Y with ".", "/" or "-" separators
     * (two- or four-digit year). Day-first is enforced so 05/06/2026 is
     * always 5 June, never 6 May. Returns the input unchanged when it cannot
     * be read, so the validator reports it as an invalid date.
     */
    public static function toDatabase(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (Throwable) {
                return $raw;
            }
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[ T].*)?$/', $raw, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1])
                ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3])
                : $raw;
        }

        if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2}|\d{4})$/', $raw, $m)) {
            $year = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return checkdate((int) $m[2], (int) $m[1], $year)
                ? sprintf('%04d-%02d-%02d', $year, $m[2], $m[1])
                : $raw;
        }

        return $raw;
    }

    /**
     * Format a stored date for display as D.M.Y.
     */
    /**
     * Value for an <input type="date"> (the browser calendar): always Y-m-d, or
     * '' when empty / unreadable. The form posts Y-m-d, which toDatabase()
     * accepts as-is, so the calendar and the D.M.Y display share one path.
     */
    public static function forInput(mixed $value): string
    {
        $converted = self::toDatabase($value);

        return is_string($converted) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $converted) ? $converted : '';
    }

    public static function display(mixed $value, string $empty = '—'): string
    {
        if ($value === null || $value === '') {
            return $empty;
        }

        try {
            $date = $value instanceof DateTimeInterface ? $value : CarbonImmutable::parse((string) $value);

            return $date->format(self::DISPLAY);
        } catch (Throwable) {
            return (string) $value;
        }
    }
}
