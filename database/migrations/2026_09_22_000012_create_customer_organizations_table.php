<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER ORGANIZATIONS  ("Customer Details For Business")
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #06 (business side), #33 (CDD business block)
| Source : AOF-Entity p.2 -- *Company/Business Name, *Nature of Business,
|          Business Registration No., Years in Business, Business Commencement
|          Date, Business Incorporation Date, Issued Date, Expiry Date,
|          NTN Number, Country of Incorporation, Sales Tax Registration No.,
|          Membership Number of Chamber of Commerce/Trade Body,
|          Tax Exemption (On Cash Withdrawal / On Profit)
|          AOF-Entity p.17 -- Parent Company/Group Name, Other Companies of
|          the Group, Main Geographic Area of Activity, If Outside Pakistan
|          (Country, Province/State), Number of employees
|          AOF-Individual p.15 -- Sole Proprietor: Business Name, Other
|          Business of the Proprietor, Main Geographic Area of Activity,
|          Number of employees, IS A DNFBP?
|
| NOTE:
| - Sole Proprietor ka customer_type = 'sole_proprietor' hota hai aur uske
|   DONO records bante hain: customer_individuals + customer_organizations.
|   Isi tarah dono PDFs ek hi design mein fit ho jate hain.
| - Registered / Current business address yahan nahi -- wo customer_addresses
|   mein address_type ke sath jati hain.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_organizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();

            /* ---- Identity ---- */
            $table->string('business_name', 200)->comment('*Company/Business Name');
            $table->string('business_name_ur', 200)->nullable();
            $table->string('other_business_of_proprietor', 200)->nullable()
                ->comment('AOF-Ind p.15: "Other Business of the Proprietor"');

            /* ---- Nature of Business ---- */
            $table->foreignUuid('business_nature_id')->nullable()->constrained()->nullOnDelete()->comment('*Nature of Business');
            $table->string('business_nature_other', 150)->nullable()->comment('Others => "(Please specify)"');

            /* ---- Registration / Incorporation ---- */
            $table->string('business_registration_number', 60)->nullable()->comment('Business Registration No. (SECP BRN etc.)');
            $table->date('issued_date')->nullable()->comment('Issued Date');
            $table->date('expiry_date')->nullable()->comment('Expiry Date');
            $table->date('business_commencement_date')->nullable()->comment('Business Commencement Date');
            $table->date('business_incorporation_date')->nullable()->comment('Business Incorporation Date');
            $table->unsignedSmallInteger('years_in_business')->nullable()->comment('Years in Business');
            $table->foreignUuid('incorporation_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Country of Incorporation');

            /* ---- Tax ---- */
            $table->string('ntn', 30)->nullable()->comment('NTN Number (if available)');
            $table->string('sales_tax_registration_number', 40)->nullable()->comment('Sales Tax Registration No.');
            $table->string('chamber_membership_number', 60)->nullable()
                ->comment('Membership Number of Chamber of Commerce/Trade Body');
            $table->boolean('tax_exempt_on_cash_withdrawal')->default(false)->comment('Tax Exemption: On Cash Withdrawal');
            $table->boolean('tax_exempt_on_profit')->default(false)
                ->comment('Tax Exemption: On Profit (Tax Exemption Certificate from FBR/CBR)');

            /* ---- Group / scale (CDD business block) ---- */
            $table->string('parent_company_name', 200)->nullable()->comment('Parent Company/Group Name');
            $table->text('group_companies')->nullable()->comment('Other Companies of the Group');
            $table->string('main_geographic_area', 200)->nullable()->comment('Main Geographic Area of Activity');
            $table->string('outside_pakistan_country', 100)->nullable()->comment('If Outside Pakistan -> Country');
            $table->string('outside_pakistan_province', 100)->nullable()->comment('If Outside Pakistan -> Province/State');
            $table->unsignedInteger('number_of_employees')->nullable()->comment('Number of employees');
            $table->boolean('is_dnfbp')->default(false)
                ->comment('AOF-Ind p.15: "IS A DNFBP?" (Designated Non-Financial Business and Professions)');

            $table->timestamps();

            $table->index('business_name');
            $table->index('ntn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_organizations');
    }
};
