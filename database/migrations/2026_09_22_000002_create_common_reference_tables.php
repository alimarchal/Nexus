<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| COMMON REFERENCE DATA : countries + currencies
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #06 (Country of Birth / Nationality / Country of
|                        Residence / Country of Incorporation), #08 (Address
|                        Country), #14 (Tax Residence), #17 (Foreign Currency)
| Source : AOF-Individual p.2, p.3, p.4  |  AOF-Entity p.2, p.4, p.5
|
| NOTE:
| - Country dono forms mein kai jagah aata hai (Country of Birth, Nationality
|   1-3, Country of Residence, Country of Incorporation, Address Country,
|   Tax Residence, Home Remittance Country). Ek hi master table sab jagah FK
|   se use hoga -- data duplicate nahi hoga.
| - `dial_code` is liye hai ke form likhta hai: "Country Code is mandatory
|   with Mobile & Telephone Number".
| - `currencies` form ke "Type of Account Foreign Currency" box se aata hai:
|   $USD, GBP, AED, SAR, Euro, Other  + PKR (local).
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->char('iso2', 2)->unique()->comment('ISO 3166-1 alpha-2, e.g. PK');
            $table->char('iso3', 3)->unique()->comment('ISO 3166-1 alpha-3, e.g. PAK');
            $table->string('name', 100);
            $table->string('name_ur', 100)->nullable();
            $table->string('dial_code', 8)->nullable()->comment('Form: country code mandatory with mobile/telephone');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->char('code', 3)->unique()->comment('PKR, USD, GBP, EUR, AED, SAR');
            $table->string('name', 60);
            $table->string('symbol', 10)->nullable();
            $table->boolean('is_local')->default(false)->comment('true sirf PKR ke liye (Rupee products)');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};
