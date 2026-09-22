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
            $table->boolean('is_startup_business')->default(false);
            $table->string('quota')->nullable();
            $table->string('gender')->nullable();
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
            $table->boolean('site_visit_completed')->default(false);
            $table->date('site_visit_date')->nullable();
            $table->enum('consent_entry', ['Yes', 'No'])->nullable();
            $table->date('consent_date')->nullable();
            $table->text('liquid_security')->nullable();
            $table->text('personal_guarantees')->nullable();
            $table->decimal('kibor_rate', 5, 2)->nullable();
            $table->decimal('spread_rate', 5, 2)->nullable();
            $table->decimal('total_rate', 5, 2)->nullable();
            $table->decimal('total_interest', 20, 6)->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
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
