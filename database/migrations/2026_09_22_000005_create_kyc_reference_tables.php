<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KYC / CDD / FATCA-CRS REFERENCE DATA
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #13 CRS Entity Classification, #31 CDD Individual,
|                        #32 UBO block, #33 CDD Business
| Source : AOF-Individual p.14 & p.15  |  AOF-Entity p.3, p.4 & p.17
|
| NOTE:
| - Individual CDD ke income sources (Salaried, Pensioner, Student, House Wife,
|   Unemployed, Self Employed, Labor/Daily Wages, Agriculturist) aur Business
|   CDD ke (Export Proceeds, Property/Real Estate, FDI, Local Trading,
|   Equity/FX Trading, Charity & Funds Donations) alag hain -- dono ko ek hi
|   `income_sources` table mein `applies_to` ke sath rakha hai, is liye ek hi
|   CDD table dono forms ko cover karti hai.
| - "Usual Mode of Credit Transaction" aur "Usual Mode of Debit Transaction"
|   ke options same hain (Cash/Clearing/Remittance/Collection/Others) --
|   duplicate rows ke bajaye pivot par `direction` column hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* #31 / #33 -- Source of Income/Occupation/Profession */
        Schema::create('income_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 120)->comment('Salaried, Pensioner, Student, House Wife, Unemployed, Self Employed, Labor/Daily Wages, Agriculturist, Stock/Investment, Rented Property, Home Remittance, Export Proceeds, Property/Real Estate, Foreign Direct Investment (FDI), Local Trading, Equity/FX Trading, Charity & Funds Donations, Others');
            $table->string('name_ur', 120)->nullable();
            $table->enum('applies_to', ['individual', 'entity', 'both'])->default('both')
                ->comment('individual = AOF-Ind p.14 | entity = AOF-Ind p.15 (Sole Prop) & AOF-Ent p.17');
            $table->boolean('requires_employer_details')->default(false)
                ->comment('Salaried/Pensioner => Employer Name, Designation, Address of Employer');
            $table->boolean('requires_fund_provider_details')->default(false)
                ->comment('Student/House Wife/Unemployed => Employer of Fund Provider, ID Doc No., Relationship');
            $table->boolean('requires_country')->default(false)
                ->comment('Home Remittance => "(Country ____ Relationship ____)"');
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['applies_to', 'is_active']);
        });

        /* #31 -- Source of Wealth (Individual AOF p.14) */
        Schema::create('wealth_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 120)->comment('Gift, Personal Savings, Rented Property/Property Sales, Inheritance, Others');
            $table->string('name_ur', 120)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #31 / #33 -- Usual Mode of Credit & Debit Transaction */
        Schema::create('transaction_modes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 80)->comment('Cash, Clearing, Remittance, Collection, Others');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #31 / #33 -- Purpose of Account */
        Schema::create('account_purposes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 80)->comment('Saving, Business, Transactional, Credit Facility, Others');
            $table->string('name_ur', 80)->nullable();
            $table->enum('applies_to', ['individual', 'entity', 'both'])->default('both')
                ->comment('Transactional sirf Individual AOF par, Credit Facility sirf Business CDD par');
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #33 -- Expected Type of Counter Parties */
        Schema::create('counter_party_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 80)->comment('Financial Institution, Limited Company, Proprietorship, Govt. Entity, NPO, Others');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #13 -- CRS Entity Classification, options (a) se (i) (Entity AOF p.3 & p.4) */
        Schema::create('crs_entity_classifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 40)->unique();
            $table->char('form_option', 1)->nullable()->comment('a, b, c, d, e, f, g, h, i -- printed form ka option letter');
            $table->string('name', 220);
            $table->string('name_ur', 220)->nullable();
            $table->boolean('requires_giin')->default(false)->comment('Options (b) & (c): "please provide the GIIN obtained"');
            $table->boolean('requires_controlling_persons')->default(false)
                ->comment('Options (a) & (i): 20% or more shareholding & voting rights wale log');
            $table->boolean('requires_stock_exchange')->default(false)
                ->comment('Option (d): "please provide name of stock exchange where company is listed"');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #13 -- Type of Controlling Person (Entity AOF p.3 & p.4) */
        Schema::create('controlling_person_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 40)->unique();
            $table->string('name', 120)->comment('Owner (direct or indirect), Controlling Person by other means, Senior Management Official, Beneficiary, Settlor, Trustee, Protector, Settlor-equivalent, Trustee-equivalent, Protector-equivalent, Beneficiary-equivalent, Other-equivalent');
            $table->string('name_ur', 120)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlling_person_types');
        Schema::dropIfExists('crs_entity_classifications');
        Schema::dropIfExists('counter_party_types');
        Schema::dropIfExists('account_purposes');
        Schema::dropIfExists('transaction_modes');
        Schema::dropIfExists('wealth_sources');
        Schema::dropIfExists('income_sources');
    }
};
