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
        Schema::create('complaint_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade'); // Reference to the complaint these metrics belong to
            $table->integer('time_to_first_response')->nullable(); // Time from complaint creation to first response (in minutes) - Example: 45 minutes
            $table->integer('time_to_resolution')->nullable(); // Time from complaint creation to resolution (in minutes) - Example: 1440 minutes (24 hours)
            $table->integer('reopened_count')->default(0); // Number of times complaint was reopened - Example: 0 (never reopened), 2 (reopened twice)
            $table->integer('escalation_count')->default(0); // Number of times complaint was escalated - Example: 1 (escalated once to manager)
            $table->integer('assignment_count')->default(0); // Number of times complaint was reassigned - Example: 3 (passed between 3 different agents)
            $table->decimal('customer_satisfaction_score', 3, 2)->nullable(); // Customer satisfaction rating on 1-5 scale - Example: 4.50, 2.75, 5.00
            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();

            // Index for query optimization
            $table->index(['complaint_id'], 'idx_complaint_metrics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_metrics');
    }
};