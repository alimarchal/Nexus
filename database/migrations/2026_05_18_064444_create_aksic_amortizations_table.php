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
        Schema::create('aksic_amortizations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('aksic_id')->constrained('aksics')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('installment_no');
            $table->date('period_start_date');
            $table->date('due_date');
            $table->unsignedInteger('days');
            $table->decimal('principal_amount_os', 20, 6);
            $table->decimal('installment_per_month', 20, 6);
            $table->decimal('product', 20, 6);
            $table->decimal('interest_rate_per_month', 20, 6);
            $table->decimal('total_interest', 20, 6);
            $table->decimal('total_rate', 8, 6);
            $table->decimal('total_installment', 20, 6);
            $table->decimal('principal_balance_after_installment', 20, 6);
            $table->timestamps();

            $table->unique(['aksic_id', 'installment_no']);
            $table->index(['aksic_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksic_amortizations');
    }
};
