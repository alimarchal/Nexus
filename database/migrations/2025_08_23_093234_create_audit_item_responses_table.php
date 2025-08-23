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
        Schema::create('audit_item_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('response_value', ['yes', 'no', 'compliant', 'noncompliant', 'na'])->nullable();
            $table->text('comment')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamps();
            $table->unique(['audit_id', 'audit_checklist_item_id']);
            $table->index(['audit_checklist_item_id', 'responded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_item_responses');
    }
};
