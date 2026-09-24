<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account opening (AOF) reference / lookup tables: common, customer, product, KYC and document references.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createCommonReferenceTables();
        $this->createCustomerReferenceTables();
        $this->createProductReferenceTables();
        $this->createKycReferenceTables();
        $this->createDocumentReferenceTables();
    }

    public function down(): void
    {
        $this->dropDocumentReferenceTables();
        $this->dropKycReferenceTables();
        $this->dropProductReferenceTables();
        $this->dropCustomerReferenceTables();
        $this->dropCommonReferenceTables();
    }

    // ------------------------------------------------------------------
    // CommonReferences (was 2026_09_22_000002_create_common_reference_tables.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | COMMON REFERENCE DATA : countries + currencies
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #06 (Country of Birth / Nationality / Country of
    |                        Residence / Country of Incorporation), #08 (Address
    |                        Country), #14 (Tax Residence), #17 (Foreign Currency)
    | Source : AOF-Individual p.2, p.3, p.4  |  AOF-Entity p.2, p.4, p.5
    |
    | NOTE:
    | - Country dono forms mein kai jagah aata hai (Country of Birth, Nationality
    |   1-3, Country of Residence, Country of Incorporation, Address Country,
    |   Tax Residence, Home Remittance Country). Ek hi master table sab jagah FK
    |   se use hoga -- data duplicate nahi hoga.
    | - `dial_code` is liye hai ke form likhta hai: "Country Code is mandatory
    |   with Mobile & Telephone Number".
    | - `currencies` form ke "Type of Account Foreign Currency" box se aata hai:
    |   $USD, GBP, AED, SAR, Euro, Other  + PKR (local).
    */
    private function createCommonReferenceTables(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->char('iso2', 2)->unique()->comment('ISO 3166-1 alpha-2, e.g. PK');
            $table->char('iso3', 3)->unique()->comment('ISO 3166-1 alpha-3, e.g. PAK');
            $table->string('name', 100);
            $table->string('name_ur', 100)->nullable();
            $table->string('dial_code', 8)->nullable()->comment('Form: country code mandatory with mobile/telephone');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->char('code', 3)->unique()->comment('PKR, USD, GBP, EUR, AED, SAR');
            $table->string('name', 60);
            $table->string('symbol', 10)->nullable();
            $table->boolean('is_local')->default(false)->comment('true sirf PKR ke liye (Rupee products)');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function dropCommonReferenceTables(): void
    {
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }

    // ------------------------------------------------------------------
    // CustomerReferences (was 2026_09_22_000003_create_customer_reference_tables.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER REFERENCE DATA : CIF ke checkbox / dropdown lists
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #04 Customer Category, #05 Special Category,
    |                        #06 Customer Details (Individual / Business),
    |                        #07 Identification
    | Source : AOF-Individual p.2  |  AOF-Entity p.2
    |
    | ======================= SAB SE AHAM DESIGN NOTE =========================
    | Dono PDFs ke "Customer Category" boxes alag alag hain:
    |   * Individual AOF : Individual, Sole Proprietorship, Joint, Others
    |   * Entity AOF     : Partnership, Limited Companies, Public Listed,
    |                      Public Unlisted, Private, Foreign Missions/Diplomats,
    |                      Government Institution, Societies, Club, Trust,
    |                      Associations, Local Zakat Committee, Business-Money
    |                      Exchange Company, Branch/Liaison Office of Foreign
    |                      Companies, NGO/NPO's Charities, Agents Accounts,
    |                      Executors & Administrators, Others
    |
    | Do alag design banane ke bajaye EK `customer_categories` table hai jis mein
    | `applies_to` batata hai ke category kis form se aayi hai. Is tarah ek hi CIF
    | screen dono forms ko serve karti hai aur nayi category sirf ek row hai.
    |
    | `requires_individual_profile` / `requires_organization_profile` decide karte
    | hain ke kaunsa detail table bharna hai. Sole Proprietorship ke liye DONO
    | true hain -- yahi wo case hai jo dono PDFs ko jorta hai.
    | =========================================================================
    */
    private function createCustomerReferenceTables(): void
    {
        /* #04 -- Customer Category (dono forms ka combined master) */
        Schema::create('customer_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique()->comment('Form box: "Category Code & Desc."');
            $table->string('name', 120);
            $table->string('name_ur', 120)->nullable();
            $table->enum('applies_to', ['individual', 'entity', 'both'])
                ->comment('individual = 15-page AOF, entity = 17-page AOF');
            $table->boolean('requires_individual_profile')->default(false)
                ->comment('true => customer_individuals row lazmi ("Customer Details For Individuals")');
            $table->boolean('requires_organization_profile')->default(false)
                ->comment('true => customer_organizations row lazmi ("Customer Details For Business")');
            $table->boolean('is_other')->default(false)->comment('Others => "(Please Specify)" free text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['applies_to', 'is_active']);
        });

        /* #05 -- Special Category of Account (Individual AOF p.2) */
        Schema::create('special_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 120)->comment('BAJK Staff, Minor, Photo Account, Mustahiqeen-e-Zakat, Parda Nasheen, Student, Visually Impaired Person/Blind, Disaster Affectees/IDPs, Registered Alien, Widow, Pensioners, Senior Citizen, Govt Emp/Semi Govt Emp, Pension, Salary, Physically Handicapped, Non-Resident, Others');
            $table->string('name_ur', 120)->nullable();
            $table->boolean('requires_extra_value')->default(false)
                ->comment('Non-Resident => "(in years) Time since residing out side Pakistan"');
            $table->string('extra_value_label', 80)->nullable();
            $table->boolean('is_other')->default(false)->comment('Others => "(Please Specify)"');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #04 -- Economic Sector Code (Entity AOF: "Mandatory if Customer category is Business") */
        Schema::create('economic_sectors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 150);
            $table->string('name_ur', 150)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #06 -- Nature of Business (Entity AOF p.2) */
        Schema::create('business_natures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 120)->comment('Import/Export, Agriculture, Manufacturing, Exchange Company, NBFI, Scheduled Bank, Retail Business, Others');
            $table->string('name_ur', 120)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #06 -- Profession (Individual AOF p.2) */
        Schema::create('professions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 120)->comment('Government Service, Private Service, Housewife, Self Employed, Unemployed, Agriculture, Student, Others');
            $table->string('name_ur', 120)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #06 -- Education (Individual AOF p.2) */
        Schema::create('education_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 80)->comment('No Education, Below Matric, Matric/O level, Intermediate/A level, Graduate, Postgraduate, Others');
            $table->string('name_ur', 80)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #06 -- Marital Status (Individual AOF p.2) */
        Schema::create('marital_statuses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 40)->comment('Married, Single, Others');
            $table->string('name_ur', 40)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #06 -- Gender (Individual AOF p.2: Male / Female / Trans Gender) */
        Schema::create('genders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 40);
            $table->string('name_ur', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        | Relationships -- ek hi master form ki kai jagah use hota hai:
        |   #06 S/o, D/o, W/o  |  #06 Relationship with Guardian/Minor
        |   #29 Next of kin ka "Relationship"
        |   #31 "Relationship with Fund Provider" / Home Remittance Relationship
        |   #32 "Relationship with Customer" (Ultimate Beneficial Owner)
        | context_* flags batate hain ke option kis dropdown mein dikhe.
        */
        Schema::create('relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 60);
            $table->string('name_ur', 60)->nullable();
            $table->boolean('context_parentage')->default(false)->comment('S/o, D/o, W/o');
            $table->boolean('context_guardian')->default(false)->comment('Relationship with Guardian/Minor');
            $table->boolean('context_next_of_kin')->default(false)->comment('Contact Persons block');
            $table->boolean('context_fund_provider')->default(false)->comment('Relationship with Fund Provider');
            $table->boolean('context_ubo')->default(false)->comment('Relationship with Customer (UBO)');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* #07 -- Identification document types (Individual AOF p.2 + documentation pages) */
        Schema::create('identification_document_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique()->comment('CNIC, SNIC, NICOP, POC, ARC, POR, PASSPORT, CRC_B_FORM, BIRTH_CERT, STUDENT_ID');
            $table->string('name', 150)->comment('Form: "CNIC / Birth Certificate(Government Authority) CRC/B-Form/Alien Registration Card No./POC/NICOP/POR" aur "Passport No. (Foreign Individual Only)"');
            $table->string('name_ur', 150)->nullable();
            $table->string('issuing_authority', 120)->nullable()->comment('NADRA / NARA-Ministry of Interior / Foreign Authority');
            $table->boolean('is_for_minor')->default(false)->comment('Form-B / CRC / Birth Certificate / Student ID card');
            $table->boolean('is_for_foreigner')->default(false)->comment('Passport "Foreign Individual Only", ARC, POR, NICOP');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function dropCustomerReferenceTables(): void
    {
        Schema::dropIfExists('identification_document_types');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('genders');
        Schema::dropIfExists('marital_statuses');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('professions');
        Schema::dropIfExists('business_natures');
        Schema::dropIfExists('economic_sectors');
        Schema::dropIfExists('special_categories');
        Schema::dropIfExists('customer_categories');
    }

    // ------------------------------------------------------------------
    // ProductReferences (was 2026_09_22_000004_create_product_reference_tables.php)
    // ------------------------------------------------------------------
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
    private function createProductReferenceTables(): void
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

    private function dropProductReferenceTables(): void
    {
        Schema::dropIfExists('statement_frequencies');
        Schema::dropIfExists('statement_delivery_modes');
        Schema::dropIfExists('card_types');
        Schema::dropIfExists('zakat_exemption_reasons');
        Schema::dropIfExists('operating_instructions');
        Schema::dropIfExists('account_products');
    }

    // ------------------------------------------------------------------
    // KycReferences (was 2026_09_22_000005_create_kyc_reference_tables.php)
    // ------------------------------------------------------------------
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
    private function createKycReferenceTables(): void
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

    private function dropKycReferenceTables(): void
    {
        Schema::dropIfExists('controlling_person_types');
        Schema::dropIfExists('crs_entity_classifications');
        Schema::dropIfExists('counter_party_types');
        Schema::dropIfExists('account_purposes');
        Schema::dropIfExists('transaction_modes');
        Schema::dropIfExists('wealth_sources');
        Schema::dropIfExists('income_sources');
    }

    // ------------------------------------------------------------------
    // DocumentReferences (was 2026_09_22_000006_create_document_reference_tables.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | DOCUMENT CHECKLIST REFERENCE ("Nature of Account -> Minimum Documentation")
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #27
    | Source : AOF-Individual p.12 (Individuals, Sole Proprietorship, Minor
    |          Accounts, Politically Exposed Person + Important Notes a..i)
    |          AOF-Entity p.12, p.13, p.14 (Partnership, Limited Companies, Clubs/
    |          Societies/Associations/Trusts, Government/Provincial/Local Govt &
    |          Armed Forces, NGOs/NPOs/Charities, Agent's Accounts, Executors and
    |          Administrators, PEP, Foreign Mission/Diplomats + Important Notes)
    |
    | NOTE:
    | - Dono PDFs ki documentation tables ko hard-code karne ke bajaye normalize
    |   kiya hai:
    |     document_types        = har document ka master (CNIC copy, Board
    |                             Resolution, Memorandum & Articles, NTN, etc.)
    |     document_requirements = kaunsi customer_category ke liye kaunsa document
    |                             mandatory hai
    |   Checklist DB se generate hogi. SBP circular badle to sirf rows update.
    | - `applies_when` conditional bullets ke liye hai, jaise
    |   "Declaration for source of funds in case of Housewives".
    */
    private function createDocumentReferenceTables(): void
    {
        Schema::create('document_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 40)->unique();
            $table->string('name', 220);
            $table->string('name_ur', 220)->nullable();
            $table->boolean('requires_attestation')->default(false)
                ->comment('Important Note (b): Gazetted officer/Nazim/Administrator/Bank officer se attested "after original seen"');
            $table->text('description')->nullable()->comment('Form ka bullet text reference ke liye');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_requirements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_type_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_category_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->string('applies_when', 255)->nullable()
                ->comment('Conditional bullet, e.g. "profession = HOUSEWIFE", "special_category = MINOR", "is_pep = true"');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_type_id', 'customer_category_id'], 'doc_req_type_category_unique');
            $table->index(['customer_category_id', 'is_mandatory']);
        });
    }

    private function dropDocumentReferenceTables(): void
    {
        Schema::dropIfExists('document_requirements');
        Schema::dropIfExists('document_types');
    }
};
