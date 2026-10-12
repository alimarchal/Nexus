<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Government / organisation AOFs record the Economic Sector Code as typed text
     * instead of picking it from the economic_sectors list.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('economic_sector_code', 10)->nullable()->after('economic_sector_id')
                ->comment('Entity AOF: Economic Sector Code typed by the user (replaces the dropdown)');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('economic_sector_code');
        });
    }
};
