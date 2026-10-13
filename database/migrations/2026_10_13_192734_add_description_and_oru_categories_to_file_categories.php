<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The 20 official "Categories of Branch Records to Be Stored in the Old Record Unit (ORU)".
     *
     * @var array<string, array{name: string, description: string}>
     */
    private array $oruCategories = [
        'FC-001' => ['name' => 'Account Opening Records', 'description' => 'Account Opening Forms (AOFs), specimen signature cards, KYC/CDD documents, mandates, authorized signatory records and account closure documents.'],
        'FC-002' => ['name' => 'Cash Transaction Records', 'description' => 'Cash payment and receipt vouchers, cash scrolls, teller sheets, daily cash balancing statements and cash difference reports.'],
        'FC-003' => ['name' => 'Cheque and Payment Records', 'description' => 'Paid cheques, debit/credit vouchers, payment instruments, stop-payment instructions and related supporting documents.'],
        'FC-004' => ['name' => 'Clearing Records', 'description' => 'Inward and outward clearing registers, clearing instruments, returned cheques and clearing reconciliation statements.'],
        'FC-005' => ['name' => 'Remittance and Funds Transfer Records', 'description' => 'Domestic and international remittance documents, funds transfer instructions, payment confirmations and supporting vouchers.'],
        'FC-006' => ['name' => 'Deposit Account Records', 'description' => 'Term deposit applications, deposit receipts, maturity instructions, premature encashment records and related correspondence.'],
        'FC-007' => ['name' => 'General Ledger and Accounting Records', 'description' => 'General ledger extracts, subsidiary ledgers, journal vouchers, day books, trial balances and accounting statements.'],
        'FC-008' => ['name' => 'Reconciliation and Suspense Accounts', 'description' => 'Inter-branch reconciliation statements, suspense account records, outstanding entries and adjustment documentation.'],
        'FC-009' => ['name' => 'Advances and Credit Records', 'description' => 'Loan applications, sanction letters, financing agreements, security documents, guarantees, mortgage records, recovery proceedings and settlement documents.'],
        'FC-010' => ['name' => 'Collateral and Securities Records', 'description' => "Original or controlled security documents, title deeds, charge documents and other collateral records, subject to the Bank's secure custody arrangements."],
        'FC-011' => ['name' => 'AML/CFT and Compliance Records', 'description' => 'Customer due diligence records, compliance review documentation and transaction-monitoring records, subject to applicable retention and confidentiality requirements.'],
        'FC-012' => ['name' => 'Audit and Inspection Records', 'description' => 'Internal and external audit reports, SBP inspection observations, audit replies, compliance reports and rectification evidence.'],
        'FC-013' => ['name' => 'Fraud, Investigation and Legal Records', 'description' => 'FIRs, investigation reports, fraud-related correspondence, insurance claims, legal notices, court orders and litigation files.'],
        'FC-014' => ['name' => 'Cheque Book and Security Stationery Records', 'description' => 'Cheque-book issuance registers, stock registers, security stationery records, damaged cheque-book records and destruction certificates.'],
        'FC-015' => ['name' => 'Cash Management Records', 'description' => 'Cash remittance records, cash transfer documents, cash-in-transit records, cash insurance-related documents and cash-limit approvals.'],
        'FC-016' => ['name' => 'Regulatory and Tax Records', 'description' => 'SBP returns, FBR notices and replies, tax-related documents, statutory statements and related correspondence.'],
        'FC-017' => ['name' => 'Branch Administrative Records', 'description' => 'Branch operational correspondence, inspection visit reports, handover/takeover documents, operational approvals and control registers.'],
        'FC-018' => ['name' => 'Customer Complaints and Claims', 'description' => 'Customer complaints, disputed transactions, claim applications, investigation findings and final settlement correspondence.'],
        'FC-019' => ['name' => 'Dormant and Unclaimed Account Records', 'description' => 'Dormant account documentation, unclaimed deposit records, customer tracing correspondence and related regulatory submissions.'],
        'FC-020' => ['name' => 'Fixed Assets and Other Registers', 'description' => "Fixed asset registers, asset disposal records, branch inventory records and other operational registers required under the Bank's policy."],
    ];

    /**
     * Old default category name => the ORU category its files move to.
     *
     * @var array<string, string>
     */
    private array $oldDefaultsToNew = [
        'Account Opening Forms (KYC)' => 'FC-001',
        'Cheque Books & Requisitions' => 'FC-014',
        'Loan & Credit Documents' => 'FC-009',
        'Fixed Deposit Receipts' => 'FC-006',
        'Correspondence & Letters' => 'FC-017',
        'Circulars & Notifications' => 'FC-017',
        'Audit Reports' => 'FC-012',
        'Legal & Litigation Documents' => 'FC-013',
        'HR & Personnel Files' => 'FC-017',
        'Inspection & Compliance Reports' => 'FC-012',
    ];

    public function up(): void
    {
        Schema::table('file_categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category_name');
        });

        $now = now();

        // 1. Free the FC-xxx codes: park every old default under a temporary code and
        //    park any other row that holds an ORU code, so the new rows can take them.
        $oldDefaults = DB::table('file_categories')->whereIn('category_name', array_keys($this->oldDefaultsToNew))->get();
        $parked = [];

        foreach ($oldDefaults as $position => $old) {
            $parked[$old->id] = $this->oldDefaultsToNew[$old->category_name];
            DB::table('file_categories')->where('id', $old->id)->update(['category_code' => 'OLD-'.($position + 1)]);
        }

        foreach (DB::table('file_categories')->whereIn('category_code', array_keys($this->oruCategories))->get() as $position => $custom) {
            DB::table('file_categories')->where('id', $custom->id)->update(['category_code' => 'X-'.$custom->category_code.'-'.($position + 1)]);
        }

        // 2. Create the 20 ORU categories.
        $newIds = [];

        foreach ($this->oruCategories as $code => $category) {
            $newIds[$code] = (string) Str::uuid();
            DB::table('file_categories')->insert([
                'id' => $newIds[$code],
                'category_code' => $code,
                'category_name' => $category['name'],
                'description' => $category['description'],
                'is_active' => '1',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Move the files of each old category to the matching ORU category, then drop the old rows.
        foreach ($parked as $oldId => $newCode) {
            DB::table('file_management_systems')->where('file_category_id', $oldId)->update(['file_category_id' => $newIds[$newCode]]);
            DB::table('file_categories')->where('id', $oldId)->delete();
        }
    }

    public function down(): void
    {
        Schema::table('file_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
