<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');  // Reference to the complaint being watched
            $table->foreignId('user_id')->constrained('users');  // User who wants to watch/follow this complaint for updates

            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();
            // Unique constraint to prevent duplicate watchers
            // Example: User ID 5 can only watch Complaint ID 123 once
            $table->unique(['complaint_id', 'user_id'], 'unique_watcher');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_watchers');
    }
};
