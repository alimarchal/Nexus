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
        Schema::create('complaints', function (Blueprint $table) {
            // Primary identifier - UUID
            $table->uuid('id')->primary();
            // Business identifier - unique complaint reference for users/reports
            $table->string('complaint_number', 20)->unique()->nullable();

            // Core complaint details
            $table->string('title', 255)->nullable(); // Brief summary for quick identification
            $table->text('description')->nullable(); // Detailed complaint information
            $table->string('category')->nullable(); // Categorization for reporting/filtering

            // Business logic fields with modern Laravel enums
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium'); // SLA and resource allocation
            $table->enum('status', ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed', 'Reopened'])->default('Open'); // Workflow tracking
            $table->enum('source', ['Phone', 'Email', 'Portal', 'Walk-in', 'Other'])->default('Phone'); // Channel analytics

            // Complainant contact details - nullable for anonymous complaints
            $table->string('complainant_name', 100)->nullable();
            $table->string('complainant_email', 100)->nullable();
            $table->string('complainant_phone', 20)->nullable();
            $table->string('complainant_account_number', 50)->nullable(); // Business context linkage

            // Location/branch association - using modern foreign key syntax
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Assignment tracking - who handles what and when
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable(); // Assignment timeline

            // Resolution tracking
            $table->text('resolution')->nullable(); // Final resolution details
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable(); // Resolution timeline

            // Workflow completion tracking
            $table->timestamp('closed_at')->nullable(); // Final closure timestamp

            // SLA (Service Level Agreement) monitoring
            $table->timestamp('expected_resolution_date')->nullable(); // Target deadline
            $table->boolean('sla_breached')->default(false); // Performance tracking
            $table->text('reopen_reason')->nullable();
            $table->string('priority_change_reason', 500)->nullable();
            $table->string('status_change_reason', 500)->nullable();

            // Soft deletes - preserve data integrity while allowing "deletion"
            $table->softDeletes();

            // Standard Laravel timestamps (created_at, updated_at)
            $table->timestamps();

            // Database performance optimization
            $table->index(['status', 'priority']); // Common filtering
            $table->index('complaint_number'); // Lookup optimization
            $table->index('assigned_to'); // Assignment queries
            $table->index(['created_at', 'status']); // Timeline reports
            $table->index('sla_breached'); // Performance monitoring
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');

    }
};