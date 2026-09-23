<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMERS  (CIF -- Customer Information File)   << CORE TABLE >>
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #03 CIF header, #04 Customer Category,
|                        #05 Special Category, #10 Zakat Exemption, #15 PEP
| Source : AOF-Individual p.2 & p.3  |  AOF-Entity p.2 & p.4
|
| ===================== DONO PDFs KO JORNE WALA TABLE =====================
| Dono forms ka CIF header bilkul same hai: Branch Code + Client/Relationship
| ID + Date. Is liye:
|
|   customers                        -> har customer/entity ka ek CIF
|     |__ customer_individuals       -> "Customer Details For Individuals"
|     |__ customer_organizations     -> "Customer Details For Business"
|
| `customer_type` batata hai kaunsa child record banega:
|   individual      -> sirf customer_individuals
|   sole_proprietor -> DONO (banda bhi, business bhi)   << bridging case
|   entity          -> sirf customer_organizations
|
| Joint account ke liye alag "joint customer" nahi banta. Har joint holder ka
| apna CIF hota hai aur `account_holders` unhein ek account se jorta hai --
| isi liye form p.1 par 4 Client/Relationship ID boxes hain.
| =========================================================================
|
| branch_id : aapke pehle se mojood `branches` table ko point karta hai
|             (AOF header ka "Branch Code" box). Yahan koi nayi branches
|             table nahi banayi gayi.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            /* ---- #03 CIF header ---- */
            $table->string('cif_number', 20)->unique()->comment('Client/Relationship ID -- AOF p.1 aur CIF header');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete()
                ->comment('Branch Code -- mojooda branches table');
            $table->date('cif_date')->comment('CIF header ka "Date" box');

            /* ---- #04 Customer Category ---- */
            $table->enum('customer_type', ['individual', 'sole_proprietor', 'entity'])
                ->comment('customer_categories.requires_* se derive hota hai');
            $table->enum('aof_form_type', ['individual_joint_sole', 'entity'])
                ->comment('Kaunsa printed AOF bhara gaya: 15-page Individual/Joint/Sole Proprietor ya 17-page Govt/Company');
            $table->foreignUuid('customer_category_id')->constrained()->restrictOnDelete();
            $table->string('customer_category_other', 150)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('category_code_description', 200)->nullable()->comment('Form box: "Category Code & Desc."');
            $table->foreignUuid('economic_sector_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Economic Sector Code');

            /* ---- #10 Zakat Exemption ---- */
            $table->boolean('is_zakat_exempt')->default(false)->comment('Zakat Exemption: Yes / No');
            $table->foreignUuid('zakat_exemption_reason_id')->nullable()->constrained()->nullOnDelete();
            $table->string('zakat_exemption_other', 150)->nullable();
            $table->date('cz50_submitted_on')->nullable()
                ->comment('Form: "Subject to submission of affidavit/declaration form CZ-50 on time"');

            /* ---- #15 PEP (For Bank Use Only) ---- */
            $table->boolean('is_pep')->default(false)
                ->comment('Politically Exposed Person / Political Connections: Yes / No');
            $table->boolean('pep_form_attached')->default(false)
                ->comment('"if Yes, please complete and attached the PEP declaration form"');

            /* ---- Record lifecycle (For Bank Use Only ke maker/checker boxes) ---- */
            $table->enum('status', ['draft', 'pending_verification', 'active', 'closed', 'rejected'])->default('draft');
            // Adds created_by / updated_by columns (app-wide UserTracking convention).
            $table->userTracking();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('"Branch Supervisor/Data Entry Checker"');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('"*Authorized by (Name)"');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes()->comment('CIF hard-delete nahi hota -- record retention requirement');

            $table->index(['customer_type', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('is_pep');
        });

        /* #05 -- Special Category of Account (ek customer ke 1 se zyada ho sakte hain) */
        Schema::create('customer_special_category', function (Blueprint $table): void {
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('special_category_id')->constrained()->cascadeOnDelete();
            $table->string('extra_value', 100)->nullable()
                ->comment('Non-Resident => "(in years) Time since residing out side Pakistan"');
            $table->string('other_description', 150)->nullable()->comment('Others => "(Please Specify)"');
            $table->timestamps();

            $table->unique(['customer_id', 'special_category_id'], 'cust_special_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_special_category');
        Schema::dropIfExists('customers');
    }
};
