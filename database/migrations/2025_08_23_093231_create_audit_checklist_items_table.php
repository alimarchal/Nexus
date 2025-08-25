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
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('audit_checklist_items')->nullOnDelete();
            $table->string('reference_code')->nullable()->index();
            $table->string('title');
            $table->text('criteria')->nullable();
            $table->text('guidance')->nullable();
            $table->enum('response_type', ['yes_no', 'compliant_noncompliant', 'rating', 'text', 'numeric', 'evidence'])->default('yes_no');
            $table->unsignedTinyInteger('max_score')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['audit_type_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_items');
    }
};
