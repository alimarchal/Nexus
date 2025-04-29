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
        Schema::create('stationery_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printed_stationery_id')->constrained()->onDelete('cascade');
            $table->enum('stock_out_to', ['Branch', 'Region','Division']);
            $table->foreignId('division_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('type', ['opening_balance', 'in', 'out']);
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2)->nullable(); // Unit price when purchased
            $table->integer('balance_after_transaction');  // Running balance
            $table->date('transaction_date');
            $table->string('reference_number')->nullable(); // Invoice/PO/Requisition number
            $table->string('document_path')->nullable(); // Path to the stored PO/document image
            $table->text('notes')->nullable();
            $table->userTracking();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stationery_transactions');
    }
};
