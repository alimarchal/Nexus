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
     * The sections in the order of the paper form.
     *
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        $sections = self::definitions();
        usort($sections, fn (array $a, array $b): int => $a['number'] <=> $b['number']);

        return $sections;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function definitions(): array
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
                'key' => 'business_contacts',
                'number' => 7,
                'title' => 'Particulars of Account (Business Account) - Contact Persons',
                'show' => ['account_nature' => 'business'],
                'fields' => [
                    ['name' => 'contact_persons', 'type' => 'repeater', 'label' => 'Contact Persons', 'rows' => 4, 'columns' => [
                        ['name' => 'name', 'label' => 'Name'],
                        ['name' => 'designation', 'label' => 'Designation'],
                        ['name' => 'telephone', 'label' => 'Telephone No.', 'max' => 30],
                    ]],
                ],
            ],
            [
                'key' => 'bajk_relationships',
                'number' => 8,
                'title' => 'Your Existing Relationships with BAJK (Business Account)',
                'show' => ['account_nature' => 'business'],
                'fields' => [
                    ['name' => 'bajk_relationships', 'type' => 'repeater', 'label' => 'Existing relationships with BAJK', 'rows' => 3, 'columns' => [
                        ['name' => 'branch_name', 'label' => 'Branch Name'],
                        ['name' => 'branch_code', 'label' => 'Branch Code', 'max' => 20],
                        ['name' => 'account_no', 'label' => 'Account/Loan/Card No.', 'max' => 40],
                        ['name' => 'account_title', 'label' => 'Account/Loan/Card Title'],
                    ]],
                ],
            ],
            [
                'key' => 'other_banks',
                'number' => 9,
                'title' => 'Your Relationship with Other Banks',
                'fields' => [
                    ['name' => 'other_banks', 'type' => 'repeater', 'label' => 'Other banks (up to 2)', 'rows' => 2, 'columns' => [
                        ['name' => 'bank_name', 'label' => 'Bank Name'],
                        ['name' => 'deposit', 'label' => 'Deposit', 'type' => 'checkbox'],
                        ['name' => 'credit_cards', 'label' => 'Credit Cards', 'type' => 'checkbox'],
                        ['name' => 'loans', 'label' => 'Loans', 'type' => 'checkbox'],
                        ['name' => 'other', 'label' => 'Other (specify)'],
                    ]],
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
                'key' => 'products',
                'number' => 12,
                'title' => 'Other BAJK Products of Interest',
                'fields' => [
                    ['name' => 'other_products', 'type' => 'checks', 'label' => 'Products of interest', 'other' => 'other', 'options' => [
                        'credit_cards' => 'Credit Cards', 'personal_loan' => 'Personal Loan', 'car_financing' => 'Car Financing',
                        'mortgage' => 'Mortgage', 'other' => 'Other (specify)',
                    ]],
                ],
            ],
            [
                'key' => 'introducer',
                'number' => 15,
                'title' => 'Introducer',
                'fields' => [
                    ['name' => 'introducer_name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                    ['name' => 'introducer_cnic', 'type' => 'text', 'label' => 'CNIC No.', 'max' => 20],
                    ['name' => 'introducer_telephone', 'type' => 'text', 'label' => 'Telephone No.', 'max' => 30],
                    ['name' => 'introducer_address', 'type' => 'textarea', 'label' => 'Address', 'max' => 500],
                    ['name' => 'introducer_bank_branch', 'type' => 'text', 'label' => 'Bank/Branch', 'required' => true],
                    ['name' => 'introducer_account_no', 'type' => 'text', 'label' => 'Account No.', 'required' => true, 'max' => 40],
                    ['name' => 'introducer_signed', 'type' => 'checkbox', 'label' => 'Introducer signature taken on the form'],
                    ['name' => 'introducer_known_years', 'type' => 'number', 'label' => 'How long has the introducer known the applicant: Years', 'integer' => true, 'max' => 99],
                    ['name' => 'introducer_known_months', 'type' => 'number', 'label' => 'How long has the introducer known the applicant: Months', 'integer' => true, 'max' => 11],
                    ['name' => 'introducer_verified_by', 'type' => 'text', 'label' => 'Verified By'],
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
                        $rules[$name.'.*.'.$column['name']] = ($column['type'] ?? 'text') === 'checkbox'
                            ? ['nullable', 'boolean']
                            : ['nullable', 'string', 'max:'.($column['max'] ?? 150)];
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
            'number' => ['nullable', ($field['integer'] ?? false) ? 'integer' : 'numeric', 'min:0', ...(isset($field['max']) ? ['max:'.$field['max']] : [])],
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
                            fn ($row): array => self::cleanRow((array) $row, $field['columns']),
                            array_slice((array) ($input[$name] ?? []), 0, $field['rows']),
                        ),
                        fn (array $row): bool => array_filter($row, fn ($cell) => $cell !== null && $cell !== false) !== [],
                    ));

                    continue;
                }

                $clean[$name] = match ($field['type']) {
                    'checkbox' => filter_var($input[$name] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'checks' => array_values(array_intersect(array_keys($field['options']), (array) ($input[$name] ?? []))),
                    'number' => self::blankToNull($input[$name] ?? null) === null ? null : (($field['integer'] ?? false) ? (int) $input[$name] : (float) $input[$name]),
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

    /**
     * One row of a repeater: only its own columns, blank cells as null, ticks as booleans.
     *
     * @param  array<string, mixed>  $row
     * @param  list<array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    private static function cleanRow(array $row, array $columns): array
    {
        $clean = [];

        foreach ($columns as $column) {
            $value = $row[$column['name']] ?? null;
            $clean[$column['name']] = ($column['type'] ?? 'text') === 'checkbox'
                ? filter_var($value, FILTER_VALIDATE_BOOLEAN)
                : self::blankToNull($value);
        }

        return $clean;
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
