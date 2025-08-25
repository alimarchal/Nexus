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
        Schema::create('audit_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('auditable');
            // Restrict status enums to lifecycle states only (do NOT include role values)
            $table->enum('from_status', ['planned', 'in_progress', 'reporting', 'issued', 'closed', 'cancelled'])->nullable();
            $table->enum('to_status', ['planned', 'in_progress', 'reporting', 'issued', 'closed', 'cancelled']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            // Short composite index name to satisfy MySQL/MariaDB identifier length limits
            $table->index(['auditable_type', 'auditable_id', 'changed_at'], 'ash_auditable_changed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_status_histories');
    }
};
