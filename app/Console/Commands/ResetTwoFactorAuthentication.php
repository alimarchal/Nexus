<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Clears every user's two-factor secret, recovery codes and confirmation.
 *
 * Needed after APP_KEY changes: the secrets were encrypted with the old key and
 * can no longer be decrypted ("The MAC is invalid" on /two-factor-challenge).
 * Users sign in without 2FA and turn it on again from their profile.
 */
class ResetTwoFactorAuthentication extends Command
{
    protected $signature = 'auth:reset-two-factor';

    protected $description = 'Clear all two-factor secrets and recovery codes (use after APP_KEY changed)';

    public function handle(): int
    {
        $count = User::withTrashed()
            ->whereNotNull('two_factor_secret')
            ->update([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]);

        $this->info("Two-factor authentication cleared for {$count} user(s).");

        return self::SUCCESS;
    }
}
