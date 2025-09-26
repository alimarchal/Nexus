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
        Schema::create('aksic_application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('aksic_application_id')->nullable()->constrained('aksic_applications')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('aksic_id')->nullable();
            $table->integer('applicant_id')->nullable();
            $table->string('old_status');
            $table->string('new_status');
            $table->string('changed_by_type')->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->text('remarks')->nullable();
            $table->json('status_json')->nullable();
            $table->timestamps();

            // Create foreign key constraint for applicant_id referencing aksic_applications.applicant_id
            // Note: aksic_id is just a reference field from API, not a foreign key since it matches applicant_id
            $table->foreign('applicant_id')->references('applicant_id')->on('aksic_applications')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksic_application_status_logs');
    }
};
