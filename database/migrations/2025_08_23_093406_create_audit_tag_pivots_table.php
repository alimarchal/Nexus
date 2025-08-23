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
        Schema::create('audit_tag_pivots', function (Blueprint $table) {
            $table->foreignUuid('audit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('audit_tag_id')->constrained('audit_tags')->cascadeOnDelete();
            $table->foreignId('tagged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->primary(['audit_id', 'audit_tag_id']);
            $table->index(['audit_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_tag_pivots');
    }
};
