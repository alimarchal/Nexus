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
            $table->unsignedBigInteger(column: 'aksic_id');
            $table->unsignedBigInteger('applicant_id');
            $table->string('education_level');
            $table->string('degree_title')->nullable();
            $table->string('institute')->nullable();
            $table->year('passing_year')->nullable();
            $table->string('grade_or_percentage')->nullable();
            $table->json('educations_json')->nullable();
            $table->timestamps();
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
