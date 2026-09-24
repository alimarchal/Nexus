<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account opening (AOF) account tables: accounts, holders, specimen signatures, cheque books and debit cards.
 * accounts.aof_version is 60 characters (the entity form identifier is 40 characters long).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createAccountsTable();
        $this->createAccountHoldersTable();
        $this->createSpecimenSignaturesTable();
        $this->createAccountChequeBooksTable();
        $this->createAccountDebitCardsTable();
    }

    public function down(): void
    {
        $this->dropAccountDebitCardsTable();
        $this->dropAccountChequeBooksTable();
        $this->dropSpecimenSignaturesTable();
        $this->dropAccountHoldersTable();
        $this->dropAccountsTable();
    }

    // ------------------------------------------------------------------
    // Accounts (was 2026_09_22_000030_create_accounts_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ACCOUNTS   << CORE TABLE >>
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #01 Particulars of Account, #02 Bank Use Only,
    |                        #16 Type of Account (Rupee), #17 Type of Account
    |                        (Foreign Currency), #18 Operating Instructions,
    |                        #19 Title of Account, #20 Special/Standing
    |                        Instructions, #21 SMS Alerts, #22 BAJK Digital
    |                        Channels/App, #23 Mailing Instructions, #26 T&C
    | Source : AOF-Individual p.1, p.4  |  AOF-Entity p.1, p.5
    |
    | NOTE:
    | - `customers` (kaun hai) aur `accounts` (kya khula hai) alag hain. Joint
    |   account ke 4 holders = 4 customers + 1 account, jinhein `account_holders`
    |   jorta hai. T&C clause 3 ka rule isi se enforce hota hai.
    | - Product ki qismein enum nahi -- `account_products` se FK. Naya product
    |   launch ho to migration nahi, sirf ek row.
    | - Mailing address `customer_addresses` (type = mailing) ko point karta hai
    |   taake address duplicate na ho.
    | - Balance / transaction columns yahan jaan boojh kar nahi hain: yeh design
    |   Account Opening Form + KYC capture ke liye hai, core banking ledger ke
    |   liye nahi.
    */
    private function createAccountsTable(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            /* ---- #01 / #02 Particulars of Account + Bank Use Only ---- */
            $table->string('account_number', 24)->unique()->comment('Account Number');
            $table->string('iban', 34)->nullable()->unique()->comment('IBAN -- form par "P K" prefilled boxes');
            $table->string('title_of_account', 200)->comment('Title of Account');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete()
                ->comment('Branch Code / Branch Name -- mojooda branches table');
            $table->string('profit_center', 20)->nullable()->comment('Profit Center');
            $table->date('opening_date')->comment('Bank Use Only ka "Date" box');

            /* ---- #16 / #17 Type of Account ---- */
            $table->foreignUuid('account_product_id')->constrained()->restrictOnDelete();
            $table->string('product_other', 150)->nullable()->comment('"Other ____ (Please Specify)"');
            $table->foreignUuid('currency_id')->constrained()->restrictOnDelete();
            $table->string('currency_other', 60)->nullable()->comment('FCY "Other (Please Specify)"');
            $table->enum('account_class', ['current', 'saving'])->comment('FCY box par Current / Saving ka alag tick');
            $table->boolean('is_foreign_currency')->default(false);

            /* ---- #18 Operating Instructions ---- */
            $table->foreignUuid('operating_instruction_id')->constrained()->restrictOnDelete();
            $table->string('operating_instruction_other', 150)->nullable()->comment('Other => "(Please Specify)"');

            /* ---- #20 Special/Standing Instructions ---- */
            $table->text('special_instructions')->nullable();

            /* ---- #21 SMS Alerts ---- */
            $table->boolean('sms_alerts_subscribed')->default(false)
                ->comment('"Would you like to subscribe for paid SMS Services..." Yes/No');

            /* ---- #22 BAJK Digital Channels/App ---- */
            $table->boolean('digital_channels_opted')->default(false)
                ->comment('"Would you like to avail BAJK Digital Channels/App financial transaction facility?"');
            $table->boolean('digital_channels_biometric_verified')->default(false)
                ->comment('"on the basis of provided account biometric verification"');

            /* ---- #23 Account Statement/Others Mailing Instructions ---- */
            $table->foreignUuid('statement_delivery_mode_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('statement_frequency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('mailing_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();

            /* ---- Bank Use Only: Initial Deposit + Zakat ---- */
            $table->decimal('initial_deposit', 18, 2)->nullable()->comment('Bank Use Only: "Initial Deposit"');
            $table->boolean('zakat_applicable')->default(false)
                ->comment('"Zakat is applicable in PKR-Saving Account Only" + customer ka exemption status');

            /* ---- #26 Terms & Conditions / #28 Indemnity & Undertaking ---- */
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('aof_version', 60)->nullable()
                ->comment('Kaunsa form version sign hua: BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026');

            /* ---- Lifecycle ---- */
            $table->enum('status', ['pending', 'active', 'closed'])->default('pending');
            $table->userTracking();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['account_product_id', 'status']);
        });
    }

    private function dropAccountsTable(): void
    {
        Schema::dropIfExists('accounts');
    }

    // ------------------------------------------------------------------
    // Holders (was 2026_09_22_000031_create_account_holders_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ACCOUNT HOLDERS  (customers <-> accounts)   << DONO FORMS KA PUL >>
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #02 (Client/Relationship ID 1-4), #30 (Applicant's
    |                        signature boxes)
    | Source : AOF-Individual p.1 (Client/Relationship ID (1)..(4)) + p.14
    |          (Applicant's (1)..(4) Name / Signature / Thumb Impression)
    |          AOF-Entity p.1 (Client/Relationship ID (1)..(4)) + p.15 & p.16
    |          (Applicant's (1)..(12) Name / Signature / Company's Stamp)
    |
    | ===================== YEH TABLE KYUN ZAROORI HAI =====================
    | Individual form 4 applicants tak allow karta hai, Entity form 12 tak. Agar
    | accounts table mein holder1_id, holder2_id... columns banate to dono forms
    | ke liye alag design banana parta. Pivot se:
    |   - jitne marzi holders (koi limit nahi),
    |   - har holder ka apna role (joint / proprietor / guardian / authorized
    |     signatory / mandate holder / trustee / office bearer),
    |   - signing order aur designation bhi mehfooz.
    | ======================================================================
    */
    private function createAccountHoldersTable(): void
    {
        Schema::create('account_holders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $table->enum('holder_role', [
                'primary',               // pehla / main account holder
                'joint',                 // Joint account holder (Individual AOF)
                'sole_proprietor',       // Sole Proprietorship
                'minor',                 // minor jiske naam account hai
                'guardian',              // minor ka guardian jo operate karta hai
                'authorized_signatory',  // Entity AOF: authorized signatories
                'mandate_holder',        // Operating Instructions = Mandate
                'agent',                 // Agents Accounts (Power of Attorney/Agency Agreement)
                'executor',              // Executors & Administrators
                'trustee',               // Trust / Board of Trustees
                'office_bearer',         // Club / Society / Association ka ohdedar
            ])->default('primary');
            $table->unsignedTinyInteger('applicant_number')->nullable()
                ->comment('Form par "Applicant\'s (1)".."(12)" ka number');
            $table->boolean('is_signatory')->default(true);
            $table->unsignedTinyInteger('signing_order')->nullable();
            $table->foreignUuid('relationship_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Primary holder ke sath rishta (joint / guardian cases)');
            $table->string('designation', 120)->nullable()
                ->comment('Entity: Director / Secretary / DDO / Trustee etc. (governing body list se)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'customer_id', 'holder_role'], 'account_holder_unique');
            $table->index(['account_id', 'is_active']);
            $table->index(['customer_id', 'is_active']);
        });
    }

    private function dropAccountHoldersTable(): void
    {
        Schema::dropIfExists('account_holders');
    }

    // ------------------------------------------------------------------
    // Signatures (was 2026_09_22_000032_create_specimen_signatures_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | SPECIMEN SIGNATURES  (SS Card)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #30
    | Source : AOF-Individual p.14 -- "Applicant's Signature/Thumb Impression",
    |          "Company's/Organisation's Stamp"
    |          AOF-Entity p.15 & p.16 -- Applicant's (1)..(12) ke signature boxes
    |          AOF Important Note (c) -- shaky/immature signature par 2 passport
    |          sized attested photographs + left & right thumb impressions on SS Card
    |          AOF-Individual p.12 -- "Obtaining Undertaking from Customer for
    |          Acceptance of Different Specimen Signatures for Account Operation"
    |          T&C clause 21 -- signature change hone par tasdeeq lazmi
    |
    | NOTE: Ek holder ke multiple specimen ho sakte hain (English + Urdu), is liye
    | alag table. Purana specimen `is_active = false` ke sath record par rehta hai,
    | delete nahi hota.
    */
    private function createSpecimenSignaturesTable(): void
    {
        Schema::create('specimen_signatures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_holder_id')->constrained()->cascadeOnDelete();
            $table->string('ss_card_number', 40)->nullable()->comment('SS Card number');
            $table->unsignedTinyInteger('specimen_number')->default(1)
                ->comment('"Different Specimen Signatures" undertaking ke tehat 1 se zyada');
            $table->enum('specimen_type', ['signature', 'thumb_impression', 'stamp'])->default('signature');
            $table->string('signature_image_path', 255)->nullable();
            $table->string('thumb_left_image_path', 255)->nullable()->comment('Important Note (c)');
            $table->string('thumb_right_image_path', 255)->nullable()->comment('Important Note (c)');
            $table->string('organization_stamp_path', 255)->nullable()
                ->comment("Company's/Organisation's Stamp");
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('T&C 21: signature change ki tasdeeq');
            $table->timestamps();

            $table->index(['account_holder_id', 'is_active']);
        });
    }

    private function dropSpecimenSignaturesTable(): void
    {
        Schema::dropIfExists('specimen_signatures');
    }

    // ------------------------------------------------------------------
    // ChequeBooks (was 2026_09_22_000033_create_account_cheque_books_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ACCOUNT CHEQUE BOOKS  ("Cheque Book Requisition")
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #25
    | Source : AOF-Individual p.5 -- Cheque Book Required Yes/No, Quantity
    |          Required, No. of Leaves: 10 Leaves / 25 Leaves / 50 Leaves / Others
    |          AOF-Entity p.5 -- Cheque Book Required Yes/No, Quantity Required,
    |          No. of Leaves: 25 Leaves / 50 Leaves / 100 Leaves / Others
    |
    | NOTE: Leaves ke options dono forms mein alag hain, is liye number column
    | rakha hai aur allowed values `account_products.allowed_cheque_leaves` se
    | validate hoti hain -- design ek hi rehta hai.
    | T&C 19: cheques sirf bank ke printed cheque par hi draw ho sakte hain.
    */
    private function createAccountChequeBooksTable(): void
    {
        Schema::create('account_cheque_books', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false)->comment('Cheque Book Required: Yes / No');
            $table->unsignedSmallInteger('quantity_required')->nullable()->comment('Quantity Required');
            $table->unsignedSmallInteger('leaves_count')->nullable()->comment('No. of Leaves: 10 / 25 / 50 / 100');
            $table->string('leaves_other', 60)->nullable()->comment('Others => "(Please Specify)"');
            $table->date('requested_on')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    private function dropAccountChequeBooksTable(): void
    {
        Schema::dropIfExists('account_cheque_books');
    }

    // ------------------------------------------------------------------
    // DebitCards (was 2026_09_22_000034_create_account_debit_cards_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ACCOUNT DEBIT CARDS  ("BAJK Debit Card")
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #24
    | Source : AOF-Individual p.5 -- "Please tick any of the following card type
    |          (for Individual & sole proprietors only)": BAJK Debit Card
    |          (Enhanced ATM & POS limits), Name on Card ("Maximum length is 19
    |          characters with spaces")
    |
    | NOTE:
    | - Entity AOF par debit card ka box nahi hai. Table phir bhi generic hai aur
    |   `card_types.available_for` control karta hai -- design ek hi rehta hai.
    | - Card account ke kisi ek holder ke naam par jaari hota hai, is liye
    |   account_holder_id bhi rakha hai (joint account mein 2 cards mumkin).
    | - T&C 47: debit card ki information/OTP mobile number par jati hai.
    */
    private function createAccountDebitCardsTable(): void
    {
        Schema::create('account_debit_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_holder_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Kis holder ke naam par card banega');
            $table->foreignUuid('card_type_id')->constrained()->restrictOnDelete();
            $table->string('name_on_card', 19)->comment('Name on Card -- max 19 characters with spaces');
            $table->date('requested_on')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    private function dropAccountDebitCardsTable(): void
    {
        Schema::dropIfExists('account_debit_cards');
    }
};
