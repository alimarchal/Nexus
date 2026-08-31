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
        Schema::create('boxes', function (Blueprint $table) {
            // UUID primary key for consistency with file_management_systems
            $table->uuid('id')->primary();

            // Auto-generated box number: BOX-2026-001
            $table->string('box_number')->unique();

            // Polymorphic relation to Branch, Region, Division, HeadOffice
            $table->morphs('boxable');

            // Status: open (accepting files), closed (full/sealed), archived
            $table->enum('status', ['open', 'closed', 'full'])->default('open');

            // Physical storage location (e.g., "Vault-A-Shelf-2")
            $table->string('location')->nullable();

            // Current file count in this box
            $table->integer('file_count')->default(0);

            // Maximum capacity of this box
            $table->integer('capacity')->default(100);

            // When this box was sealed/closed
            $table->timestamp('archived_date')->nullable();

            // Who created this box
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['boxable_type', 'boxable_id'], 'idx_boxable');
            $table->index('status', 'idx_box_status');
            $table->index('box_number', 'idx_box_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
