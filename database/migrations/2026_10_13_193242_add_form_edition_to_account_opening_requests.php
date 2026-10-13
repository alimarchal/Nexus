<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which Account Opening Form a request was made with: the current ("new") one or the old BAJK paper form.
 * Every request that exists today was made with the new form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_opening_requests', function (Blueprint $table): void {
            $table->string('form_edition', 10)->default('new')->after('aof_form_type')
                ->comment('new = current AOF, old = old BAJK paper AOF');
            $table->index('form_edition');
        });
    }

    public function down(): void
    {
        Schema::table('account_opening_requests', function (Blueprint $table): void {
            $table->dropIndex(['form_edition']);
            $table->dropColumn('form_edition');
        });
    }
};
