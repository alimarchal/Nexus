<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER CONTACTS  ("Contact Detail" -- phone / email / fax)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #09
| Source : AOF-Individual p.3 -- ***Mobile No., Telephone No. Residential/
|          Office (1) & (2), **Personal/Office E-Mail ID, Personal/Office Fax
|          AOF-Entity p.2 -- Mobile No., Telephone No. Residential/Office,
|          Office E-Mail ID, Office Fax
|
| NOTE:
| - Form 2 landline boxes deta hai, is liye har contact alag row.
| - Form footnotes:
|     ** Mandatory Field for Internet Banking & BAJK E-Statement (email)
|     *** Mandatory Field for Mobile Banking (mobile)
|   Yeh flags columns mein rakhe gaye hain.
| - T&C clause 67: CIF ka phone number hi registered/Contact Center number
|   samjha jata hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contacts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('contact_type', [
                'mobile', 'telephone_residence_office', 'fax_personal_office', 'email_personal_office',
            ]);
            $table->string('country_code', 8)->nullable()
                ->comment('Form: "(Country Code is mandatory with Mobile & Telephone Number)"');
            $table->string('value', 150)->comment('Number ya email address');
            $table->unsignedTinyInteger('sequence')->default(1)->comment('Telephone (1) / (2)');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_registered_for_mobile_banking')->default(false)->comment('*** mandatory for Mobile Banking');
            $table->boolean('is_registered_for_estatement')->default(false)
                ->comment('** mandatory for Internet Banking & BAJK E-Statement');
            $table->boolean('is_contact_center_registered')->default(false)->comment('T&C clause 67');
            $table->timestamps();

            $table->index(['customer_id', 'contact_type']);
            $table->index('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
