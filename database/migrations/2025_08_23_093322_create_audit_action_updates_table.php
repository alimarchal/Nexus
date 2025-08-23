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
        Schema::create('audit_action_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_action_id')->constrained('audit_actions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('update_text');
            $table->enum('status_after', ['open', 'in_progress', 'implemented', 'verified', 'closed', 'cancelled'])->nullable()->index();
            $table->boolean('is_system_generated')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['audit_action_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_action_updates');
    }
};
