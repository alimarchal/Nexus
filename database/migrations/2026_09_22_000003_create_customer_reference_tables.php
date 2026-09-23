<?php

declare(strict_types=1);

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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
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
};
