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
            $table->id();
            $table->morphs('auditable');
            $table->enum('from_status', ['planned', 'in_progress', 'reporting', 'issued', 'closed', 'cancelled', 'open', 'implemented', 'verified'])->nullable();
            $table->enum('to_status', ['planned', 'in_progress', 'reporting', 'issued', 'closed', 'cancelled', 'open', 'implemented', 'verified']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id', 'changed_at']);
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
