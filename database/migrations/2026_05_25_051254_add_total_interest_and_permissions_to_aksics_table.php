<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->ensureNoDuplicates('cnic');
        $this->ensureNoDuplicates('application_no');

        Schema::table('aksics', function (Blueprint $table) {
            if (! Schema::hasColumn('aksics', 'total_interest')) {
                $table->decimal('total_interest', 20, 6)->nullable()->after('total_rate');
            }

            if (! Schema::hasColumn('aksics', 'consent_entry')) {
                $table->enum('consent_entry', ['Yes', 'No'])->nullable()->after('site_visit_date');
            }

            if (! Schema::hasColumn('aksics', 'consent_date')) {
                $table->date('consent_date')->nullable()->after('consent_entry');
            }

            if (! Schema::hasColumn('aksics', 'liquid_security')) {
                $table->text('liquid_security')->nullable()->after('consent_date');
            }

            if (! Schema::hasColumn('aksics', 'personal_guarantees')) {
                $table->text('personal_guarantees')->nullable()->after('liquid_security');
            }

            if (Schema::hasColumn('aksics', 'sanction_date')) {
                $table->dropColumn('sanction_date');
            }

            if (! $this->hasIndex('aksics_cnic_unique')) {
                $table->unique('cnic', 'aksics_cnic_unique');
            }

            if (! $this->hasIndex('aksics_application_no_unique')) {
                $table->unique('application_no', 'aksics_application_no_unique');
            }
        });

        $this->allowRejectStatus();
        $this->syncPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            if (! Schema::hasColumn('aksics', 'sanction_date')) {
                $table->date('sanction_date')->nullable()->after('disbursement_date');
            }

            foreach (['personal_guarantees', 'liquid_security', 'consent_date', 'consent_entry'] as $column) {
                if (Schema::hasColumn('aksics', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('aksics', 'total_interest')) {
                $table->dropColumn('total_interest');
            }
        });

        $this->revertRejectStatus();
    }

    private function ensureNoDuplicates(string $column): void
    {
        $duplicate = DB::table('aksics')
            ->select($column, DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->having('aggregate', '>', 1)
            ->first();

        if ($duplicate) {
            throw new RuntimeException("Cannot add unique index to aksics.{$column}; duplicate value exists: {$duplicate->{$column}}.");
        }
    }

    private function hasIndex(string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('aksics')"))
                ->contains(fn (object $index): bool => $index->name === $indexName);
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            return collect(DB::select('SHOW INDEX FROM aksics'))
                ->contains(fn (object $index): bool => $index->Key_name === $indexName);
        }

        return collect(Schema::getIndexes('aksics'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $indexName);
    }

    private function syncPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(['approve aksics', 'import aksics'])
            ->map(fn (string $permission): Permission => Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));

        Role::query()
            ->whereIn('name', ['super-admin', 'head-office'])
            ->orWhere('name', 'like', '%aksic%')
            ->get()
            ->each(fn (Role $role): Role => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function allowRejectStatus(): void
    {
        if (! Schema::hasColumn('aksics', 'status') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE aksics MODIFY status ENUM('Pending', 'Approved', 'Reject') NOT NULL DEFAULT 'Pending'");
    }

    private function revertRejectStatus(): void
    {
        if (! Schema::hasColumn('aksics', 'status') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('aksics')
            ->where('status', 'Reject')
            ->update(['status' => 'Pending']);

        DB::statement("ALTER TABLE aksics MODIFY status ENUM('Pending', 'Approved') NOT NULL DEFAULT 'Pending'");
    }
};
