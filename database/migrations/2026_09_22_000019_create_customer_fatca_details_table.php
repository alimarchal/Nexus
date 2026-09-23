<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER FATCA / CRS DETAILS
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #12 FATCA, #13 CRS Entity Classification
| Source : AOF-Individual p.3 -- "*FATCA for Individual/Controlling Person/
|          Sole Proprietor/Joint Account Holder/Minor & Mandate Holder"
|          (Questions 1-4, Form W9 / W-8 BEN)
|          AOF-Entity p.3 -- "*FATCA For Non-Financial Entities (Companies)"
|          (a) Country of Incorporation is US?  (b) Active or Passive NFE?
|          AOF-Entity p.3 & p.4 -- "*CRS for Non-Individual Accounts" options
|          (a) se (i), GIIN, stock exchange name
|
| ===================== DONO FORMS KA MERGE =====================
| Individual FATCA 4 yes/no sawal poochta hai; Entity FATCA incorporation
| country aur Active/Passive NFE. Dono ek hi table mein hain -- individual
| columns entity ke liye NULL rahenge aur ulta bhi. Fayda: FATCA/CRS reporting
| ek hi query se, chahe customer koi bhi type ho.
| ===============================================================
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_fatca_details', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();

            /* ---------- INDIVIDUAL FATCA (AOF-Ind p.3) ---------- */
            $table->boolean('q1_is_us_person')->nullable()
                ->comment('Q1: "Are you a US National/Citizen/Green Card Holder or U.S. Resident"');
            $table->boolean('q2_country_of_birth_us')->nullable()->comment('Q2: "Is your country of birth US?"');
            $table->boolean('q3_has_us_address_or_phone')->nullable()
                ->comment('Q3: "Do you have a U.S. Address or Telephone No?"');
            $table->boolean('q4_has_us_mandate_or_links')->nullable()
                ->comment('Q4: mandate to a person having an address in the US / any US links');
            $table->boolean('form_w9_signed')->default(false)->comment('"If Yes please sign Form W9"');
            $table->boolean('form_w8_ben_signed')->default(false)->comment('Non-US Person claim => W8 BEN');
            $table->boolean('us_nationality_revoked')->default(false)
                ->comment('"If applicant claims revocation of U.S. Nationality"');
            $table->string('loss_of_nationality_cert_path', 255)->nullable()
                ->comment('Certificate of loss of residency / written explanation of revocation');

            /* ---------- ENTITY FATCA (AOF-Ent p.3) ---------- */
            $table->boolean('entity_incorporated_in_us')->nullable()
                ->comment('(a) Country of Incorporation and/or Parent Company Country of Incorporation is the U.S.?');
            $table->foreignUuid('incorporation_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Form box: "Country of Incorporation (CoI)"');
            $table->enum('nfe_type', ['active', 'passive'])->nullable()
                ->comment('(b) Active Non-Financial Entity ya Passive Non-Financial Entity');
            $table->boolean('form_w8_ben_e_signed')->default(false)->comment('Passive NFE => W8-BEN-E');
            $table->string('ssn_ein_document_path', 255)->nullable()
                ->comment('"copy of Social Security Number Card (SSN)/Employer Identification Number (EIN)"');

            /* ---------- ENTITY CRS CLASSIFICATION (AOF-Ent p.3 & p.4) ---------- */
            $table->foreignUuid('crs_entity_classification_id')->nullable()
                ->constrained('crs_entity_classifications')->nullOnDelete()
                ->comment('"Please Classify your Entity Type (Only select One option from the list below)"');
            $table->string('giin', 30)->nullable()->comment('Global Intermediary Identification Number');
            $table->string('listed_stock_exchange', 150)->nullable()
                ->comment('Option (d): name of stock exchange where company is listed');

            /* ---------- Common ---------- */
            $table->boolean('is_tax_resident_other_country')->nullable()
                ->comment('"Are you a Tax Resident of country other than Pakistan & USA?"');
            $table->date('self_certification_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_fatca_details');
    }
};
