<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ACCOUNTS   << CORE TABLE >>
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #01 Particulars of Account, #02 Bank Use Only,
|                        #16 Type of Account (Rupee), #17 Type of Account
|                        (Foreign Currency), #18 Operating Instructions,
|                        #19 Title of Account, #20 Special/Standing
|                        Instructions, #21 SMS Alerts, #22 BAJK Digital
|                        Channels/App, #23 Mailing Instructions, #26 T&C
| Source : AOF-Individual p.1, p.4  |  AOF-Entity p.1, p.5
|
| NOTE:
| - `customers` (kaun hai) aur `accounts` (kya khula hai) alag hain. Joint
|   account ke 4 holders = 4 customers + 1 account, jinhein `account_holders`
|   jorta hai. T&C clause 3 ka rule isi se enforce hota hai.
| - Product ki qismein enum nahi -- `account_products` se FK. Naya product
|   launch ho to migration nahi, sirf ek row.
| - Mailing address `customer_addresses` (type = mailing) ko point karta hai
|   taake address duplicate na ho.
| - Balance / transaction columns yahan jaan boojh kar nahi hain: yeh design
|   Account Opening Form + KYC capture ke liye hai, core banking ledger ke
|   liye nahi.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            /* ---- #01 / #02 Particulars of Account + Bank Use Only ---- */
            $table->string('account_number', 24)->unique()->comment('Account Number');
            $table->string('iban', 34)->nullable()->unique()->comment('IBAN -- form par "P K" prefilled boxes');
            $table->string('title_of_account', 200)->comment('Title of Account');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete()
                ->comment('Branch Code / Branch Name -- mojooda branches table');
            $table->string('profit_center', 20)->nullable()->comment('Profit Center');
            $table->date('opening_date')->comment('Bank Use Only ka "Date" box');

            /* ---- #16 / #17 Type of Account ---- */
            $table->foreignUuid('account_product_id')->constrained()->restrictOnDelete();
            $table->string('product_other', 150)->nullable()->comment('"Other ____ (Please Specify)"');
            $table->foreignUuid('currency_id')->constrained()->restrictOnDelete();
            $table->string('currency_other', 60)->nullable()->comment('FCY "Other (Please Specify)"');
            $table->enum('account_class', ['current', 'saving'])->comment('FCY box par Current / Saving ka alag tick');
            $table->boolean('is_foreign_currency')->default(false);

            /* ---- #18 Operating Instructions ---- */
            $table->foreignUuid('operating_instruction_id')->constrained()->restrictOnDelete();
            $table->string('operating_instruction_other', 150)->nullable()->comment('Other => "(Please Specify)"');

            /* ---- #20 Special/Standing Instructions ---- */
            $table->text('special_instructions')->nullable();

            /* ---- #21 SMS Alerts ---- */
            $table->boolean('sms_alerts_subscribed')->default(false)
                ->comment('"Would you like to subscribe for paid SMS Services..." Yes/No');

            /* ---- #22 BAJK Digital Channels/App ---- */
            $table->boolean('digital_channels_opted')->default(false)
                ->comment('"Would you like to avail BAJK Digital Channels/App financial transaction facility?"');
            $table->boolean('digital_channels_biometric_verified')->default(false)
                ->comment('"on the basis of provided account biometric verification"');

            /* ---- #23 Account Statement/Others Mailing Instructions ---- */
            $table->foreignUuid('statement_delivery_mode_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('statement_frequency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('mailing_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();

            /* ---- Bank Use Only: Initial Deposit + Zakat ---- */
            $table->decimal('initial_deposit', 18, 2)->nullable()->comment('Bank Use Only: "Initial Deposit"');
            $table->boolean('zakat_applicable')->default(false)
                ->comment('"Zakat is applicable in PKR-Saving Account Only" + customer ka exemption status');

            /* ---- #26 Terms & Conditions / #28 Indemnity & Undertaking ---- */
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('aof_version', 30)->nullable()
                ->comment('Kaunsa form version sign hua: BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026');

            /* ---- Lifecycle ---- */
            $table->enum('status', ['pending', 'active', 'closed'])->default('pending');
            $table->userTracking();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['account_product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
