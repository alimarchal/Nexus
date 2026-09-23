<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER TAX RESIDENCIES  (CRS self-certification grid)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #14
| Source : AOF-Individual p.3 -- "*Individual Tax Residency Status"
|          AOF-Entity p.4 -- "*Entity Tax Residency Status"
|
| DONO forms mein grid bilkul same hai:
|   Country/Jurisdiction of Tax Residence | TIN/NTN | Reason A, B or C |
|   Explanation (Reason B)
| Is liye EK hi table dono ke liye. Form 3 rows deta hai aur likhta hai "If the
| Account Holder is a tax resident in more than three countries please use a
| separate sheet" -- DB mein koi limit nahi.
|
| Reason codes (form ke exact alfaaz):
|   A = "The country where you are resident does not issue TINs /NTN to its residents"
|   B = "You are otherwise unable to obtain an NTN/TIN or equivalent number"  (explanation lazmi)
|   C = "No TIN/NTN is required"
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tax_residencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('country_id')->constrained()->restrictOnDelete()
                ->comment('Country/Jurisdiction of Tax Residence');
            $table->string('tin', 50)->nullable()->comment('TIN/NTN');
            $table->enum('no_tin_reason', ['A', 'B', 'C'])->nullable()->comment('If no TIN available enter Reason A, B or C');
            $table->text('reason_b_explanation')->nullable()
                ->comment('"Please explain why you are unable to obtain a TIN if you selected Reason B"');
            $table->string('tin_document_path', 255)->nullable()
                ->comment('Entity AOF: "Please provide copy of TIN Card OR NTN (National Tax Number)/Tax document"');
            $table->unsignedTinyInteger('sequence')->default(1)->comment('Grid ki row number');
            $table->timestamps();

            $table->unique(['customer_id', 'country_id'], 'cust_tax_residency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tax_residencies');
    }
};
