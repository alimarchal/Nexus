<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * A production database that was imported and migrated has no Account Opening Form options (account types,
 * currencies, special categories, sources of funds, cards ...), because seeders do not run with migrations.
 * Loading them here means every `php artisan migrate --force` leaves the form usable; the load is idempotent.
 * To ship new or changed options later, add a migration like this one that calls the same command.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The test suite seeds what each test needs itself.
        if (app()->runningUnitTests()) {
            return;
        }

        Artisan::call('aof:sync-reference-data', ['--no-interaction' => true]);
    }

    public function down(): void
    {
        // The options are shared reference data; rolling back never removes them.
    }
};
