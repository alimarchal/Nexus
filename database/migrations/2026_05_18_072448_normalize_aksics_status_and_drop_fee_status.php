<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('aksics', 'status')) {
            DB::table('aksics')
                ->whereNotIn('status', ['Pending', 'Approved'])
                ->update(['status' => 'Pending']);

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE aksics MODIFY status ENUM('Pending', 'Approved') NOT NULL DEFAULT 'Pending'");
            }
        }

        Schema::table('aksics', function (Blueprint $table) {
            if (Schema::hasColumn('aksics', 'fee_status')) {
                $table->dropColumn('fee_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            if (! Schema::hasColumn('aksics', 'fee_status')) {
                $table->enum('fee_status', ['paid', 'unpaid'])->default('unpaid')->after('cnic_back_url');
            }
        });

        if (Schema::hasColumn('aksics', 'status')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE aksics MODIFY status ENUM('NotCompleted', 'Pending', 'Forwarded', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending'");
            }
        }
    }
};
