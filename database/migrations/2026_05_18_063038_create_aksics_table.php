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
        Schema::create('aksics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('father_name');
            $table->string('cnic')->unique();
            $table->string('application_no')->unique();
            $table->date('cnic_issue_date')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('quota')->nullable();
            $table->text('business_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->unsignedBigInteger('business_category_id')->nullable();
            $table->unsignedBigInteger('business_sub_category_id')->nullable();
            $table->integer('tier')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('tehsil_id')->nullable();
            $table->string('applicant_choosed_branch_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('challan_branch_id')->nullable();
            $table->string('applicant_choosed_branch_code')->nullable();
            $table->string('challan_branch_code')->nullable();
            $table->decimal('challan_fee', 10, 2)->nullable();
            $table->string('challan_image')->nullable();
            $table->string('cnic_front')->nullable();
            $table->string('cnic_back')->nullable();
            $table->string('challan_image_url')->nullable();
            $table->string('cnic_front_url')->nullable();
            $table->string('cnic_back_url')->nullable();
            $table->enum('status', ['Pending', 'Approved'])->default('Pending');
            $table->string('bank_status')->nullable();
            $table->string('fee_branch_code')->nullable();
            $table->string('district_name')->nullable();
            $table->string('tehsil_name')->nullable();
            $table->decimal('principal_amount', 15, 2)->nullable();
            $table->unsignedInteger('tenure')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('sanction_date')->nullable();
            $table->decimal('kibor_rate', 5, 2)->nullable();
            $table->decimal('spread_rate', 5, 2)->nullable();
            $table->decimal('total_rate', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksics');
    }
};
