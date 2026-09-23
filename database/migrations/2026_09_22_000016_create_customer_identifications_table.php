<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER IDENTIFICATIONS  ("Identification" block)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #07
| Source : AOF-Individual p.2 -- CNIC / Birth Certificate (Government
|          Authority) / CRC / B-Form / Alien Registration Card No. / POC /
|          NICOP / POR, Passport No. (Foreign Individual Only), Issued Date,
|          Expiry Date of Identification Document, Place of Issuance (City)
|          + AOF-Individual p.12 / AOF-Entity p.14 Important Notes
|
| NOTE:
| - Ek customer ke 1 se zyada ID ho sakti hain (CNIC + Passport), is liye
|   multi-row table.
| - Important Note (d) + T&C 5: expired CNIC par account tab khul sakta hai
|   jab NADRA receipt/token attach ho aur 3 mahine mein renewed CNIC aa jaye.
| - AOF-Entity Important Note (j): NADRA Verisys report photocopy ki jagah
|   record par rakhi jati hai -- isi liye verisys columns.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_identifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('identification_document_type_id')->constrained('identification_document_types')
                ->restrictOnDelete();
            $table->string('document_number', 50);
            $table->date('issue_date')->nullable()->comment('Issued Date');
            $table->date('expiry_date')->nullable()->comment('Expiry Date of Identification Document');
            $table->string('place_of_issuance', 120)->nullable()->comment('Place of Issuance (City)');
            $table->boolean('is_primary')->default(false);

            /* Important Note (d) / T&C 5 -- expired CNIC exception */
            $table->boolean('is_expired_accepted')->default(false);
            $table->string('nadra_token_number', 60)->nullable()->comment('NADRA receipt/token number');
            $table->date('renewal_due_date')->nullable()->comment('3 maheene ke andar renewed CNIC lazmi');

            /* AOF-Entity Important Note (j) -- NADRA Verisys */
            $table->boolean('verisys_verified')->default(false);
            $table->date('verisys_date')->nullable();
            $table->string('verisys_report_path', 255)->nullable();

            $table->boolean('is_attested')->default(false)
                ->comment('Important Note (b): Gazetted officer/Nazim/Administrator/Bank officer');
            $table->string('scan_path', 255)->nullable()->comment('Photocopy of identity document');

            $table->timestamps();

            $table->unique(['customer_id', 'identification_document_type_id', 'document_number'], 'cust_ident_unique');
            $table->index('document_number');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_identifications');
    }
};
