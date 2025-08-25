<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaint_witnesses', function (Blueprint $table) {
            $table->id();
            $table->uuid('complaint_id');
            $table->string('employee_number', 50)->nullable();
            $table->string('name', 150);
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('statement')->nullable();
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('complaints')->cascadeOnDelete();
            $table->index(['complaint_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_witnesses');
    }
};
