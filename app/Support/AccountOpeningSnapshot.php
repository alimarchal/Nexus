<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AccountOpeningRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A flat, human readable picture of everything on an Account Opening request, taken
 * before and after a step is saved so the history can show exactly what changed:
 * "Customer > Mobile > Value": "3001234567" -> "3009999999".
 *
 * Foreign keys are shown by name (gender_id -> "Male"), uploaded files as "file on record".
 */
final class AccountOpeningSnapshot
{
    /** Attributes that are bookkeeping, not form content. */
    private const SKIP = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'approved_by', 'approved_at',
        'customer_id', 'account_id', 'account_holder_id', 'customer_due_diligence_id', 'account_opening_request_id',
        'sequence', 'is_active', 'effective_from', 'status', 'submitted_by', 'submitted_at', 'rejected_by', 'rejected_at',
        'locked_at', 'unlocked_by', 'unlocked_at', 'unlock_reason', 'rejection_reason', 'authorized_by_name', 'authorized_on',
    ];

    /**
     * Relation tree walked from the request: label => [relation path, key attribute, one row per record].
     * "Key attribute" names repeated rows ("Contact (Mobile 1)"); single records ignore it.
     *
     * @var array<string, array{0: string, 1: string|null, 2: bool}>
     */
    private const SECTIONS = [
        'Request' => ['', null, false],
        'Customer' => ['customer', null, false],
        'Individual details' => ['customer.individual', null, false],
        'Business details' => ['customer.organization', null, false],
        'Address' => ['customer.addresses', 'address_type', true],
        'Contact' => ['customer.contacts', 'contact_type', true],
        'Identification' => ['customer.identifications', null, true],
        'Nationality' => ['customer.nationalities', null, true],
        'Next of kin' => ['customer.nextOfKin', null, true],
        'FATCA / CRS' => ['customer.fatcaDetail', null, false],
        'Tax residency' => ['customer.taxResidencies', null, true],
        'Controlling person' => ['customer.controllingPersons', null, true],
        'Account' => ['account', null, false],
        'Applicant' => ['account.holders', null, true],
        'Cheque book' => ['account.chequeBooks', null, true],
        'Debit card' => ['account.debitCards', null, true],
        'Due diligence' => ['account.dueDiligences', null, false],
        'Beneficial owner' => ['account.ultimateBeneficialOwners', null, true],
    ];

    /** Longest value kept in a history line. */
    private const MAX = 200;

    /**
     * @return array<string, string>
     */
    public static function capture(AccountOpeningRequest $request): array
    {
        $request = $request->fresh() ?? $request;
        $flat = [];

        foreach (self::SECTIONS as $label => [$path, $key, $many]) {
            foreach (self::resolve($request, $path) as $index => $model) {
                $rowLabel = $many ? self::rowLabel($label, $model, $key, $index) : $label;

                foreach (self::attributes($model) as $attribute => $value) {
                    $flat[$rowLabel.' > '.self::label($attribute)] = $value;
                }
            }
        }

        // Many-to-many choices recorded on the due diligence (income sources, purposes ...).
        foreach (self::resolve($request, 'account.dueDiligences') as $dueDiligence) {
            foreach (['incomeSources' => 'Income sources', 'wealthSources' => 'Wealth sources', 'accountPurposes' => 'Account purposes', 'counterPartyTypes' => 'Counter parties'] as $relation => $label) {
                if (method_exists($dueDiligence, $relation)) {
                    $flat['Due diligence > '.$label] = $dueDiligence->{$relation}->pluck('name')->sort()->implode(', ');
                }
            }
        }

        return $flat;
    }

    /**
     * What changed between two snapshots: label => ['old' => ..., 'new' => ...].
     *
     * @param  array<string, string>  $before
     * @param  array<string, string>  $after
     * @return array<string, array{old: string|null, new: string|null}>
     */
    public static function diff(array $before, array $after): array
    {
        $changes = [];

        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $label) {
            $old = $before[$label] ?? null;
            $new = $after[$label] ?? null;

            if (self::blank($old) && self::blank($new)) {
                continue;
            }

            if ($old !== $new) {
                $changes[$label] = ['old' => self::blank($old) ? null : $old, 'new' => self::blank($new) ? null : $new];
            }
        }

        return $changes;
    }

    private static function blank(?string $value): bool
    {
        return $value === null || $value === '';
    }

    /**
     * @return Collection<int, Model>
     */
    private static function resolve(AccountOpeningRequest $request, string $path): Collection
    {
        if ($path === '') {
            return collect([$request]);
        }

        $current = collect([$request]);

        foreach (explode('.', $path) as $relation) {
            $current = $current->flatMap(function (Model $model) use ($relation): Collection {
                if (! method_exists($model, $relation)) {
                    return collect();
                }

                $related = $model->{$relation};

                return $related instanceof Collection ? $related->values() : collect($related ? [$related] : []);
            });
        }

        return $current->values();
    }

    private static function rowLabel(string $section, Model $model, ?string $key, int $index): string
    {
        $name = $key !== null && $model->getAttribute($key) ? Str::headline((string) $model->getAttribute($key)).' ' : '';
        $position = $model->getAttribute('sequence') ?? ($index + 1);

        return $section.' ('.$name.$position.')';
    }

    /**
     * @return array<string, string>
     */
    private static function attributes(Model $model): array
    {
        $values = [];

        foreach ($model->getAttributes() as $attribute => $raw) {
            if (in_array($attribute, self::SKIP, true)) {
                continue;
            }

            $value = $model->getAttribute($attribute);

            if (str_ends_with($attribute, '_path')) {
                $values[$attribute] = $value ? 'file on record' : '';

                continue;
            }

            if (str_ends_with($attribute, '_id')) {
                $values[$attribute] = self::related($model, $attribute) ?? (string) ($value ?? '');

                continue;
            }

            $values[$attribute] = self::stringify($value);
        }

        return $values;
    }

    private static function related(Model $model, string $attribute): ?string
    {
        $relation = Str::camel(Str::beforeLast($attribute, '_id'));

        if (! method_exists($model, $relation)) {
            return null;
        }

        $relationship = $model->{$relation}();

        if (! $relationship instanceof BelongsTo) {
            return null;
        }

        $related = $model->{$relation};

        if (! $related) {
            return '';
        }

        foreach (['name', 'full_name', 'business_name', 'title', 'code'] as $column) {
            if (filled($related->getAttribute($column))) {
                return (string) $related->getAttribute($column);
            }
        }

        return (string) $related->getKey();
    }

    private static function stringify(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? 'Yes' : 'No',
            $value instanceof \DateTimeInterface => $value->format('d.m.Y'),
            is_array($value) => Str::limit(json_encode($value) ?: '', self::MAX),
            default => Str::limit((string) $value, self::MAX),
        };
    }

    private static function label(string $attribute): string
    {
        return Str::headline(Str::beforeLast($attribute, '_id') ?: $attribute);
    }
}
