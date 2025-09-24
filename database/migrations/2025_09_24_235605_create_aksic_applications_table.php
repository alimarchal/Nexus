<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aksic_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('applicant_id')->unique();
            $table->string('name');
            $table->string('fatherName');
            $table->string('cnic')->unique();
            $table->string('application_no')->unique();
            $table->date('cnic_issue_date')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('businessName')->nullable();
            $table->string('businessType')->nullable();
            $table->string('quota')->nullable();
            $table->text('businessAddress')->nullable();
            $table->text('permanentAddress')->nullable();
            $table->unsignedBigInteger('business_category_id')->nullable();
            $table->unsignedBigInteger('business_sub_category_id')->nullable();
            $table->integer('tier')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('tehsil_id')->nullable();
            $table->unsignedBigInteger('applicant_choosed_branch_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('challan_branch_id')->nullable();
            $table->decimal('challan_fee', 10, 2)->nullable();
            $table->string('challan_image')->nullable();
            $table->string('cnic_front')->nullable();
            $table->string('cnic_back')->nullable();
            $table->enum('fee_status', ['paid', 'unpaid'])->default('unpaid');
            $table->enum('status', ['NotCompleted', 'Pending', 'Forwarded', 'Approved', 'Rejected'])->default('Pending');
            $table->string('bank_status')->nullable();
            $table->string('fee_branch_code')->nullable();
            $table->string('district_name')->nullable();
            $table->string('tehsil_name')->nullable();
            $table->json('api_call_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksic_applications');
    }
};
