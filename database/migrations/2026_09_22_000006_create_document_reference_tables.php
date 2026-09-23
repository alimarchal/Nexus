<?php

declare(strict_types=1);

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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
        Schema::dropIfExists('document_types');
    }
};
