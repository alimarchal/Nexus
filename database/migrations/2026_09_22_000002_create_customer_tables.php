<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account opening (AOF) customer (CIF) tables: customers and their individual / organisation profiles,
 * nationalities, addresses, contacts, identifications, next of kin, tax residencies, FATCA and controlling persons.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createCustomersTable();
        $this->createCustomerIndividualsTable();
        $this->createCustomerOrganizationsTable();
        $this->createCustomerNationalitiesTable();
        $this->createCustomerAddressesTable();
        $this->createCustomerContactsTable();
        $this->createCustomerIdentificationsTable();
        $this->createCustomerNextOfKinTable();
        $this->createCustomerTaxResidenciesTable();
        $this->createCustomerFatcaDetailsTable();
        $this->createCustomerControllingPersonsTable();
    }

    public function down(): void
    {
        $this->dropCustomerControllingPersonsTable();
        $this->dropCustomerFatcaDetailsTable();
        $this->dropCustomerTaxResidenciesTable();
        $this->dropCustomerNextOfKinTable();
        $this->dropCustomerIdentificationsTable();
        $this->dropCustomerContactsTable();
        $this->dropCustomerAddressesTable();
        $this->dropCustomerNationalitiesTable();
        $this->dropCustomerOrganizationsTable();
        $this->dropCustomerIndividualsTable();
        $this->dropCustomersTable();
    }

    // ------------------------------------------------------------------
    // Customers (was 2026_09_22_000010_create_customers_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMERS  (CIF -- Customer Information File)   << CORE TABLE >>
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #03 CIF header, #04 Customer Category,
    |                        #05 Special Category, #10 Zakat Exemption, #15 PEP
    | Source : AOF-Individual p.2 & p.3  |  AOF-Entity p.2 & p.4
    |
    | ===================== DONO PDFs KO JORNE WALA TABLE =====================
    | Dono forms ka CIF header bilkul same hai: Branch Code + Client/Relationship
    | ID + Date. Is liye:
    |
    |   customers                        -> har customer/entity ka ek CIF
    |     |__ customer_individuals       -> "Customer Details For Individuals"
    |     |__ customer_organizations     -> "Customer Details For Business"
    |
    | `customer_type` batata hai kaunsa child record banega:
    |   individual      -> sirf customer_individuals
    |   sole_proprietor -> DONO (banda bhi, business bhi)   << bridging case
    |   entity          -> sirf customer_organizations
    |
    | Joint account ke liye alag "joint customer" nahi banta. Har joint holder ka
    | apna CIF hota hai aur `account_holders` unhein ek account se jorta hai --
    | isi liye form p.1 par 4 Client/Relationship ID boxes hain.
    | =========================================================================
    |
    | branch_id : aapke pehle se mojood `branches` table ko point karta hai
    |             (AOF header ka "Branch Code" box). Yahan koi nayi branches
    |             table nahi banayi gayi.
    */
    private function createCustomersTable(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            /* ---- #03 CIF header ---- */
            $table->string('cif_number', 20)->unique()->comment('Client/Relationship ID -- AOF p.1 aur CIF header');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete()
                ->comment('Branch Code -- mojooda branches table');
            $table->date('cif_date')->comment('CIF header ka "Date" box');

            /* ---- #04 Customer Category ---- */
            $table->enum('customer_type', ['individual', 'sole_proprietor', 'entity'])
                ->comment('customer_categories.requires_* se derive hota hai');
            $table->enum('aof_form_type', ['individual_joint_sole', 'entity'])
                ->comment('Kaunsa printed AOF bhara gaya: 15-page Individual/Joint/Sole Proprietor ya 17-page Govt/Company');
            $table->foreignUuid('customer_category_id')->constrained()->restrictOnDelete();
            $table->string('customer_category_other', 150)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('category_code_description', 200)->nullable()->comment('Form box: "Category Code & Desc."');
            $table->foreignUuid('economic_sector_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Economic Sector Code');

            /* ---- #10 Zakat Exemption ---- */
            $table->boolean('is_zakat_exempt')->default(false)->comment('Zakat Exemption: Yes / No');
            $table->foreignUuid('zakat_exemption_reason_id')->nullable()->constrained()->nullOnDelete();
            $table->string('zakat_exemption_other', 150)->nullable();
            $table->date('cz50_submitted_on')->nullable()
                ->comment('Form: "Subject to submission of affidavit/declaration form CZ-50 on time"');

            /* ---- #15 PEP (For Bank Use Only) ---- */
            $table->boolean('is_pep')->default(false)
                ->comment('Politically Exposed Person / Political Connections: Yes / No');
            $table->boolean('pep_form_attached')->default(false)
                ->comment('"if Yes, please complete and attached the PEP declaration form"');

            /* ---- Record lifecycle (For Bank Use Only ke maker/checker boxes) ---- */
            $table->enum('status', ['draft', 'pending_verification', 'active', 'closed', 'rejected'])->default('draft');
            // Adds created_by / updated_by columns (app-wide UserTracking convention).
            $table->userTracking();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('"Branch Supervisor/Data Entry Checker"');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('"*Authorized by (Name)"');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes()->comment('CIF hard-delete nahi hota -- record retention requirement');

            $table->index(['customer_type', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('is_pep');
        });

        /* #05 -- Special Category of Account (ek customer ke 1 se zyada ho sakte hain) */
        Schema::create('customer_special_category', function (Blueprint $table): void {
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('special_category_id')->constrained()->cascadeOnDelete();
            $table->string('extra_value', 100)->nullable()
                ->comment('Non-Resident => "(in years) Time since residing out side Pakistan"');
            $table->string('other_description', 150)->nullable()->comment('Others => "(Please Specify)"');
            $table->timestamps();

            $table->unique(['customer_id', 'special_category_id'], 'cust_special_cat_unique');
        });
    }

    private function dropCustomersTable(): void
    {
        Schema::dropIfExists('customer_special_category');
        Schema::dropIfExists('customers');
    }

    // ------------------------------------------------------------------
    // Individuals (was 2026_09_22_000011_create_customer_individuals_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER INDIVIDUALS  ("Customer Details For Individuals")
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #06, #11 (Visually Impaired/Blind block), #09 (MNP)
    | Source : AOF-Individual p.2 -- Name Mr/Mrs/Ms, S/o D/o W/o, Mother's Maiden
    |          Name, Gender, Marital Status, Education, Date of Birth, Country of
    |          Birth, City of Birth, Country of Residence, Profession, Designation,
    |          N.T.N Number, Details for Minor Account Holders (Name of Guardian,
    |          Relationship with Guardian/Minor, Employer's/Educational Institution
    |          Name & Address)
    |          AOF-Individual p.3 -- MNP question, "For Visually Impaired Persons/
    |          Blind Customers Only" (Name of Witness, Signature, Relation with
    |          Customer, CNIC)
    |
    | NOTE:
    | - `customers` ke sath 1:1 (customer_id unique).
    | - Entity AOF mein yeh block nahi hota, MAGAR entity ke authorized signatories
    |   ka bhi apna CIF banta hai (AOF-Ent p.13: "CIF required of all authorized
    |   signatories") -- us waqt bhi yahi table bharti hai. Is liye ek hi table
    |   dono forms ke afraad ko cover karti hai.
    | - Nationality yahan column nahi: form 3 nationalities allow karta hai, is
    |   liye `customer_nationalities` alag table hai.
    */
    private function createCustomerIndividualsTable(): void
    {
        Schema::create('customer_individuals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();

            /* ---- Name block ---- */
            $table->string('title', 10)->nullable()->comment('Mr / Mrs / Ms');
            $table->string('full_name', 150)->comment('*Name');
            $table->string('full_name_ur', 150)->nullable();
            $table->foreignUuid('parentage_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('*S/o, D/o, W/o');
            $table->string('parent_or_spouse_name', 150)->nullable();
            $table->string('mother_maiden_name', 150)->nullable()->comment("*Mother's Maiden Name");

            /* ---- Demographics ---- */
            $table->foreignUuid('gender_id')->nullable()->constrained()->nullOnDelete()->comment('*Gender');
            $table->foreignUuid('marital_status_id')->nullable()->constrained()->nullOnDelete()->comment('*Marital Status');
            $table->string('marital_status_other', 80)->nullable()->comment('Others => "(Please Specify)"');
            $table->foreignUuid('education_level_id')->nullable()->constrained()->nullOnDelete()->comment('Education');
            $table->string('education_other', 80)->nullable();
            $table->date('date_of_birth')->comment('*Date of Birth');
            $table->foreignUuid('birth_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Country of Birth');
            $table->string('birth_city', 100)->nullable()->comment('City of Birth');
            $table->foreignUuid('residence_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('*Country of Residence');

            /* ---- Profession ---- */
            $table->foreignUuid('profession_id')->nullable()->constrained()->nullOnDelete()->comment('*Profession');
            $table->string('profession_other', 120)->nullable();
            $table->string('designation', 120)->nullable()->comment('Designation');
            $table->string('ntn', 30)->nullable()->comment('N.T.N Number');

            /* ---- Details for Minor Account Holders ---- */
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name', 150)->nullable()->comment('*Name of Guardian');
            $table->foreignUuid('guardian_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('Relationship with Guardian/Minor');
            $table->foreignUuid('guardian_customer_id')->nullable()->constrained('customers')->nullOnDelete()
                ->comment('Agar guardian ka apna CIF mojood hai to link');
            $table->string('employer_institution_name', 200)->nullable()
                ->comment("Employer's/Educational Institution Name");
            $table->text('employer_institution_address')->nullable()
                ->comment("Employer's/Educational Institution Address");

            /* ---- #09 Mobile Number Portability (MNP) ---- */
            $table->boolean('has_availed_mnp')->nullable()
                ->comment('"Have you availed Mobile Number Portability (MNP) services?" Yes/No');
            $table->string('mnp_new_provider', 100)->nullable()
                ->comment('"please mention name of your new Mobile Service provider"');

            /* ---- #11 For Visually Impaired Persons/Blind Customers Only ---- */
            $table->boolean('requires_witness')->default(false)
                ->comment('*Witness is mandatory for illiterate/visually impaired customers');
            $table->string('witness_name', 150)->nullable()->comment('Name of Witness (Accompanied by Customer)');
            $table->string('witness_relation', 100)->nullable()->comment('Relation with Customer');
            $table->string('witness_cnic', 25)->nullable()->comment('CNIC');
            $table->string('witness_signature_path', 255)->nullable()->comment('Signature');

            /* ---- Important Notes (c) & (e): photograph / thumb impression ---- */
            $table->string('photograph_path', 255)->nullable()
                ->comment('Photo Account / shaky-immature signature case: passport sized photograph');
            $table->boolean('thumb_impression_taken')->default(false)
                ->comment('Left & right thumb impressions on SS Card');

            $table->timestamps();

            $table->index('full_name');
            $table->index('date_of_birth');
        });
    }

    private function dropCustomerIndividualsTable(): void
    {
        Schema::dropIfExists('customer_individuals');
    }

    // ------------------------------------------------------------------
    // Organizations (was 2026_09_22_000012_create_customer_organizations_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerOrganizationsTable(): void
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

    private function dropCustomerOrganizationsTable(): void
    {
        Schema::dropIfExists('customer_organizations');
    }

    // ------------------------------------------------------------------
    // Nationalities (was 2026_09_22_000013_create_customer_nationalities_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerNationalitiesTable(): void
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

    private function dropCustomerNationalitiesTable(): void
    {
        Schema::dropIfExists('customer_nationalities');
    }

    // ------------------------------------------------------------------
    // Addresses (was 2026_09_22_000014_create_customer_addresses_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerAddressesTable(): void
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

    private function dropCustomerAddressesTable(): void
    {
        Schema::dropIfExists('customer_addresses');
    }

    // ------------------------------------------------------------------
    // Contacts (was 2026_09_22_000015_create_customer_contacts_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerContactsTable(): void
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

    private function dropCustomerContactsTable(): void
    {
        Schema::dropIfExists('customer_contacts');
    }

    // ------------------------------------------------------------------
    // Identifications (was 2026_09_22_000016_create_customer_identifications_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerIdentificationsTable(): void
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

    private function dropCustomerIdentificationsTable(): void
    {
        Schema::dropIfExists('customer_identifications');
    }

    // ------------------------------------------------------------------
    // NextOfKin (was 2026_09_22_000017_create_customer_next_of_kin_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerNextOfKinTable(): void
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

    private function dropCustomerNextOfKinTable(): void
    {
        Schema::dropIfExists('customer_next_of_kin');
    }

    // ------------------------------------------------------------------
    // TaxResidencies (was 2026_09_22_000018_create_customer_tax_residencies_table.php)
    // ------------------------------------------------------------------
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
    private function createCustomerTaxResidenciesTable(): void
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

    private function dropCustomerTaxResidenciesTable(): void
    {
        Schema::dropIfExists('customer_tax_residencies');
    }

    // ------------------------------------------------------------------
    // Fatca (was 2026_09_22_000019_create_customer_fatca_details_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER FATCA / CRS DETAILS
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #12 FATCA, #13 CRS Entity Classification
    | Source : AOF-Individual p.3 -- "*FATCA for Individual/Controlling Person/
    |          Sole Proprietor/Joint Account Holder/Minor & Mandate Holder"
    |          (Questions 1-4, Form W9 / W-8 BEN)
    |          AOF-Entity p.3 -- "*FATCA For Non-Financial Entities (Companies)"
    |          (a) Country of Incorporation is US?  (b) Active or Passive NFE?
    |          AOF-Entity p.3 & p.4 -- "*CRS for Non-Individual Accounts" options
    |          (a) se (i), GIIN, stock exchange name
    |
    | ===================== DONO FORMS KA MERGE =====================
    | Individual FATCA 4 yes/no sawal poochta hai; Entity FATCA incorporation
    | country aur Active/Passive NFE. Dono ek hi table mein hain -- individual
    | columns entity ke liye NULL rahenge aur ulta bhi. Fayda: FATCA/CRS reporting
    | ek hi query se, chahe customer koi bhi type ho.
    | ===============================================================
    */
    private function createCustomerFatcaDetailsTable(): void
    {
        Schema::create('customer_fatca_details', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained()->cascadeOnDelete();

            /* ---------- INDIVIDUAL FATCA (AOF-Ind p.3) ---------- */
            $table->boolean('q1_is_us_person')->nullable()
                ->comment('Q1: "Are you a US National/Citizen/Green Card Holder or U.S. Resident"');
            $table->boolean('q2_country_of_birth_us')->nullable()->comment('Q2: "Is your country of birth US?"');
            $table->boolean('q3_has_us_address_or_phone')->nullable()
                ->comment('Q3: "Do you have a U.S. Address or Telephone No?"');
            $table->boolean('q4_has_us_mandate_or_links')->nullable()
                ->comment('Q4: mandate to a person having an address in the US / any US links');
            $table->boolean('form_w9_signed')->default(false)->comment('"If Yes please sign Form W9"');
            $table->boolean('form_w8_ben_signed')->default(false)->comment('Non-US Person claim => W8 BEN');
            $table->boolean('us_nationality_revoked')->default(false)
                ->comment('"If applicant claims revocation of U.S. Nationality"');
            $table->string('loss_of_nationality_cert_path', 255)->nullable()
                ->comment('Certificate of loss of residency / written explanation of revocation');

            /* ---------- ENTITY FATCA (AOF-Ent p.3) ---------- */
            $table->boolean('entity_incorporated_in_us')->nullable()
                ->comment('(a) Country of Incorporation and/or Parent Company Country of Incorporation is the U.S.?');
            $table->foreignUuid('incorporation_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Form box: "Country of Incorporation (CoI)"');
            $table->enum('nfe_type', ['active', 'passive'])->nullable()
                ->comment('(b) Active Non-Financial Entity ya Passive Non-Financial Entity');
            $table->boolean('form_w8_ben_e_signed')->default(false)->comment('Passive NFE => W8-BEN-E');
            $table->string('ssn_ein_document_path', 255)->nullable()
                ->comment('"copy of Social Security Number Card (SSN)/Employer Identification Number (EIN)"');

            /* ---------- ENTITY CRS CLASSIFICATION (AOF-Ent p.3 & p.4) ---------- */
            $table->foreignUuid('crs_entity_classification_id')->nullable()
                ->constrained('crs_entity_classifications')->nullOnDelete()
                ->comment('"Please Classify your Entity Type (Only select One option from the list below)"');
            $table->string('giin', 30)->nullable()->comment('Global Intermediary Identification Number');
            $table->string('listed_stock_exchange', 150)->nullable()
                ->comment('Option (d): name of stock exchange where company is listed');

            /* ---------- Common ---------- */
            $table->boolean('is_tax_resident_other_country')->nullable()
                ->comment('"Are you a Tax Resident of country other than Pakistan & USA?"');
            $table->date('self_certification_date')->nullable();
            $table->timestamps();
        });
    }

    private function dropCustomerFatcaDetailsTable(): void
    {
        Schema::dropIfExists('customer_fatca_details');
    }

    // ------------------------------------------------------------------
    // ControllingPersons (was 2026_09_22_000020_create_customer_controlling_persons_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER CONTROLLING PERSONS (FATCA 10% shareholders / CRS 20% controllers)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #12 & #13
    | Source : AOF-Entity p.3 -- "Declare the individual(s) holding 10% or more
    |          shares/voting rights (Use separate sheet in case of more
    |          applicants)": Applicant 1..10, Designation, Share holding/Voting
    |          rights %
    |          AOF-Entity p.3 & p.4 -- CRS: "Identity the Type of Controlling
    |          Person (Having 20% or more shareholding & Voting rights)"
    |
    | NOTE:
    | - Form 10 fixed rows deta hai aur "separate sheet" kehta hai -> DB mein
    |   unlimited rows.
    | - `declaration_basis` batata hai row FATCA (10%) ke liye hai ya CRS (20%),
    |   kyunki thresholds alag hain magar log wahi ho sakte hain.
    | - AOF-Ent p.13: "CIF required of all members and authorized signatories" --
    |   is liye `person_customer_id` se CIF link ho jata hai.
    */
    private function createCustomerControllingPersonsTable(): void
    {
        Schema::create('customer_controlling_persons', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete()->comment('Jis entity ka controller hai');
            $table->foreignUuid('person_customer_id')->nullable()->constrained('customers')->nullOnDelete()
                ->comment('Agar is shakhs ka apna CIF bhi hai');
            $table->enum('declaration_basis', ['fatca_10_percent', 'crs_20_percent', 'both'])
                ->default('fatca_10_percent');
            $table->unsignedTinyInteger('sequence')->default(1)->comment('Applicant 1..10 (form ka number)');
            $table->string('person_name', 150)->comment('Applicant ka naam');
            $table->string('designation', 120)->nullable()->comment('Designation');
            $table->decimal('shareholding_percentage', 5, 2)->nullable()->comment('Share holding / Voting rights %');
            $table->foreignUuid('controlling_person_type_id')->nullable()
                ->constrained('controlling_person_types')->nullOnDelete()
                ->comment('Owner (direct or indirect) / Settlor / Trustee / Protector / Beneficiary etc.');
            $table->timestamps();

            $table->index(['customer_id', 'declaration_basis']);
        });
    }

    private function dropCustomerControllingPersonsTable(): void
    {
        Schema::dropIfExists('customer_controlling_persons');
    }
};
