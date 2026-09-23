<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CDD PIVOT TABLES  (multi-select checkboxes of the CDD blocks)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #31, #33
| Source : AOF-Individual p.14 & p.15  |  AOF-Entity p.17
|
| Form ke jo boxes ek se zyada tick ho sakte hain, unhein pivot mein rakha hai:
|   Source of Income/Occupation/Profession .......... cdd_income_source
|   Source of Wealth ................................ cdd_wealth_source
|   Usual Mode of Credit / Debit Transaction ........ cdd_transaction_mode
|   Purpose of Account .............................. cdd_account_purpose
|   Expected Type of Counter Parties ................ cdd_counter_party_type
|
| NOTE: Credit aur Debit modes ke options same hain, is liye ek hi pivot mein
| `direction` column hai -- do alag tables ki zaroorat nahi.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdd_income_source', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('income_source_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'income_source_id'], 'cdd_income_source_unique');
        });

        Schema::create('cdd_wealth_source', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wealth_source_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'wealth_source_id'], 'cdd_wealth_source_unique');
        });

        Schema::create('cdd_transaction_mode', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('transaction_mode_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['credit', 'debit'])
                ->comment('Usual Mode of Credit Transaction / Usual Mode of Debit Transaction');
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'transaction_mode_id', 'direction'], 'cdd_txn_mode_unique');
        });

        Schema::create('cdd_account_purpose', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_purpose_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'account_purpose_id'], 'cdd_account_purpose_unique');
        });

        Schema::create('cdd_counter_party_type', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('counter_party_type_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'counter_party_type_id'], 'cdd_counter_party_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdd_counter_party_type');
        Schema::dropIfExists('cdd_account_purpose');
        Schema::dropIfExists('cdd_transaction_mode');
        Schema::dropIfExists('cdd_wealth_source');
        Schema::dropIfExists('cdd_income_source');
    }
};
