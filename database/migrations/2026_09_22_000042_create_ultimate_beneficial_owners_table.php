<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ULTIMATE BENEFICIAL OWNERS (UBO)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #32
| Source : AOF-Individual p.15 -- "Ultimate Beneficial Owner of the Account
|          (if different from Customer)", "Relationship with Customer",
|          "Identification document of Ultimate Beneficial Owner
|          CNIC/SNIC/NICOP/POC/Passport/Alient Registered Card Number"
|          AOF-Entity p.17 -- same block
|          AOF-Individual p.12 -- "Ultimate Beneficial Owner (UBO) Declaration
|          Form" (Sole Proprietorship documentation)
|
| NOTE: Dono forms mein yeh block bilkul same hai, is liye ek hi table.
| Account ke sath juda hai kyunki form ise "For Bank Use Only" CDD box mein
| rakhta hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ultimate_beneficial_owners', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_due_diligence_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150)->comment('Ultimate Beneficial Owner of the Account (if different from Customer)');
            $table->foreignUuid('relationship_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Relationship with Customer');
            $table->string('relationship_other', 120)->nullable();
            // MariaDB caps identifiers at 64 characters and the generated name
            // (ultimate_beneficial_owners_identification_document_type_id_foreign)
            // is 66, so the constraint is named explicitly.
            $table->foreignUuid('identification_document_type_id')->nullable()
                ->constrained(table: 'identification_document_types', indexName: 'ubo_identification_doc_type_foreign')
                ->nullOnDelete();
            $table->string('identification_number', 50)->nullable()
                ->comment('CNIC/SNIC/NICOP/POC/Passport/Alient Registered Card Number');
            $table->boolean('declaration_form_received')->default(false)
                ->comment('UBO Declaration Form (documentation checklist)');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ultimate_beneficial_owners');
    }
};
