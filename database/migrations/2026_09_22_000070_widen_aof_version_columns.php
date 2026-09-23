<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The AOF version identifies which printed form was signed, e.g.
 * "BAJK-AOF-Govt-Public-Private-28-Jan-2026" (40 characters). The original
 * 30-character column was too small for the entity form's identifier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->string('aof_version', 60)->nullable()->change();
        });

        Schema::table('account_opening_requests', function (Blueprint $table): void {
            $table->string('aof_version', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->string('aof_version', 30)->nullable()->change();
        });

        Schema::table('account_opening_requests', function (Blueprint $table): void {
            $table->string('aof_version', 30)->nullable()->change();
        });
    }
};
