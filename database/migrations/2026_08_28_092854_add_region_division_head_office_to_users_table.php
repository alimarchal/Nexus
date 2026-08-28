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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('division_id')->nullable()->after('region_id')->constrained()->nullOnDelete();
            $table->foreignId('head_office_id')->nullable()->after('division_id')->constrained('head_offices')->nullOnDelete();
            $table->boolean('is_president_office')->default(false)->after('head_office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('head_office_id');
            $table->dropColumn('is_president_office');
        });
    }
};
