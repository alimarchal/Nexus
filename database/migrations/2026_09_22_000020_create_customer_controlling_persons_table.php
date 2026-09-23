<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER CONTROLLING PERSONS (FATCA 10% shareholders / CRS 20% controllers)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #12 & #13
| Source : AOF-Entity p.3 -- "Declare the individual(s) holding 10% or more
|          shares/voting rights (Use separate sheet in case of more
|          applicants)": Applicant 1..10, Designation, Share holding/Voting
|          rights %
|          AOF-Entity p.3 & p.4 -- CRS: "Identity the Type of Controlling
|          Person (Having 20% or more shareholding & Voting rights)"
|
| NOTE:
| - Form 10 fixed rows deta hai aur "separate sheet" kehta hai -> DB mein
|   unlimited rows.
| - `declaration_basis` batata hai row FATCA (10%) ke liye hai ya CRS (20%),
|   kyunki thresholds alag hain magar log wahi ho sakte hain.
| - AOF-Ent p.13: "CIF required of all members and authorized signatories" --
|   is liye `person_customer_id` se CIF link ho jata hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_controlling_persons', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete()->comment('Jis entity ka controller hai');
            $table->foreignUuid('person_customer_id')->nullable()->constrained('customers')->nullOnDelete()
                ->comment('Agar is shakhs ka apna CIF bhi hai');
            $table->enum('declaration_basis', ['fatca_10_percent', 'crs_20_percent', 'both'])
                ->default('fatca_10_percent');
            $table->unsignedTinyInteger('sequence')->default(1)->comment('Applicant 1..10 (form ka number)');
            $table->string('person_name', 150)->comment('Applicant ka naam');
            $table->string('designation', 120)->nullable()->comment('Designation');
            $table->decimal('shareholding_percentage', 5, 2)->nullable()->comment('Share holding / Voting rights %');
            $table->foreignUuid('controlling_person_type_id')->nullable()
                ->constrained('controlling_person_types')->nullOnDelete()
                ->comment('Owner (direct or indirect) / Settlor / Trustee / Protector / Beneficiary etc.');
            $table->timestamps();

            $table->index(['customer_id', 'declaration_basis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_controlling_persons');
    }
};
