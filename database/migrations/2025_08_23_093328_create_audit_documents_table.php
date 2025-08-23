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
        Schema::create('audit_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('audit_finding_id')->nullable()->constrained('audit_findings')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('category')->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['audit_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_documents');
    }
};
