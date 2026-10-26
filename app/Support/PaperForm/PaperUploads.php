<?php

declare(strict_types=1);

namespace App\Support\PaperForm;

/**
 * What can be uploaded on an old (paper) Account Opening Form: the specimen signature and thumb impression of each
 * applicant (Authorized Signature) and one file for each document of the Documents/Papers to be Obtained checklist.
 * Every upload has a slot key, and one slot holds one file (a new upload replaces the old one).
 */
final class PaperUploads
{
    public const COLLECTION = 'paper_uploads';

    public const MIMES = ['jpg', 'jpeg', 'png', 'pdf'];

    public const MAX_KILOBYTES = 5120;

    /**
     * The signature and thumb impression slots, as slot key => label.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function signatureSlots(array $values): array
    {
        $names = collect((array) ($values['signatories'] ?? []))
            ->map(fn ($row): string => trim((string) ($row['name'] ?? '')))
            ->filter()
            ->values();

        if ($names->isEmpty() && ($values['account_nature'] ?? null) === 'individual') {
            $names = collect([1, 2])
                ->filter(fn (int $n): bool => $n === 1 || ($values['individual_kind'] ?? null) === 'joint')
                ->map(fn (int $n): string => trim((string) ($values["a{$n}_name"] ?? '')))
                ->values();
        }

        $slots = [];

        foreach (($names->isEmpty() ? collect(['']) : $names)->take(4) as $index => $name) {
            $number = $index + 1;
            $who = 'Applicant '.$number.($name !== '' ? ' ('.$name.')' : '');
            $slots["signature:applicant_{$number}"] = 'Specimen signature - '.$who;
            $slots["thumb:applicant_{$number}"] = 'Thumb impression - '.$who;
        }

        return $slots;
    }

    /**
     * One slot for each document of the checklist that applies to these values, as slot key => label.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function documentSlots(array $values): array
    {
        $slots = [];

        foreach (PaperFormSchema::visibleSections($values) as $section) {
            foreach ($section['fields'] as $field) {
                if (! str_starts_with((string) ($field['name'] ?? ''), 'docs_') || ! isset($field['options'])) {
                    continue;
                }

                foreach ($field['options'] as $key => $label) {
                    $slots["document:{$field['name']}.{$key}"] = rtrim((string) $field['label'], ' *').': '.$label;
                }
            }
        }

        return $slots;
    }

    /**
     * Every slot that applies to these values.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function slots(array $values): array
    {
        return self::signatureSlots($values) + self::documentSlots($values);
    }
}
