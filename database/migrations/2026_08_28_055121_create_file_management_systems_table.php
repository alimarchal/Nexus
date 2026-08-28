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
            // Stores the unique UUID identifier for each document record.
            $table->uuid('id')->primary();

            // digital_id      VARCHAR(40)  NOT NULL UNIQUE,     -- e.g. BR014-20260827-CASH-000123
            $table->string('digital_id', 40)->unique();

            // document_categories foreign id
            $table->foreignUuid('file_category_id')->constrained('file_categories')->nullOnDelete()->cascadeOnUpdate();

            // Polymorphic org-unit scope: branch, region, division, or head-office.
            // Creates fileable_type (e.g. 'App\Models\Branch') + fileable_id (uuid).
            $table->uuidMorphs('fileable');

            // The date of the underlying work/record being scanned (not the upload date).
            $table->date('document_date');

            // Optional human-readable title/description of the document.
            $table->string('title')->nullable();

            // Adds audit tracking columns such as created_by/updated_by to record who created or updated the record.
            $table->userTracking();

            // Adds a deleted_at column so records can be soft-deleted without being permanently removed from the database.
            $table->softDeletes();

            // Adds created_at and updated_at timestamps for record lifecycle tracking.
            $table->timestamps();

            // Fast lookups: all documents for a given org unit, filtered by date.
            $table->index(['fileable_type', 'fileable_id', 'document_date'], 'idx_fileable_date');

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
