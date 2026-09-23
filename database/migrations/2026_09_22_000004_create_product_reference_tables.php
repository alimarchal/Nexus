<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PRODUCT & SERVICE REFERENCE DATA
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #16 Type of Account (Rupee), #17 Type of Account
|                        (Foreign Currency), #18 Operating Instructions,
|                        #10 Zakat Exemption, #23 Statement Mailing
|                        Instructions, #24 BAJK Debit Card
| Source : AOF-Individual p.3, p.4, p.5  |  AOF-Entity p.2, p.5
|
| NOTE:
| - Dono forms ke product boxes 95% same hain. Farq:
|     * PPRSA aur Debit Card sirf Individual AOF par hain
|     * Hold Mail Facility sirf Entity AOF par hai
|     * Cheque leaves Individual = 10/25/50, Entity = 25/50/100
|     * E-Statement frequency Individual = Monthly/Quarterly/Six Monthly/Yearly,
|       Entity = Monthly/Quarterly/Semi-Annually
|   In sab ko `available_for` aur `allowed_cheque_leaves` se handle kiya hai --
|   code mein if/else nahi, sirf data.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* #16 / #17 -- Current Products + Saving Products + FCY products */
        Schema::create('account_products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 150)->comment('BAJK Current Account, PLS Saving Account, Special Deposit Account (SDA), Bemisal Mahana Bachat Account (BMBA), Premium Plus Remittance Saving Account (PPRSA), BAJK Current Account-FCY, BAJK Saving Account-FCY');
            $table->string('name_ur', 150)->nullable();
            $table->enum('product_class', ['current', 'saving'])->comment('Form headings: "Current Products" / "Saving Products"');
            $table->enum('currency_type', ['local', 'foreign'])->default('local')
                ->comment('Rupee box vs Foreign Currency box');
            $table->enum('available_for', ['individual', 'entity', 'both'])->default('both')
                ->comment('PPRSA sirf Individual AOF par chhapa hai');
            $table->boolean('is_zakat_applicable')->default(false)
                ->comment('Form: "Zakat is applicable in PKR-Saving Account Only"');
            $table->boolean('allows_debit_card')->default(false)
                ->comment('Form: debit card "for Individual & sole proprietor accounts only"');
            $table->json('allowed_cheque_leaves')->nullable()->comment('[10,25,50] Individual | [25,50,100] Entity');
            $table->boolean('is_other')->default(false)->comment('"Other ____ (Please Specify)" box');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_class', 'currency_type', 'is_active']);
        });

        /* #18 -- Operating Instructions (dono forms par bilkul same) */
        Schema::create('operating_instructions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 80)->comment('Signing Authority, Singly, Either or Survivor, Jointly, Mandate, Other');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('requires_multiple_holders')->default(false)->comment('Either or Survivor / Jointly');
            $table->boolean('requires_mandate_form')->default(false)
                ->comment('Mandate => "Please fill the prescribed form"');
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #10 -- Zakat Exemption Code (Individual AOF p.3) */
        Schema::create('zakat_exemption_reasons', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 80)->comment('Non-Muslim, Foreigner, Due to Fiqah, Others');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #24 -- BAJK Debit Card (Individual AOF p.5) */
        Schema::create('card_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 120)->comment('BAJK Debit Card (Enhanced ATM & POS limits)');
            $table->string('name_ur', 120)->nullable();
            $table->unsignedTinyInteger('name_on_card_max_length')->default(19)
                ->comment('Form: "Maximum length is 19 characters with spaces"');
            $table->enum('available_for', ['individual', 'entity', 'both'])->default('individual')
                ->comment('Form: "for Individual & sole proprietor accounts only"');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #23 -- Please Tick: BAJK E-Statement / Mail by Post-Courier / Hold Mail Facility */
        Schema::create('statement_delivery_modes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 80)->comment('BAJK E-Statement, Mail by Post/Courier, Hold Mail Facility');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('requires_email')->default(false)
                ->comment('Form footnote: email "Mandatory Field for Internet Banking & BAJK E-Statement"');
            $table->enum('available_for', ['individual', 'entity', 'both'])->default('both')
                ->comment('Hold Mail Facility sirf Entity AOF p.5 par hai');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #23 -- Frequency for BAJK E-Statement */
        Schema::create('statement_frequencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 60)->comment('Monthly, Quarterly, Six Monthly/Semi-Annually, Yearly');
            $table->string('name_ur', 60)->nullable();
            $table->unsignedSmallInteger('months_interval')->comment('1, 3, 6, 12');
            $table->enum('available_for', ['individual', 'entity', 'both'])->default('both')
                ->comment('Yearly sirf Individual AOF par, Semi-Annually sirf Entity AOF par');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statement_frequencies');
        Schema::dropIfExists('statement_delivery_modes');
        Schema::dropIfExists('card_types');
        Schema::dropIfExists('zakat_exemption_reasons');
        Schema::dropIfExists('operating_instructions');
        Schema::dropIfExists('account_products');
    }
};
