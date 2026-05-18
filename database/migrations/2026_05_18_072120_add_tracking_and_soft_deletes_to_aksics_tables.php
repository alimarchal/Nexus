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
        Schema::table('aksics', function (Blueprint $table) {
            if (! Schema::hasColumn('aksics', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('aksics', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('aksics', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('aksic_amortizations', function (Blueprint $table) {
            if (! Schema::hasColumn('aksic_amortizations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('aksic_amortizations', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('aksic_amortizations', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aksic_amortizations', function (Blueprint $table) {
            if (Schema::hasColumn('aksic_amortizations', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('aksic_amortizations', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }

            if (Schema::hasColumn('aksic_amortizations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('aksics', function (Blueprint $table) {
            if (Schema::hasColumn('aksics', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('aksics', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }

            if (Schema::hasColumn('aksics', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
