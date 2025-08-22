<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_witness_harassment', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('complaint_id'); // foreign key
    $table->string('accused_name');
    $table->string('accused_designation');
    $table->text('accused_id')->nullable();
    
    // single field for date and time
    $table->dateTime('incident_datetime'); // ye datetime store karega
    
    $table->string('incident_location');
    $table->string('harassment_type');
    $table->json('witnesses')->nullable(); // multiple witnesses
    $table->timestamps();

    $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_witness_harassment');
    }
};
