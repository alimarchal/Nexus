<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER ADDRESSES  ("Contact Details" address boxes)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #08, #23 (Mailing Instructions)
| Source : AOF-Individual p.2 -- Permanent Residential/Registered Business
|          Address + Current Residential Address (For Individual A/c)/Current
|          Business Address (for Business A/c)
|          AOF-Individual p.4 -- Account Statement/Others Mailing Instructions
|          AOF-Entity p.2 -- Registered Business Address + Current Business
|          Address
|          AOF-Entity p.5 -- Account Statement Mailing Instructions
|
| ===================== DONO FORMS KA MERGE =====================
| Individual form "Permanent Residential" likhta hai, Entity form "Registered
| Business" -- boxes bilkul same hain (House/Office No., Street/Area, Tehsil/
| District, Nearest Landmark, City, Country, Postal Code). Is liye alag tables
| ke bajaye ek hi table + `address_type`.
| ===============================================================
|
| Form warning: "Do not use a PO Box or in-care-of address".
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('address_type', [
                'permanent_residential',   // Individual AOF
                'registered_business',     // Entity AOF (same box, alag label)
                'current_residential',     // Individual AOF
                'current_business',        // dono forms
                'mailing',                 // Account Statement/Others Mailing Instructions
            ]);
            $table->string('house_office_no', 100)->nullable()->comment('House/Office No.');
            $table->string('street_area', 150)->nullable()->comment('Street/Area');
            $table->string('tehsil_district', 120)->nullable()->comment('Tehsil/District');
            $table->string('nearest_landmark', 150)->nullable()->comment('Nearest Landmark');
            $table->string('city', 100)->nullable()->comment('City');
            $table->foreignUuid('country_id')->nullable()->constrained()->nullOnDelete()->comment('Country');
            $table->string('postal_code', 20)->nullable()->comment('Postal Code');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'address_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
