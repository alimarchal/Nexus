<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('reports', function (Blueprint $table) {
        $table->id();  // Creates an auto-incrementing ID
        $table->date('date');  // For storing the date
        $table->string('branch_name');  // For storing branch name
        $table->string('branch_code');  // For storing branch code
        $table->boolean('status')->default(0);  // For storing status, with a default value
        $table->timestamps();  // For created_at and updated_at timestamps
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
