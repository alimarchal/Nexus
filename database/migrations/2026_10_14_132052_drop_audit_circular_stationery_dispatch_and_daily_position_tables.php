<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Removes the Audits, Circulars, Stationery (printed stationeries and transactions), Dispatch Register and
 * Daily Positions features: their tables, the permissions that guarded them, and the activity-log and media rows
 * that belonged to their records. The code, routes and the original create-table migrations are gone too.
 *
 * WARNING: this deletes the data in those tables. Take a backup first if anything in them is still needed.
 */
return new class extends Migration
{
    /**
     * Children first, so foreign keys never block a drop.
     *
     * @var array<int, string>
     */
    private array $tables = [
        'audit_tag_pivots', 'audit_action_updates', 'audit_finding_attachments', 'audit_actions', 'audit_findings',
        'audit_item_responses', 'audit_checklist_items', 'audit_auditors', 'audit_status_histories', 'audit_scopes',
        'audit_documents', 'audit_risks', 'audit_notifications', 'audit_schedules', 'audit_metrics_caches',
        'audit_tags', 'audits', 'audit_types',
        'stationery_transactions', 'printed_stationeries', 'dispatch_registers', 'circulars', 'daily_positions',
    ];

    /**
     * @var array<int, string>
     */
    private array $models = [
        'Audit', 'AuditAction', 'AuditActionUpdate', 'AuditAuditor', 'AuditChecklistItem', 'AuditDocument', 'AuditFinding',
        'AuditFindingAttachment', 'AuditItemResponse', 'AuditMetricsCache', 'AuditNotification', 'AuditRisk', 'AuditSchedule',
        'AuditScope', 'AuditStatusHistory', 'AuditTag', 'AuditTagPivot', 'AuditType',
        'Circular', 'DailyPosition', 'DispatchRegister', 'PrintedStationery', 'StationeryTransaction',
    ];

    public function up(): void
    {
        $morphTypes = array_map(fn (string $model): string => 'App\\Models\\'.$model, $this->models);

        foreach (['activity_log' => 'subject_type', 'media' => 'model_type'] as $table => $column) {
            if (Schema::hasTable($table)) {
                DB::table($table)->whereIn($column, $morphTypes)->delete();
            }
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        $this->removePermissions();
    }

    public function down(): void
    {
        // The features were removed on purpose; their tables are not recreated.
    }

    private function removePermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::query()
            ->where(function ($query): void {
                foreach (['audits', 'circulars', 'stationeries', 'stationery transactions', 'dispatch registers', 'daily positions'] as $subject) {
                    $query->orWhere('name', 'like', '% '.$subject);
                }
            })
            ->get()
            ->each(function (Permission $permission): void {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
            });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
