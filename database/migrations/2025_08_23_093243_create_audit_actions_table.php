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
        Schema::create('audit_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('audit_finding_id')->nullable()->constrained('audit_findings')->nullOnDelete();
            $table->string('reference_no')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('action_type', ['corrective', 'preventive', 'remediation', 'improvement'])->default('corrective')->index();
            $table->enum('status', ['open', 'in_progress', 'implemented', 'verified', 'closed', 'cancelled'])->default('open')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
            $table->date('due_date')->nullable()->index();
            $table->date('completed_date')->nullable();
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
        Schema::dropIfExists('audit_actions');
    }
};
