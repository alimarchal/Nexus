<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Migrations that exist in the code but have not been run on this database.
 * Shown to super admin on the dashboard so a deploy that forgot
 * "php artisan migrate" (missing permissions, columns ...) is noticed at once.
 */
final class PendingMigrations
{
    /**
     * @return array<int, string> migration names, oldest first
     */
    public static function list(): array
    {
        return Cache::remember('pending-migrations:'.self::fingerprint(), 300, function (): array {
            return rescue(function (): array {
                $migrator = app('migrator');
                if (! $migrator->repositoryExists()) {
                    return [];
                }
                $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
                $ran = $migrator->getRepository()->getRan();

                return array_values(array_diff(array_keys($files), $ran));
            }, [], false);
        });
    }

    /**
     * Changes when a migration file is added or a migration is run, so the
     * warning appears at once and disappears right after "php artisan migrate".
     */
    private static function fingerprint(): string
    {
        $files = glob(database_path('migrations/*.php')) ?: [];
        $ran = rescue(fn () => DB::table(config('database.migrations.table', 'migrations'))->count(), 0, false);

        return md5(count($files).'|'.basename((string) end($files)).'|'.$ran);
    }
}
