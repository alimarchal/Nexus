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
        Schema::create('audit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('frequency', ['once', 'monthly', 'quarterly', 'semiannual', 'annual'])->default('once');
            $table->date('scheduled_date');
            $table->date('next_run_date')->nullable();
            $table->boolean('is_generated')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['frequency', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_schedules');
    }
};
