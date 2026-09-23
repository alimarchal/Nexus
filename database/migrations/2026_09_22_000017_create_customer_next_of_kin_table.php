<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER NEXT OF KIN  ("Contact Persons" block)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #29
| Source : AOF-Individual p.14 -- "Next of kin to be contacted for ascertaining
|          my/our whereabouts": Name, Relationship, CNIC/CRC/NICOP/POC/Alient
|          Registration Card No., Address, Tehsil/District, Nearest Landmark,
|          City, Country, Post Code, Personal/Office Telephone No.,
|          Personal/Office Email ID
|
| NOTE: Yeh block sirf Individual AOF par hai. Table generic rakhi gayi hai
| taake entity ke authorized signatory ke CIF ke sath bhi use ho sake.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_next_of_kin', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150)->comment('Name');
            $table->foreignUuid('relationship_id')->nullable()->constrained()->nullOnDelete()->comment('Relationship');
            $table->string('relationship_other', 80)->nullable();
            $table->foreignUuid('identification_document_type_id')->nullable()
                ->constrained('identification_document_types')->nullOnDelete();
            $table->string('identification_number', 50)->nullable()
                ->comment('CNIC/CRC/NICOP/POC/Alient Registration Card No.');
            $table->string('address', 255)->nullable()->comment('Address');
            $table->string('tehsil_district', 120)->nullable()->comment('Tehsil/District');
            $table->string('nearest_landmark', 150)->nullable()->comment('Nearest Landmark');
            $table->string('city', 100)->nullable()->comment('City');
            $table->foreignUuid('country_id')->nullable()->constrained()->nullOnDelete()->comment('Country');
            $table->string('postal_code', 20)->nullable()->comment('Post Code');
            $table->string('telephone', 30)->nullable()->comment('Personal/Office Telephone No.');
            $table->string('email', 150)->nullable()->comment('Personal/Office Email ID');
            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_next_of_kin');
    }
};
