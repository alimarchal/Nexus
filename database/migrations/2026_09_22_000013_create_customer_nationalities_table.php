<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER NATIONALITIES
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #06
| Source : AOF-Individual p.2 -- "*Nationality (1)" + "Other Nationalities (2)
|          (3)" + note "Disclose all Nationalities held"
|
| NOTE: Form 3 boxes deta hai. Columns banane ke bajaye alag table hai taake
| dual/triple citizenship poori store ho aur FATCA/CRS query aasan rahe.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_nationalities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('country_id')->constrained()->restrictOnDelete();
            $table->boolean('is_primary')->default(false)->comment('Nationality (1) = primary');
            $table->unsignedTinyInteger('sequence')->default(1)->comment('Form ke box ka number: 1, 2, 3');
            $table->timestamps();

            $table->unique(['customer_id', 'country_id'], 'cust_nationality_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_nationalities');
    }
};
