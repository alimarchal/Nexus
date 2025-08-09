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
        $connection = config('activitylog.database_connection') ?: config('database.default');
        $tableName = config('activitylog.table_name', 'activity_log');
        
        Schema::connection($connection)->table($tableName, function (Blueprint $table) {
            // Change subject_id to handle UUIDs (36 characters)
            $table->char('subject_id', 36)->nullable()->change();
            
            // Also fix causer_id if it needs to handle UUIDs
            $table->char('causer_id', 36)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('activitylog.database_connection') ?: config('database.default');
        $tableName = config('activitylog.table_name', 'activity_log');
        
        Schema::connection($connection)->table($tableName, function (Blueprint $table) {
            // Revert subject_id back to BIGINT UNSIGNED
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            
            // Revert causer_id back to BIGINT UNSIGNED
            $table->unsignedBigInteger('causer_id')->nullable()->change();
        });
    }
};