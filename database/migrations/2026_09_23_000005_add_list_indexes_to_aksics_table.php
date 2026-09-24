<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the AKSIC case list so it stays fast at 10k - 1M rows:
 * branch / region scoping, status tabs, default newest-first order,
 * district and quota filters, and "starts with" name search.
 */
return new class extends Migration
{
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

    public function up(): void
    {
        Schema::table('aksics', function (Blueprint $table): void {
            foreach ($this->indexes as $name => $columns) {
                if (! Schema::hasIndex('aksics', $name)) {
                    $table->index($columns, $name);
                }
            }
        });
    }

    public function down(): void
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
