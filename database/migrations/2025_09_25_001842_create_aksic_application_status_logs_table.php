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
            $table->unsignedBigInteger(column: 'aksic_id');
            $table->unsignedBigInteger('applicant_id');
            $table->string('old_status');
            $table->string('new_status');
            $table->string('changed_by_type')->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
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
