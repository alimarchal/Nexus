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
        Schema::create('file_categories', function (Blueprint $table) {
            // Stores the unique UUID identifier for each document category.
            $table->uuid('id')->primary();

            // Stores the unique code used to identify the document category.
            $table->string('category_code', 20)->unique();

            // Stores the display name of the document category.
            $table->string('category_name', 100);

            // Indicates whether the document category is currently active.
            $table->enum('is_active', ['0', '1'])->default('1');

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
        Schema::dropIfExists('file_categories');
    }
};
