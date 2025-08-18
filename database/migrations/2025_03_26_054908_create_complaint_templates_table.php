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
        Schema::create('complaint_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100); // Name of the template (e.g., "Product Return Acknowledgment", "Service Issue Response")
            $table->unsignedBigInteger('category_id')->nullable(); // Associated complaint category (e.g., "Product Quality", "Customer Service")
            $table->string('template_subject', 255)->nullable(); // Email subject line template (e.g., "Re: Your Product Quality Complaint #{{complaint_id}}")
            $table->text('template_body'); // Email/response body template with placeholders (e.g., "Dear {{customer_name}}, Thank you for contacting us...")
            $table->boolean('is_active')->default(true); // Whether this template is currently available for use
            $table->userTracking(); // Tracks who created/modified records
            $table->softDeletes(); // Soft delete for audit trail
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_templates');
    }
};
