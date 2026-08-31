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
            $table->foreignId('current_custodian_id')
                ->nullable()
                ->after('updated_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            if (Schema::hasColumn('file_management_systems', 'current_custodian_id')) {
                try {
                    $table->dropConstrainedForeignId('current_custodian_id');
                } catch (Throwable $e) {
                    // MySQL may already have removed the foreign key in a later migration rollback.
                }
            }
        });

        Schema::table('file_management_systems', function (Blueprint $table) {
            if (Schema::hasColumn('file_management_systems', 'current_custodian_id')) {
                $table->dropColumn('current_custodian_id');
            }
        });
    }
};
