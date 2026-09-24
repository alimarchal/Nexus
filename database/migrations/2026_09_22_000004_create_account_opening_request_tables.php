<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Account opening (AOF) due diligence, beneficial owners, requests, customer documents and the module permissions.
 * account_opening_requests.aof_version is 60 characters (the entity form identifier is 40 characters long).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createCustomerDueDiligencesTable();
        $this->createCddPivotTables();
        $this->createUltimateBeneficialOwnersTable();
        $this->createAccountOpeningRequestsTable();
        $this->createCustomerDocumentsTable();
        $this->seedAccountOpeningPermissions();
    }

    public function down(): void
    {
        $this->removeAccountOpeningPermissions();
        $this->dropCustomerDocumentsTable();
        $this->dropAccountOpeningRequestsTable();
        $this->dropUltimateBeneficialOwnersTable();
        $this->dropCddPivotTables();
        $this->dropCustomerDueDiligencesTable();
    }

    // ------------------------------------------------------------------
    // DueDiligence (was 2026_09_22_000040_create_customer_due_diligences_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER DUE DILIGENCE (CDD)  -- "For Bank Use Only" blocks
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #31, #32, #33
    | Source : AOF-Individual p.14 -- "Customer Due Diligence Individual Account"
    |          AOF-Individual p.15 -- UBO block + "Customer Due Diligence Business
    |          Account (Sole Proprietor)"
    |          AOF-Entity p.17 -- "Customer Due Diligence Business Account" + UBO
    |
    | ===================== DONO FORMS KA MERGE =====================
    | Teeno CDD boxes (Individual / Sole Proprietor / Business) ke 80% fields
    | bilkul same hain:
    |   Type of Customer (Walk In / Marketed / Referred By), Physical Verification
    |   Conducted, Name Cleared From Proscribed List, Usual Mode of Credit & Debit
    |   Transaction, Purpose of Account, UBO block, Expected Aggregate Credit/Debit
    |   (Amount + No. of Transaction), Expected IFTT/OFTT Amount, Initial Deposit.
    |
    | Farq sirf income/occupation wale hisse ka hai, jo pivot tables
    | (cdd_income_sources / cdd_wealth_sources) se handle hota hai.
    | Is liye TEENO boxes ek hi table `customer_due_diligences` mein aate hain aur
    | `cdd_type` batata hai kaunsa box bhara gaya.
    | ===============================================================
    |
    | Yeh record per-ACCOUNT hai (form har account ke sath bharta hai), aur
    | customer_id bhi rakha hai taake customer-level reporting aasan ho.
    */
    private function createCustomerDueDiligencesTable(): void
    {
        Schema::create('customer_due_diligences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('cdd_type', ['individual', 'sole_proprietor', 'business'])
                ->comment('Kaunsa printed CDD box bhara gaya');

            /* ---- Type of Customer (teeno boxes mein same) ---- */
            $table->enum('customer_source', ['walk_in', 'marketed', 'referred'])->nullable()
                ->comment('Type of Customer: Walk In / Marketed / Referred By');
            $table->string('referred_by', 150)->nullable()->comment('"Referred By ____"');
            $table->boolean('physical_verification_conducted')->default(false)
                ->comment('Physical Verification Conducted');
            $table->boolean('proscribed_list_cleared')->default(false)
                ->comment('Name Cleared From Proscribed List');

            /* ---- Source of Income: Salaried / Pensioner (AOF-Ind p.14) ---- */
            $table->string('employer_name', 200)->nullable()->comment('Employer Name');
            $table->string('employer_designation', 120)->nullable()->comment('Designation');
            $table->text('employer_address')->nullable()->comment('Address of Employer');

            /* ---- Source of Income: Student / House Wife / Unemployed ---- */
            $table->string('fund_provider_employer', 200)->nullable()->comment('Employer of Fund Provider');
            $table->string('fund_provider_id_number', 50)->nullable()->comment('ID Document No. of Fund Provider');
            $table->foreignUuid('fund_provider_relationship_id')->nullable()->constrained('relationships')->nullOnDelete()
                ->comment('Relationship with Fund Provider');

            /* ---- Source of Income: Self Employed (AOF-Ind p.14) ---- */
            $table->boolean('is_dnfbp')->default(false)->comment('"IS A DNFBP?"');
            $table->string('business_name', 200)->nullable()->comment('Business Name');
            $table->string('business_nature_text', 200)->nullable()->comment('Business Nature');
            $table->text('business_address')->nullable()->comment('Business Address');
            $table->string('type_of_channels', 200)->nullable()->comment('Type of Channels');
            $table->string('type_of_counterparties', 200)->nullable()->comment('Type of Counterparties');
            $table->string('geographies_involved', 200)->nullable()->comment('Geographies Involved');

            /* ---- Source of Income: Labor/Daily Wages / Agriculturist ---- */
            $table->string('nature_of_work', 200)->nullable()->comment('Nature of Work');

            /* ---- Home Remittance ---- */
            $table->foreignUuid('home_remittance_country_id')->nullable()->constrained('countries')->nullOnDelete()
                ->comment('Home Remittance* (Country ____)');
            $table->string('home_remittance_relationship', 120)->nullable()->comment('Relationship ____');

            /* ---- Income figures ---- */
            $table->decimal('monthly_income', 18, 2)->nullable()->comment('Monthly Income (Individual CDD)');
            $table->decimal('monthly_net_income', 18, 2)->nullable()->comment('Monthly Net Income (Business CDD)');
            $table->unsignedInteger('number_of_employees')->nullable()->comment('Number of employees (Business CDD)');
            $table->string('source_of_income_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('source_of_wealth_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('purpose_of_account_other', 200)->nullable()->comment('Others => "(Please Specify)"');
            $table->string('credit_mode_other', 120)->nullable()->comment('Usual Mode of Credit Transaction: Others');
            $table->string('debit_mode_other', 120)->nullable()->comment('Usual Mode of Debit Transaction: Others');
            $table->string('counter_party_other', 120)->nullable()->comment('Expected Type of Counter Parties: Others');

            /* ---- Expected turnover (dono forms ke UBO box ke sath) ---- */
            $table->decimal('initial_deposit', 18, 2)->nullable()->comment('Initial Deposit');
            $table->decimal('expected_monthly_credit_amount', 18, 2)->nullable()
                ->comment('Expected Aggregate Credit (per month): Amount');
            $table->unsignedInteger('expected_monthly_credit_count')->nullable()->comment('No. of Transaction');
            $table->decimal('expected_monthly_debit_amount', 18, 2)->nullable()
                ->comment('Expected Aggregate Debit (per month): Amount');
            $table->unsignedInteger('expected_monthly_debit_count')->nullable()->comment('No. of Transaction');
            $table->decimal('expected_iftt_amount', 18, 2)->nullable()
                ->comment('Expected IFTT Amount (per month) -- Intra-Fund Transfer Transaction');
            $table->decimal('expected_oftt_amount', 18, 2)->nullable()
                ->comment('Expected OFTT Amount (per month) -- Online/Outward Fund Transfer');
            $table->string('currency_symbol', 10)->nullable()
                ->comment('Form note: "In case of Foreign Currency Account, please mention currency symbol"');
            $table->text('joint_account_note')->nullable()
                ->comment('Form note: joint accounts mein expected credit/debit aggregate aur source of income collectively');

            $table->timestamps();

            $table->unique(['account_id', 'customer_id', 'cdd_type'], 'cdd_account_customer_unique');
            $table->index('customer_id');
        });
    }

    private function dropCustomerDueDiligencesTable(): void
    {
        Schema::dropIfExists('customer_due_diligences');
    }

    // ------------------------------------------------------------------
    // CddPivots (was 2026_09_22_000041_create_cdd_pivot_tables.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CDD PIVOT TABLES  (multi-select checkboxes of the CDD blocks)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #31, #33
    | Source : AOF-Individual p.14 & p.15  |  AOF-Entity p.17
    |
    | Form ke jo boxes ek se zyada tick ho sakte hain, unhein pivot mein rakha hai:
    |   Source of Income/Occupation/Profession .......... cdd_income_source
    |   Source of Wealth ................................ cdd_wealth_source
    |   Usual Mode of Credit / Debit Transaction ........ cdd_transaction_mode
    |   Purpose of Account .............................. cdd_account_purpose
    |   Expected Type of Counter Parties ................ cdd_counter_party_type
    |
    | NOTE: Credit aur Debit modes ke options same hain, is liye ek hi pivot mein
    | `direction` column hai -- do alag tables ki zaroorat nahi.
    */
    private function createCddPivotTables(): void
    {
        Schema::create('cdd_income_source', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('income_source_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'income_source_id'], 'cdd_income_source_unique');
        });

        Schema::create('cdd_wealth_source', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wealth_source_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'wealth_source_id'], 'cdd_wealth_source_unique');
        });

        Schema::create('cdd_transaction_mode', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('transaction_mode_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['credit', 'debit'])
                ->comment('Usual Mode of Credit Transaction / Usual Mode of Debit Transaction');
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'transaction_mode_id', 'direction'], 'cdd_txn_mode_unique');
        });

        Schema::create('cdd_account_purpose', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_purpose_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'account_purpose_id'], 'cdd_account_purpose_unique');
        });

        Schema::create('cdd_counter_party_type', function (Blueprint $table): void {
            $table->foreignUuid('customer_due_diligence_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('counter_party_type_id')->constrained()->cascadeOnDelete();
            $table->string('other_description', 200)->nullable();
            $table->timestamps();

            $table->unique(['customer_due_diligence_id', 'counter_party_type_id'], 'cdd_counter_party_unique');
        });
    }

    private function dropCddPivotTables(): void
    {
        Schema::dropIfExists('cdd_counter_party_type');
        Schema::dropIfExists('cdd_account_purpose');
        Schema::dropIfExists('cdd_transaction_mode');
        Schema::dropIfExists('cdd_wealth_source');
        Schema::dropIfExists('cdd_income_source');
    }

    // ------------------------------------------------------------------
    // BeneficialOwners (was 2026_09_22_000042_create_ultimate_beneficial_owners_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ULTIMATE BENEFICIAL OWNERS (UBO)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #32
    | Source : AOF-Individual p.15 -- "Ultimate Beneficial Owner of the Account
    |          (if different from Customer)", "Relationship with Customer",
    |          "Identification document of Ultimate Beneficial Owner
    |          CNIC/SNIC/NICOP/POC/Passport/Alient Registered Card Number"
    |          AOF-Entity p.17 -- same block
    |          AOF-Individual p.12 -- "Ultimate Beneficial Owner (UBO) Declaration
    |          Form" (Sole Proprietorship documentation)
    |
    | NOTE: Dono forms mein yeh block bilkul same hai, is liye ek hi table.
    | Account ke sath juda hai kyunki form ise "For Bank Use Only" CDD box mein
    | rakhta hai.
    */
    private function createUltimateBeneficialOwnersTable(): void
    {
        Schema::create('ultimate_beneficial_owners', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_due_diligence_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150)->comment('Ultimate Beneficial Owner of the Account (if different from Customer)');
            $table->foreignUuid('relationship_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Relationship with Customer');
            $table->string('relationship_other', 120)->nullable();
            // MariaDB caps identifiers at 64 characters and the generated name
            // (ultimate_beneficial_owners_identification_document_type_id_foreign)
            // is 66, so the constraint is named explicitly.
            $table->foreignUuid('identification_document_type_id')->nullable()
                ->constrained(table: 'identification_document_types', indexName: 'ubo_identification_doc_type_foreign')
                ->nullOnDelete();
            $table->string('identification_number', 50)->nullable()
                ->comment('CNIC/SNIC/NICOP/POC/Passport/Alient Registered Card Number');
            $table->boolean('declaration_form_received')->default(false)
                ->comment('UBO Declaration Form (documentation checklist)');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    private function dropUltimateBeneficialOwnersTable(): void
    {
        Schema::dropIfExists('ultimate_beneficial_owners');
    }

    // ------------------------------------------------------------------
    // Requests (was 2026_09_22_000050_create_account_opening_requests_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | ACCOUNT OPENING REQUESTS  (bhara hua AOF khud, + recommendation block)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #26 T&C, #28 Indemnity & Undertaking, #34 Recommendation
    | Source : AOF-Individual p.13 -- "INDEMNITY & UNDERTAKING" + "I/We hereby
    |          agree with the Terms & Conditions ... Shariah Compliant manner"
    |          AOF-Individual p.15 / AOF-Entity p.17 -- "I/We recommend to open the
    |          Account subject to fulfilling all account opening requirements. The
    |          bonafide have been confirmed.": Name of Sale Staff/relationship Mgr
    |          (1)(2), Employee No., Signature of Sales Staff/Relationship Mgr,
    |          *Authorized by (Name), Employee Number/PP.No, Signature,
    |          *Branch Supervisor/Data Entry Checker (Individual form) /
    |          *Branch Manager/Data Entry Checker (Entity form)
    |
    | NOTE: Yeh table ek bhare hue form ka "application" record hai. Ek request se
    | ek account banta hai; maker-checker ka poora trail yahin hai.
    | Individual form "Branch Supervisor" likhta hai aur Entity form "Branch
    | Manager" -- dono ke liye ek hi column `checker_name` aur `checker_title`.
    */
    private function createAccountOpeningRequestsTable(): void
    {
        Schema::create('account_opening_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('request_number', 30)->unique();
            $table->enum('aof_form_type', ['individual_joint_sole', 'entity'])
                ->comment('15-page Individual/Joint/Sole Proprietor form ya 17-page Govt/Partnership/Company/NGO form');
            $table->string('aof_version', 60)->nullable()
                ->comment('BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete()->comment('Primary applicant ka CIF');
            $table->foreignUuid('account_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Approve hone par bana hua account');
            $table->date('request_date');

            /* ---- #26 / #28 Customer declarations ---- */
            $table->boolean('terms_and_conditions_accepted')->default(false);
            $table->boolean('indemnity_undertaking_accepted')->default(false);
            $table->boolean('aof_copy_received_by_customer')->default(false)
                ->comment('"I/We confirm having received the copy of the Account Opening Form along with Terms & Conditions"');
            $table->boolean('shariah_compliant_investment_authorized')->default(false)
                ->comment('Individual form: "authorize the Bank to invest the deposit in only Shariah Compliant manner"');
            $table->boolean('vernacular_form_attached')->default(false)
                ->comment('Important Note (g): customer English/Urdu ke ilawa sign kare to 100 rupee bond paper par vernacular form');
            $table->boolean('qa22_form_attached')->default(false)
                ->comment('Important Note (f): QA-22 form for foreigner residing in Pakistan');

            /* ---- #34 Recommendation (For Bank Use Only) ---- */
            $table->string('sales_staff_name_1', 150)->nullable()->comment('Name of Sale Staff/relationship Mgr (1)');
            $table->string('sales_staff_employee_no_1', 30)->nullable()->comment('Employee No. (1)');
            $table->string('sales_staff_name_2', 150)->nullable()->comment('Name of Sale Staff/relationship Mgr (2)');
            $table->string('sales_staff_employee_no_2', 30)->nullable()->comment('Employee No. (2)');
            $table->string('authorized_by_name', 150)->nullable()->comment('*Authorized by (Name)');
            $table->string('authorized_by_employee_no', 30)->nullable()->comment('Employee Number/PP.No');
            $table->date('authorized_on')->nullable();
            $table->string('checker_name', 150)->nullable()
                ->comment('*Branch Supervisor/Data Entry Checker (Ind) ya *Branch Manager/Data Entry Checker (Ent)');
            $table->string('checker_employee_no', 30)->nullable()->comment('Employee Number/PP.No');
            $table->date('checked_on')->nullable();

            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();

            $table->userTracking();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index('request_date');
        });
    }

    private function dropAccountOpeningRequestsTable(): void
    {
        Schema::dropIfExists('account_opening_requests');
    }

    // ------------------------------------------------------------------
    // Documents (was 2026_09_22_000051_create_customer_documents_table.php)
    // ------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | CUSTOMER DOCUMENTS  (checklist ke against jama shuda kaghzaat)
    |--------------------------------------------------------------------------
    | AOF SECTION SEQUENCE : #27
    | Source : AOF-Individual p.12  |  AOF-Entity p.12, p.13, p.14
    |          "Minimum Documentation to be Obtained" + Important Notes
    |
    | NOTE:
    | - `document_requirements` batati hai KYA chahiye, yeh table batati hai KYA
    |   mila. Branch staff ka checklist screen inhi dono ko join kar ke banti hai.
    | - Document customer ke sath bhi ho sakta hai aur account ke sath bhi
    |   (e.g. Board Resolution account-specific hota hai), is liye dono nullable FK.
    | - Important Note (b): "must be attested by a Gazetted officer/Nazim/
    |   Administrator or an officer of the bank after original seen".
    */
    private function createCustomerDocumentsTable(): void
    {
        Schema::create('customer_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_opening_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('document_type_id')->constrained()->restrictOnDelete();
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 200)->nullable();
            $table->string('document_number', 80)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_attested')->default(false)->comment('Important Note (b)');
            $table->string('attested_by', 150)->nullable()->comment('Gazetted officer / Nazim / Administrator / Bank officer');
            $table->enum('status', ['pending', 'received', 'verified', 'waived', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_on')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['account_id', 'status']);
            $table->index('expiry_date');
        });
    }

    private function dropCustomerDocumentsTable(): void
    {
        Schema::dropIfExists('customer_documents');
    }

    // ------------------------------------------------------------------
    // Permissions (was 2026_09_22_000060_seed_account_opening_permissions.php)
    // ------------------------------------------------------------------
    /**
     * Permissions guarding the BAJK Account Opening (AOF) module.
     *
     * Follows the same pattern as the File Management System module: branch, region
     * and division staff capture and edit account opening requests; only head-office
     * and super-admin may approve or delete them.
     */
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'view account openings',
        'create account openings',
        'edit account openings',
        'delete account openings',
        'approve account openings',
        'manage account opening references',
    ];

    private function seedAccountOpeningPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $editable = [
            'view account openings',
            'create account openings',
            'edit account openings',
        ];

        $rolePermissions = [
            'branch' => $editable,
            'region' => $editable,
            'division' => $editable,
            'head-office' => $this->permissions,
            'super-admin' => $this->permissions,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function removeAccountOpeningPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                foreach ($permission->roles as $role) {
                    $role->revokePermissionTo($permission);
                }

                $permission->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
