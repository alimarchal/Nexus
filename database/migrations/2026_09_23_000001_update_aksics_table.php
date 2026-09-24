<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AKSIC cases table: account no and mortgage columns, and the indexes the case list needs for large tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addAccountNoAndMortgage();
        $this->addListIndexes();
    }

    public function down(): void
    {
        $this->dropListIndexes();
        $this->dropAccountNoAndMortgage();
    }

    // ------------------------------------------------------------------
    // Columns (was 2026_09_23_000001_add_account_no_and_mortgage_to_aksics_table.php)
    // ------------------------------------------------------------------
    /**
     * AKSIC Portal Change Requirements (demo 22-Sep-2026)
     *   #11 Account No -- bank account the loan is booked against, for branch
     *       cross-reference.
     *   #9  Mortgage   -- collateral / mortgage details recorded against the loan.
     */
    private function addAccountNoAndMortgage(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->string('account_no', 50)->nullable()->after('application_no')
                ->comment('Change #11: bank account number the loan is booked against');
            $table->text('mortgage')->nullable()->after('personal_guarantees')
                ->comment('Change #9: mortgage / collateral details');
            $table->index('account_no');
        });
    }

    private function dropAccountNoAndMortgage(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->dropIndex(['account_no']);
            $table->dropColumn(['account_no', 'mortgage']);
        });
    }

    // ------------------------------------------------------------------
    // Indexes (was 2026_09_23_000005_add_list_indexes_to_aksics_table.php)
    // ------------------------------------------------------------------
    /**
     * Indexes for the AKSIC case list so it stays fast at 10k - 1M rows:
     * branch / region scoping, status tabs, default newest-first order,
     * district and quota filters, and "starts with" name search.
     */
    /** @var array<string, array<int, string>> */
    private array $indexes = [
        'aksics_branch_id_created_at_index' => ['branch_id', 'created_at'],
        'aksics_status_created_at_index' => ['status', 'created_at'],
        'aksics_created_at_id_index' => ['created_at', 'id'],
        'aksics_district_id_index' => ['district_id'],
        'aksics_quota_index' => ['quota'],
        'aksics_name_index' => ['name'],
        'aksics_disbursement_date_index' => ['disbursement_date'],
    ];

    private function addListIndexes(): void
    {
        Schema::table('aksics', function (Blueprint $table): void {
            foreach ($this->indexes as $name => $columns) {
                if (! Schema::hasIndex('aksics', $name)) {
                    $table->index($columns, $name);
                }
            }
        });
    }

    private function dropListIndexes(): void
    {
        Schema::table('aksics', function (Blueprint $table): void {
            foreach (array_keys($this->indexes) as $name) {
                if (Schema::hasIndex('aksics', $name)) {
                    $table->dropIndex($name);
                }
            }
        });
    }
};
