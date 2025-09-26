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
        Schema::create('aksic_application_education', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('aksic_application_id')->nullable()->constrained('aksic_applications')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('applicant_id')->nullable();
            $table->string('education_level');
            $table->string('degree_title')->nullable();
            $table->string('institute')->nullable();
            $table->year('passing_year')->nullable();
            $table->string('grade_or_percentage')->nullable();
            $table->json('educations_json')->nullable();
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
        Schema::dropIfExists('aksic_application_education');
    }
};
