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
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('audit_item_response_id')->nullable()->constrained('audit_item_responses')->nullOnDelete();
            $table->string('reference_no')->nullable()->index();
            $table->enum('category', ['process', 'compliance', 'safety', 'financial', 'operational', 'other'])->default('other');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->index();
            $table->enum('status', ['open', 'in_progress', 'implemented', 'verified', 'closed', 'void'])->default('open')->index();
            $table->text('title');
            $table->text('description')->nullable();
            $table->text('risk_description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('recommendation')->nullable();
            $table->date('target_closure_date')->nullable()->index();
            $table->date('actual_closure_date')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['audit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
    }
};
