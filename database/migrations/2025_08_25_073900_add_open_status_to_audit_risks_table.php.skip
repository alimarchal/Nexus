<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'open' to the status enum for audit_risks table
        DB::statement("ALTER TABLE `audit_risks` MODIFY `status` ENUM('open', 'identified', 'assessed', 'treated', 'retired') DEFAULT 'identified' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `audit_risks` MODIFY `status` ENUM('identified', 'assessed', 'treated', 'retired') DEFAULT 'identified' NOT NULL");
    }
};