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
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('division_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('manager_user_id')->constrained('users');
            $table->string('title')->comment('designation')->nullable();
            $table->foreignId('update_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
