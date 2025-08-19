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
        Schema::create('complaint_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('category_name', 100); // Name/title of the complaint category (e.g., "Product Quality", "Customer Service")
            $table->unsignedBigInteger('parent_category_id')->nullable(); // For subcategories
            $table->text('description')->nullable();
            $table->enum('default_priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->integer('sla_hours')->default(24); // Expected resolution time in hours
            $table->boolean('is_active')->default(true);
            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();
            // Self-referencing foreign key for parent category
            $table->foreign('parent_category_id')->references('id')->on('complaint_categories');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
