<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Step-wise rules for the Account Opening wizard (AOF_STRICT_STEPS).
 *
 * With strict steps on, a step opens only when every step before it is complete,
 * and "Save & Continue" only moves on once the step just saved is complete.
 */
final class AccountOpeningSteps
{
    public static function strict(): bool
    {
        return (bool) config('account_opening.strict_steps', true);
    }

    /**
     * The first incomplete step that comes before $step, or null when $step may be opened.
     *
     * @param  array<string, array{label: string, complete: bool}>  $progress  steps in wizard order
     */
    public static function blockedBy(array $progress, string $step): ?string
    {
        if (! self::strict()) {
            return null;
        }

        foreach ($progress as $key => $row) {
            if ($key === $step) {
                return null;
            }

            if (! $row['complete']) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Steps that cannot be opened yet, keyed by step => the step that blocks it.
     *
     * @param  array<string, array{label: string, complete: bool}>  $progress
     * @return array<string, string>
     */
    public static function locked(array $progress): array
    {
        $locked = [];

        foreach (array_keys($progress) as $key) {
            if ($blocker = self::blockedBy($progress, $key)) {
                $locked[$key] = $blocker;
            }
        }

        return $locked;
    }

    /**
     * The step to work on next: the first incomplete one (or the last when all are done).
     *
     * @param  array<string, array{label: string, complete: bool}>  $progress
     */
    public static function next(array $progress): string
    {
        foreach ($progress as $key => $row) {
            if (! $row['complete']) {
                return $key;
            }
        }

        return array_key_last($progress);
    }
}
