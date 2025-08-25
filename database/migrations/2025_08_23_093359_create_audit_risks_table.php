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
        Schema::create('audit_risks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('audit_finding_id')->nullable()->constrained('audit_findings')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('likelihood', ['low', 'medium', 'high'])->nullable();
            $table->enum('impact', ['low', 'medium', 'high'])->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->nullable()->index();
            $table->enum('status', ['identified', 'assessed', 'treated', 'retired'])->default('identified')->index();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['audit_id', 'risk_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_risks');
    }
};
