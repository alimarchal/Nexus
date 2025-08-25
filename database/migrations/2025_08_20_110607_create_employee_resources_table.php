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
        Schema::create('employee_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->cascadeOnUpdate();
            $table->string('resource_no')->nullable()->unique();   // Manually entered no (can be null)
            $table->string('resource_number')->unique();           // System generated immutable reference
            $table->string('reference_no')->nullable()->unique();  // Business duplicate (optional)
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->userTracking();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_resources');
    }
};
