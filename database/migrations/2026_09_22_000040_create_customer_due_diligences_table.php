<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER DUE DILIGENCE (CDD)  -- "For Bank Use Only" blocks
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #31, #32, #33
| Source : AOF-Individual p.14 -- "Customer Due Diligence Individual Account"
|          AOF-Individual p.15 -- UBO block + "Customer Due Diligence Business
|          Account (Sole Proprietor)"
|          AOF-Entity p.17 -- "Customer Due Diligence Business Account" + UBO
|
| ===================== DONO FORMS KA MERGE =====================
| Teeno CDD boxes (Individual / Sole Proprietor / Business) ke 80% fields
| bilkul same hain:
|   Type of Customer (Walk In / Marketed / Referred By), Physical Verification
|   Conducted, Name Cleared From Proscribed List, Usual Mode of Credit & Debit
|   Transaction, Purpose of Account, UBO block, Expected Aggregate Credit/Debit
|   (Amount + No. of Transaction), Expected IFTT/OFTT Amount, Initial Deposit.
|
| Farq sirf income/occupation wale hisse ka hai, jo pivot tables
| (cdd_income_sources / cdd_wealth_sources) se handle hota hai.
| Is liye TEENO boxes ek hi table `customer_due_diligences` mein aate hain aur
| `cdd_type` batata hai kaunsa box bhara gaya.
| ===============================================================
|
| Yeh record per-ACCOUNT hai (form har account ke sath bharta hai), aur
| customer_id bhi rakha hai taake customer-level reporting aasan ho.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_due_diligences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('cdd_type', ['individual', 'sole_proprietor', 'business'])
                ->comment('Kaunsa printed CDD box bhara gaya');

            /* ---- Type of Customer (teeno boxes mein same) ---- */
            $table->enum('customer_source', ['walk_in', 'marketed', 'referred'])->nullable()
                ->comment('Type of Customer: Walk In / Marketed / Referred By');
            $table->string('referred_by', 150)->nullable()->comment('"Referred By ____"');
            $table->boolean('physical_verification_conducted')->default(false)
                ->comment('Physical Verification Conducted');
            $table->boolean('proscribed_list_cleared')->default(false)
                ->comment('Name Cleared From Proscribed List');

            /* ---- Source of Income: Salaried / Pensioner (AOF-Ind p.14) ---- */
            $table->string('employer_name', 200)->nullable()->comment('Employer Name');
            $table->string('employer_designation', 120)->nullable()->comment('Designation');
            $table->text('employer_address')->nullable()->comment('Address of Employer');

            /* ---- Source of Income: Student / House Wife / Unemployed ---- */
            $table->string('fund_provider_employer', 200)->nullable()->comment('Employer of Fund Provider');
            $table->string('fund_provider_id_number', 50)->nullable()->comment('ID Document No. of Fund Provider');
            $table->foreignUuid('fund_provider_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('Relationship with Fund Provider');

            /* ---- Source of Income: Self Employed (AOF-Ind p.14) ---- */
            $table->boolean('is_dnfbp')->default(false)->comment('"IS A DNFBP?"');
            $table->string('business_name', 200)->nullable()->comment('Business Name');
            $table->string('business_nature_text', 200)->nullable()->comment('Business Nature');
            $table->text('business_address')->nullable()->comment('Business Address');
            $table->string('type_of_channels', 200)->nullable()->comment('Type of Channels');
            $table->string('type_of_counterparties', 200)->nullable()->comment('Type of Counterparties');
            $table->string('geographies_involved', 200)->nullable()->comment('Geographies Involved');

            /* ---- Source of Income: Labor/Daily Wages / Agriculturist ---- */
            $table->string('nature_of_work', 200)->nullable()->comment('Nature of Work');

            /* ---- Home Remittance ---- */
            $table->foreignUuid('home_remittance_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Home Remittance* (Country ____)');
            $table->string('home_remittance_relationship', 120)->nullable()->comment('Relationship ____');

            /* ---- Income figures ---- */
            $table->decimal('monthly_income', 18, 2)->nullable()->comment('Monthly Income (Individual CDD)');
            $table->decimal('monthly_net_income', 18, 2)->nullable()->comment('Monthly Net Income (Business CDD)');
            $table->unsignedInteger('number_of_employees')->nullable()->comment('Number of employees (Business CDD)');
            $table->string('source_of_income_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('source_of_wealth_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('purpose_of_account_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('credit_mode_other', 120)->nullable()->comment('Usual Mode of Credit Transaction: Others');
            $table->string('debit_mode_other', 120)->nullable()->comment('Usual Mode of Debit Transaction: Others');
            $table->string('counter_party_other', 120)->nullable()->comment('Expected Type of Counter Parties: Others');

            /* ---- Expected turnover (dono forms ke UBO box ke sath) ---- */
            $table->decimal('initial_deposit', 18, 2)->nullable()->comment('Initial Deposit');
            $table->decimal('expected_monthly_credit_amount', 18, 2)->nullable()
                ->comment('Expected Aggregate Credit (per month): Amount');
            $table->unsignedInteger('expected_monthly_credit_count')->nullable()->comment('No. of Transaction');
            $table->decimal('expected_monthly_debit_amount', 18, 2)->nullable()
                ->comment('Expected Aggregate Debit (per month): Amount');
            $table->unsignedInteger('expected_monthly_debit_count')->nullable()->comment('No. of Transaction');
            $table->decimal('expected_iftt_amount', 18, 2)->nullable()
                ->comment('Expected IFTT Amount (per month) -- Intra-Fund Transfer Transaction');
            $table->decimal('expected_oftt_amount', 18, 2)->nullable()
                ->comment('Expected OFTT Amount (per month) -- Online/Outward Fund Transfer');
            $table->string('currency_symbol', 10)->nullable()
                ->comment('Form note: "In case of Foreign Currency Account, please mention currency symbol"');
            $table->text('joint_account_note')->nullable()
                ->comment('Form note: joint accounts mein expected credit/debit aggregate aur source of income collectively');

            $table->timestamps();

            $table->unique(['account_id', 'customer_id', 'cdd_type'], 'cdd_account_customer_unique');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_due_diligences');
    }
};
