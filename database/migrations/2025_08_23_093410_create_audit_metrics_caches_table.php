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
        Schema::create('audit_metrics_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('metric_key');
            $table->decimal('metric_value', 12, 4)->nullable();
            $table->unsignedBigInteger('numeric_value')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('calculated_at');
            $table->unsignedInteger('ttl_seconds')->default(3600);
            $table->timestamps();
            $table->unique(['audit_id', 'metric_key']);
            $table->index(['metric_key', 'calculated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_metrics_caches');
    }
};
