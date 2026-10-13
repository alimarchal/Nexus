<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\BajkAccountOpeningSeeder;
use Illuminate\Console\Command;

/**
 * Loads (or refreshes) every option list the Account Opening Form uses: account types, currencies, special
 * categories, sources of funds, card types, documents checklist and the rest. Safe to run at any time: it
 * only adds missing options and refreshes the existing ones by their code, it never deletes anything.
 */
class SyncAccountOpeningReferenceData extends Command
{
    protected $signature = 'aof:sync-reference-data';

    protected $description = 'Load or refresh the Account Opening Form option lists (safe to run on every deployment)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => BajkAccountOpeningSeeder::class, '--force' => true]);

        $this->info('Account Opening Form options are loaded.');

        return self::SUCCESS;
    }
}
