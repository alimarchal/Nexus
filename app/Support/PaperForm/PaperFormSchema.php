<?php

declare(strict_types=1);

namespace App\Support\PaperForm;

use Illuminate\Validation\Rule;

/**
 * The old BAJK paper Account Opening Form, described as data: sections in the order of the paper form, the
 * fields in each section, their options and when they apply. The same description draws the entry form, the
 * saved view and the print, and builds the validation, so they can never drift apart.
 *
 * Field types: text, secret, textarea, date, number, select, radio, checkbox, checks, repeater.
 * A field (or section) with a "show" condition applies only while every condition holds, for example
 * ['account_nature' => 'individual'] or ['individual_kind' => ['joint']]. A value that is not shown is ignored.
 * An option listed in a field's "other" gets an "Other (specify)" text box, saved as "{name}_other".
 */
final class PaperFormSchema
{
    /** @var array<string, string> */
    public const YES_NO = ['yes' => 'Yes', 'no' => 'No'];

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'nature',
                'number' => 3,
                'title' => 'Nature of Account',
                'fields' => [
                    ['name' => 'account_nature', 'type' => 'radio', 'label' => 'Nature of Account', 'required' => true,
                        'options' => ['individual' => 'Individual A/c', 'business' => 'Business A/c']],
                    ['name' => 'individual_kind', 'type' => 'radio', 'label' => 'Individual A/c', 'required' => true,
                        'show' => ['account_nature' => 'individual'], 'options' => ['single' => 'Single', 'joint' => 'Joint']],
                ],
            ],
            [
                'key' => 'particulars',
                'number' => 4,
                'title' => 'Particulars of Account (Individual: Single/Joint)',
                'show' => ['account_nature' => 'individual'],
                'fields' => [
                    ['name' => 'title_of_account', 'type' => 'text', 'label' => 'Title of Account', 'required' => true],
                    ['name' => 'key_word', 'type' => 'secret', 'label' => 'Key/Secret Word (at least 6 characters)', 'required' => true, 'min' => 6, 'max' => 50,
                        'help' => 'Kept encrypted. Used to identify the customer on the phone.'],
                    ['name' => 'hold_mail', 'type' => 'radio', 'label' => 'Hold Mail', 'options' => self::YES_NO],
                ],
            ],
            [
                'key' => 'personal',
                'number' => 5,
                'title' => 'Personal Information',
                'show' => ['account_nature' => 'individual'],
                'fields' => [
                    ['name' => 'preferred_language_1', 'type' => 'text', 'label' => 'Preferred Language (Applicant 1)', 'max' => 60],
                    ['name' => 'preferred_language_2', 'type' => 'text', 'label' => 'Preferred Language (Applicant 2)', 'max' => 60,
                        'show' => ['individual_kind' => 'joint']],
                ],
            ],
            [
                'key' => 'wallet',
                'number' => 10,
                'title' => 'BAJK Wallet',
                'fields' => [
                    ['name' => 'supplementary_card', 'type' => 'radio', 'label' => 'Supplementary Card Required (fill the prescribed form)',
                        'options' => self::YES_NO],
                ],
            ],
            [
                'key' => 'bank_use',
                'number' => 18,
                'title' => 'For Bank Use Only',
                'fields' => [
                    ['name' => 'expected_average_balance', 'type' => 'number', 'label' => 'Expected Average Balance'],
                ],
            ],
        ];
    }

    /**
     * Whether a condition such as ['account_nature' => 'individual'] holds for the given values.
     *
     * @param  array<string, mixed>  $show
     * @param  array<string, mixed>  $values
     */
    public static function holds(array $show, array $values): bool
    {
        foreach ($show as $name => $expected) {
            $actual = $values[$name] ?? null;
            $actual = is_array($actual) ? $actual : [$actual];

            if (array_intersect($actual, (array) $expected) === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * The sections that apply to these values, each with only the fields that apply.
     *
     * @param  array<string, mixed>  $values
     * @return list<array<string, mixed>>
     */
    public static function visibleSections(array $values): array
    {
        $visible = [];

        foreach (self::sections() as $section) {
            if (! self::holds($section['show'] ?? [], $values)) {
                continue;
            }

            $section['fields'] = array_values(array_filter(
                $section['fields'],
                fn (array $field): bool => self::holds($field['show'] ?? [], $values),
            ));

            if ($section['fields'] !== []) {
                $visible[] = $section;
            }
        }

        return $visible;
    }

    /**
     * Validation rules for the submitted values; only the fields that apply are checked.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, list<mixed>>
     */
    public static function rules(array $values): array
    {
        $rules = ['data' => ['required', 'array']];

        foreach (self::visibleSections($values) as $section) {
            foreach ($section['fields'] as $field) {
                $name = 'data.'.$field['name'];

                if ($field['type'] === 'repeater') {
                    $rules[$name] = ['nullable', 'array', 'max:'.$field['rows']];

                    foreach ($field['columns'] as $column) {
                        $rules[$name.'.*.'.$column['name']] = ['nullable', 'string', 'max:'.($column['max'] ?? 150)];
                    }

                    continue;
                }

                $rules[$name] = self::fieldRules($field);

                if ($field['type'] === 'checks') {
                    $rules[$name.'.*'] = [Rule::in(array_keys($field['options']))];
                }

                if (isset($field['other'])) {
                    $rules[$name.'_other'] = ['nullable', 'string', 'max:150'];
                }
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<mixed>
     */
    private static function fieldRules(array $field): array
    {
        $max = $field['max'] ?? 150;

        return match ($field['type']) {
            'text' => ['nullable', 'string', 'max:'.$max],
            'secret' => ['nullable', 'string', 'min:'.($field['min'] ?? 6), 'max:'.$max],
            'textarea' => ['nullable', 'string', 'max:'.($field['max'] ?? 2000)],
            'date' => ['nullable', 'date'],
            'number' => ['nullable', 'numeric', 'min:0'],
            'select', 'radio' => ['nullable', Rule::in(array_keys($field['options']))],
            'checkbox' => ['nullable', 'boolean'],
            'checks' => ['nullable', 'array'],
        };
    }

    /**
     * Keep only what the form currently asks for: values of fields that do not apply are dropped and the
     * rest are tidied (blank strings become null, checkboxes become booleans).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function clean(array $input): array
    {
        $clean = [];

        foreach (self::visibleSections($input) as $section) {
            foreach ($section['fields'] as $field) {
                $name = $field['name'];

                if ($field['type'] === 'repeater') {
                    $clean[$name] = array_values(array_filter(
                        array_map(
                            fn ($row): array => array_map(fn ($cell) => self::blankToNull($cell), array_intersect_key((array) $row, array_flip(array_column($field['columns'], 'name')))),
                            array_slice((array) ($input[$name] ?? []), 0, $field['rows']),
                        ),
                        fn (array $row): bool => array_filter($row, fn ($cell) => $cell !== null) !== [],
                    ));

                    continue;
                }

                $clean[$name] = match ($field['type']) {
                    'checkbox' => filter_var($input[$name] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'checks' => array_values(array_intersect(array_keys($field['options']), (array) ($input[$name] ?? []))),
                    'number' => self::blankToNull($input[$name] ?? null) === null ? null : (float) $input[$name],
                    default => self::blankToNull($input[$name] ?? null),
                };

                if (isset($field['other'])) {
                    $selected = (array) ($input[$name] ?? []);
                    $clean[$name.'_other'] = array_intersect((array) $field['other'], $selected) !== []
                        ? self::blankToNull($input[$name.'_other'] ?? null)
                        : null;
                }
            }
        }

        return $clean;
    }

    /**
     * The mandatory fields that apply but are still empty.
     *
     * @param  array<string, mixed>  $values
     * @return list<string>
     */
    public static function missingRequired(array $values): array
    {
        $missing = [];

        foreach (self::visibleSections($values) as $section) {
            foreach ($section['fields'] as $field) {
                if (($field['required'] ?? false) && self::isBlank($values[$field['name']] ?? null)) {
                    $missing[] = $field['label'];
                }
            }
        }

        return $missing;
    }

    /**
     * Field name => label, for the history and the validation messages.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::sections() as $section) {
            foreach ($section['fields'] as $field) {
                $labels[$field['name']] = $field['label'];
            }
        }

        return $labels;
    }

    /**
     * What the request list calls this form: the title of the account.
     *
     * @param  array<string, mixed>  $values
     */
    public static function displayName(array $values): ?string
    {
        $name = $values['title_of_account'] ?? null;

        return is_string($name) && trim($name) !== '' ? trim($name) : null;
    }

    private static function blankToNull(mixed $value): mixed
    {
        return is_string($value) && trim($value) === '' ? null : (is_string($value) ? trim($value) : $value);
    }

    private static function isBlank(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
