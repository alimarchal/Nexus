<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            // Manually assigned file number for the user's own convenience (e.g. HRMS/12/ABC), independent of the auto-generated digital_id.
            $table->string('file_no', 60)->nullable()->after('digital_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            $table->dropIndex(['file_no']);
            $table->dropColumn('file_no');
        });
    }
};
