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
        Schema::create('dispatch_registers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('date')->useCurrent();
            $table->string('dispatch_no')->unique();
            $table->string('particulars');
            $table->string('address');
            // from division id
            $table->foreignId('division_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('receipt_no')->nullable();
            $table->string('name_of_courier_service')->nullable();
            $table->string('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->text('meta_data')->nullable();
            $table->userTracking();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_registers');
    }
};
