<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ACCOUNT OPENING REQUESTS  (bhara hua AOF khud, + recommendation block)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #26 T&C, #28 Indemnity & Undertaking, #34 Recommendation
| Source : AOF-Individual p.13 -- "INDEMNITY & UNDERTAKING" + "I/We hereby
|          agree with the Terms & Conditions ... Shariah Compliant manner"
|          AOF-Individual p.15 / AOF-Entity p.17 -- "I/We recommend to open the
|          Account subject to fulfilling all account opening requirements. The
|          bonafide have been confirmed.": Name of Sale Staff/relationship Mgr
|          (1)(2), Employee No., Signature of Sales Staff/Relationship Mgr,
|          *Authorized by (Name), Employee Number/PP.No, Signature,
|          *Branch Supervisor/Data Entry Checker (Individual form) /
|          *Branch Manager/Data Entry Checker (Entity form)
|
| NOTE: Yeh table ek bhare hue form ka "application" record hai. Ek request se
| ek account banta hai; maker-checker ka poora trail yahin hai.
| Individual form "Branch Supervisor" likhta hai aur Entity form "Branch
| Manager" -- dono ke liye ek hi column `checker_name` aur `checker_title`.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_opening_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('request_number', 30)->unique();
            $table->enum('aof_form_type', ['individual_joint_sole', 'entity'])
                ->comment('15-page Individual/Joint/Sole Proprietor form ya 17-page Govt/Partnership/Company/NGO form');
            $table->string('aof_version', 30)->nullable()
                ->comment('BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete()->comment('Primary applicant ka CIF');
            $table->foreignUuid('account_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Approve hone par bana hua account');
            $table->date('request_date');

            /* ---- #26 / #28 Customer declarations ---- */
            $table->boolean('terms_and_conditions_accepted')->default(false);
            $table->boolean('indemnity_undertaking_accepted')->default(false);
            $table->boolean('aof_copy_received_by_customer')->default(false)
                ->comment('"I/We confirm having received the copy of the Account Opening Form along with Terms & Conditions"');
            $table->boolean('shariah_compliant_investment_authorized')->default(false)
                ->comment('Individual form: "authorize the Bank to invest the deposit in only Shariah Compliant manner"');
            $table->boolean('vernacular_form_attached')->default(false)
                ->comment('Important Note (g): customer English/Urdu ke ilawa sign kare to 100 rupee bond paper par vernacular form');
            $table->boolean('qa22_form_attached')->default(false)
                ->comment('Important Note (f): QA-22 form for foreigner residing in Pakistan');

            /* ---- #34 Recommendation (For Bank Use Only) ---- */
            $table->string('sales_staff_name_1', 150)->nullable()->comment('Name of Sale Staff/relationship Mgr (1)');
            $table->string('sales_staff_employee_no_1', 30)->nullable()->comment('Employee No. (1)');
            $table->string('sales_staff_name_2', 150)->nullable()->comment('Name of Sale Staff/relationship Mgr (2)');
            $table->string('sales_staff_employee_no_2', 30)->nullable()->comment('Employee No. (2)');
            $table->string('authorized_by_name', 150)->nullable()->comment('*Authorized by (Name)');
            $table->string('authorized_by_employee_no', 30)->nullable()->comment('Employee Number/PP.No');
            $table->date('authorized_on')->nullable();
            $table->string('checker_name', 150)->nullable()
                ->comment('*Branch Supervisor/Data Entry Checker (Ind) ya *Branch Manager/Data Entry Checker (Ent)');
            $table->string('checker_employee_no', 30)->nullable()->comment('Employee Number/PP.No');
            $table->date('checked_on')->nullable();

            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();

            $table->userTracking();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index('request_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_opening_requests');
    }
};
