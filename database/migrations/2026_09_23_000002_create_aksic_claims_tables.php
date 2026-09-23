<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AKSIC Portal Change #5 -- Claim lodging by District / Branch-Region / Gender.
 *
 * aksic_claims       one lodged claim: the period it covers and the filters it
 *                    was lodged for (district / region / branch / gender, all
 *                    optional), with its totals and settlement status.
 * aksic_claim_items  one line per loan in the claim. District, region, branch
 *                    and gender are snapshotted at lodging time so MIS reports
 *                    stay correct even if the loan or branch is edited later.
 *
 * Claim amount per loan = markup of the schedule instalments falling due
 * inside the claim period. A loan cannot sit in two live claims whose periods
 * overlap (enforced in App\Services\AksicClaimService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aksic_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('claim_no', 30)->unique();
            $table->date('claim_date');
            $table->date('period_from');
            $table->date('period_to');

            // Filters the claim was lodged for (null = all).
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('gender', 20)->nullable();

            $table->unsignedInteger('total_loans')->default(0);
            $table->decimal('total_principal_outstanding', 20, 2)->default(0);
            $table->decimal('total_markup', 20, 2)->default(0);

            $table->enum('status', ['Lodged', 'Settled', 'Rejected'])->default('Lodged');
            $table->date('status_date')->nullable()->comment('Date settled / rejected');
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index(['period_from', 'period_to']);
            $table->index('status');
        });

        Schema::create('aksic_claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('aksic_claim_id')->constrained('aksic_claims')->cascadeOnDelete();
            $table->foreignUuid('aksic_id')->constrained('aksics')->cascadeOnDelete();

            // Snapshot for District / Region / Branch / Gender-wise reporting.
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('gender', 20)->nullable();

            $table->unsignedSmallInteger('installments_count');
            $table->decimal('principal_outstanding', 20, 2)->comment('Opening principal of the first instalment in the period');
            $table->decimal('markup_amount', 20, 2)->comment('Markup of instalments due within the claim period');
            $table->timestamps();

            $table->unique(['aksic_claim_id', 'aksic_id']);
            $table->index(['district_id', 'region_id', 'branch_id', 'gender'], 'aksic_claim_items_mis_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aksic_claim_items');
        Schema::dropIfExists('aksic_claims');
    }
};
