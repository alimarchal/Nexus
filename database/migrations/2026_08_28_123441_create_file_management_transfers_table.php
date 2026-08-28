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
        Schema::create('file_management_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('file_management_system_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('source_fileable_type');
            $table->unsignedBigInteger('source_fileable_id');
            $table->string('destination_fileable_type');
            $table->unsignedBigInteger('destination_fileable_id');
            $table->foreignId('recipient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['file_management_system_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_management_transfers');
    }
};
