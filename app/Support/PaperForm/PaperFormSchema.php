<?php

declare(strict_types=1);

namespace App\Support\PaperForm;

use Illuminate\Validation\Rule;

/**
 * The old BAJK paper Account Opening Form, described as data: sections in the order of the paper form, the
 * fields in each section, their options and when they apply. The same description draws the entry form, the
 * saved view and the print, and builds the validation, so they can never drift apart.
 *
 * Field types: text, email, secret, textarea, date, number, select, radio, checkbox, checks, repeater, plus
 * heading (a caption inside a section) and terms (the Terms & Conditions text). Both carry no value.
 * A condition may also be ['any' => [condition, condition]]: it holds when one of them does.
 * A field (or section) with a "show" condition applies only while every condition holds, for example
 * ['account_nature' => 'individual'] or ['individual_kind' => ['joint']]. A value that is not shown is ignored.
 * An option listed in a field's "other" gets an "Other (specify)" text box, saved as "{name}_other".
 */
final class PaperFormSchema
{
    /**
     * The sections in the order of the paper form (sections with the same number keep their order).
     *
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        $sections = PaperFormSections::all();
        usort($sections, fn (array $a, array $b): int => $a['number'] <=> $b['number']);

        return $sections;
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
            if ($name === 'any') {
                if (! array_filter((array) $expected, fn (array $alternative): bool => self::holds($alternative, $values))) {
                    return false;
                }

                continue;
            }

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

        foreach (self::visibleSections(self::clean($values)) as $section) {
            foreach ($section['fields'] as $field) {
                if (self::carriesNoValue($field)) {
                    continue;
                }

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
            'email' => ['nullable', 'email', 'max:'.$max],
            'secret' => ['nullable', 'string', 'min:'.($field['min'] ?? 6), 'max:'.$max],
            'textarea' => ['nullable', 'string', 'max:'.($field['max'] ?? 2000)],
            'date' => ['nullable', 'date', ...(($field['past'] ?? false) ? ['before:today'] : [])],
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

        // A field applies only if the answers kept before it say so, so a stale answer in a hidden field
        // (for example a business kind left over after switching to an individual account) switches nothing on.
        foreach (self::sections() as $section) {
            if (! self::holds($section['show'] ?? [], $clean)) {
                continue;
            }

            foreach ($section['fields'] as $field) {
                if (self::carriesNoValue($field) || ! self::holds($field['show'] ?? [], $clean)) {
                    continue;
                }

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
                if (($field['required'] ?? false) && ! self::carriesNoValue($field) && self::isBlank($values[$field['name']] ?? null)) {
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
                if (! self::carriesNoValue($field)) {
                    $labels[$field['name']] = $field['label'];
                }
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
        return $value === null || $value === '' || $value === [] || $value === false;
    }

    /**
     * Headings and the Terms & Conditions text are shown on the form but hold no answer.
     *
     * @param  array<string, mixed>  $field
     */
    private static function carriesNoValue(array $field): bool
    {
        return in_array($field['type'], ['heading', 'terms'], true);
    }
}
