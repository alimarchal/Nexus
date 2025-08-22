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
        Schema::create('complaint_escalations', function (Blueprint $table) {


            $table->id();
            $table->uuid('complaint_id'); // Reference to the complaint being escalated
            $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->foreignId('escalated_from')->constrained('users'); // User who escalated the complaint
            $table->foreignId('escalated_to')->constrained('users');   // User/role the complaint was escalated to
            $table->integer('escalation_level')->default(1); // Level of escalation (1, 2, 3 for different hierarchy levels)
            $table->timestamp('escalated_at')->useCurrent(); // When the escalation occurred
            $table->timestamp('resolved_at')->nullable(); // When the escalation was resolved (nullable if still pending)
            $table->text('escalation_reason'); // Reason for escalating the complaint
            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();

            // Index for query optimization
            $table->index(['complaint_id', 'escalation_level'], 'idx_complaint_escalations');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_escalations');
    }
};
