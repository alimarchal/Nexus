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
        Schema::create('file_management_systems', function (Blueprint $table) {
            // Stores the unique UUID identifier for each document category.
            $table->uuid('id')->primary();

            // digital_id      VARCHAR(40)  NOT NULL UNIQUE,     -- e.g. BR014-20260827-CASH-000123
            $table->string('digital_id', 40)->unique();

            // document_categories foreign id      BIGINT UNSIGNED NOT NULL, $table->foreignUuid('aksic_application_id')->nullable()->constrained('aksic_applications')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('document_category_id')->constrained('document_categories')->nullOnDelete()->cascadeOnUpdate();

            // Adds audit tracking columns such as created_by/updated_by to record who created or updated the category.
            $table->userTracking();

            // Adds a deleted_at column so records can be soft-deleted without being permanently removed from the database.
            $table->softDeletes();

            // Adds created_at and updated_at timestamps for record lifecycle tracking.
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_management_systems');
    }
};
