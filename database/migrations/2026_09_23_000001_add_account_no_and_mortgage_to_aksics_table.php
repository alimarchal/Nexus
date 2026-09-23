<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AKSIC Portal Change Requirements (demo 22-Sep-2026)
 *   #11 Account No -- bank account the loan is booked against, for branch
 *       cross-reference.
 *   #9  Mortgage   -- collateral / mortgage details recorded against the loan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->string('account_no', 50)->nullable()->after('application_no')
                ->comment('Change #11: bank account number the loan is booked against');
            $table->text('mortgage')->nullable()->after('personal_guarantees')
                ->comment('Change #9: mortgage / collateral details');
            $table->index('account_no');
        });
    }

    public function down(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->dropIndex(['account_no']);
            $table->dropColumn(['account_no', 'mortgage']);
        });
    }
};
