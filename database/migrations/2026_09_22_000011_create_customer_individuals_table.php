<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER INDIVIDUALS  ("Customer Details For Individuals")
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #06, #11 (Visually Impaired/Blind block), #09 (MNP)
| Source : AOF-Individual p.2 -- Name Mr/Mrs/Ms, S/o D/o W/o, Mother's Maiden
|          Name, Gender, Marital Status, Education, Date of Birth, Country of
|          Birth, City of Birth, Country of Residence, Profession, Designation,
|          N.T.N Number, Details for Minor Account Holders (Name of Guardian,
|          Relationship with Guardian/Minor, Employer's/Educational Institution
|          Name & Address)
|          AOF-Individual p.3 -- MNP question, "For Visually Impaired Persons/
|          Blind Customers Only" (Name of Witness, Signature, Relation with
|          Customer, CNIC)
|
| NOTE:
| - `customers` ke sath 1:1 (customer_id unique).
| - Entity AOF mein yeh block nahi hota, MAGAR entity ke authorized signatories
|   ka bhi apna CIF banta hai (AOF-Ent p.13: "CIF required of all authorized
|   signatories") -- us waqt bhi yahi table bharti hai. Is liye ek hi table
|   dono forms ke afraad ko cover karti hai.
| - Nationality yahan column nahi: form 3 nationalities allow karta hai, is
|   liye `customer_nationalities` alag table hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_individuals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();

            /* ---- Name block ---- */
            $table->string('title', 10)->nullable()->comment('Mr / Mrs / Ms');
            $table->string('full_name', 150)->comment('*Name');
            $table->string('full_name_ur', 150)->nullable();
            $table->foreignUuid('parentage_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('*S/o, D/o, W/o');
            $table->string('parent_or_spouse_name', 150)->nullable();
            $table->string('mother_maiden_name', 150)->nullable()->comment("*Mother's Maiden Name");

            /* ---- Demographics ---- */
            $table->foreignUuid('gender_id')->nullable()->constrained()->nullOnDelete()->comment('*Gender');
            $table->foreignUuid('marital_status_id')->nullable()->constrained()->nullOnDelete()->comment('*Marital Status');
            $table->string('marital_status_other', 80)->nullable()->comment('Others => "(Please Specify)"');
            $table->foreignUuid('education_level_id')->nullable()->constrained()->nullOnDelete()->comment('Education');
            $table->string('education_other', 80)->nullable();
            $table->date('date_of_birth')->comment('*Date of Birth');
            $table->foreignUuid('birth_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Country of Birth');
            $table->string('birth_city', 100)->nullable()->comment('City of Birth');
            $table->foreignUuid('residence_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('*Country of Residence');

            /* ---- Profession ---- */
            $table->foreignUuid('profession_id')->nullable()->constrained()->nullOnDelete()->comment('*Profession');
            $table->string('profession_other', 120)->nullable();
            $table->string('designation', 120)->nullable()->comment('Designation');
            $table->string('ntn', 30)->nullable()->comment('N.T.N Number');

            /* ---- Details for Minor Account Holders ---- */
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name', 150)->nullable()->comment('*Name of Guardian');
            $table->foreignUuid('guardian_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('Relationship with Guardian/Minor');
            $table->foreignUuid('guardian_customer_id')->nullable()->constrained('customers')->nullOnDelete()
                ->comment('Agar guardian ka apna CIF mojood hai to link');
            $table->string('employer_institution_name', 200)->nullable()
                ->comment("Employer's/Educational Institution Name");
            $table->text('employer_institution_address')->nullable()
                ->comment("Employer's/Educational Institution Address");

            /* ---- #09 Mobile Number Portability (MNP) ---- */
            $table->boolean('has_availed_mnp')->nullable()
                ->comment('"Have you availed Mobile Number Portability (MNP) services?" Yes/No');
            $table->string('mnp_new_provider', 100)->nullable()
                ->comment('"please mention name of your new Mobile Service provider"');

            /* ---- #11 For Visually Impaired Persons/Blind Customers Only ---- */
            $table->boolean('requires_witness')->default(false)
                ->comment('*Witness is mandatory for illiterate/visually impaired customers');
            $table->string('witness_name', 150)->nullable()->comment('Name of Witness (Accompanied by Customer)');
            $table->string('witness_relation', 100)->nullable()->comment('Relation with Customer');
            $table->string('witness_cnic', 25)->nullable()->comment('CNIC');
            $table->string('witness_signature_path', 255)->nullable()->comment('Signature');

            /* ---- Important Notes (c) & (e): photograph / thumb impression ---- */
            $table->string('photograph_path', 255)->nullable()
                ->comment('Photo Account / shaky-immature signature case: passport sized photograph');
            $table->boolean('thumb_impression_taken')->default(false)
                ->comment('Left & right thumb impressions on SS Card');

            $table->timestamps();

            $table->index('full_name');
            $table->index('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_individuals');
    }
};
