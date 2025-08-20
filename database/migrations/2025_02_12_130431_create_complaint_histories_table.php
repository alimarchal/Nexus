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
        Schema::create('complaint_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('complaint_id');
            $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->enum('action_type', [
                'Created',
                'Assigned',
                'Reassigned',
                'Status Changed',
                'Comment Added',
                'File Attached',
                'Resolved',
                'Reopened',
                'Closed',
                'Priority Changed',
                'Category Changed',
                'Feedback',
                'Escalated'
            ]);
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('status_id')->constrained('complaint_status_types');
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamp('performed_at')->useCurrent();

            $table->string('attachment')->nullable();
            $table->enum('complaint_type', [
                'Internal',
                'Customer',
                'System',
            ])->default('Internal'); // Type of complaint for better categorization
            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();
        });



        // Schema::create('complaint_categories', function (Blueprint $table) {
        //     // Primary identifier
        //     $table->id();
        //     // Category name - required for identification
        //     $table->string('category_name', 100);
        //     // Self-referencing foreign key for hierarchical categories
        //     $table->unsignedBigInteger('parent_category_id')->nullable();
        //     // Detailed description of category scope
        //     $table->text('description')->nullable();
        //     // Default priority for complaints in this category
        //     $table->enum('default_priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
        //     // SLA hours - expected resolution time for this category
        //     $table->integer('sla_hours')->default(24);
        //     // Active status - allows soft deactivation without deletion
        //     $table->boolean('is_active')->default(true);
        //     $table->userTracking(); // Tracks who created/modified records
        //     // Timestamp for creation tracking use from created_at default laravel timestamp
        //     $table->timestamps();
        //     $table->softDeletes(); // Soft delete for audit trail

        //     // Self-referencing foreign key constraint
        //     $table->foreign('parent_category_id')->references('category_id')->on('complaint_categories')->nullOnDelete();
        //     // Performance indexes
        //     $table->index('parent_category_id');
        //     $table->index(['is_active', 'category_name']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_histories');
        Schema::dropIfExists('complaint_categories');
    }
};