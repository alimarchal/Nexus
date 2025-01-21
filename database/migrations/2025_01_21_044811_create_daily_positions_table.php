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
        Schema::create('daily_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');

            $table->decimal('consumer', 15, 3)->default(0.000);
            $table->decimal('commercial', 15, 3)->default(0.000);
            $table->decimal('micro', 15, 3)->default(0.000);
            $table->decimal('agri', 15, 3)->default(0.000);
            $table->decimal('noOfAccount', 15, 3)->default(0.000);
            $table->decimal('profit', 15, 3)->default(0.000);
            $table->decimal('totalAssets', 15, 3)->default(0.000);


            $table->decimal('govtDeposit', 15, 3)->default(0.000);
            $table->decimal('privateDeposit', 15, 3)->default(0.000);
            $table->decimal('totalDeposits', 15, 3)->default(0.000);

            $table->decimal('casa', 15, 3)->default(0.000);
            $table->decimal('tdr', 15, 3)->default(0.000);
            $table->decimal('totalCasaTdr', 15, 3)->default(0.000);
            $table->decimal('noOfAcc', 15, 3)->default(0.000);
            $table->decimal('grandTotal', 15, 3)->default(0.000);


            $table->date('target_start_date')->nullable();
            $table->date('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->timestamps();

            // Ensure only one target per branch per fiscal year
            $table->unique(['branch_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_positions');
    }
};
