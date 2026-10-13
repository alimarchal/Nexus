<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Maker-checker for File Management records: who submitted / approved / rejected a record, the approval lock and
 * the per-record super-admin unlock, plus the permissions that drive it. Records that exist today are treated as
 * already approved and are not locked, so nothing that works now stops working.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'submit file management systems',
        'approve file management systems',
        'unlock file management systems',
    ];

    public function up(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table): void {
            $table->string('status', 20)->default('draft')->after('title')->index()
                ->comment('draft, submitted, approved, rejected');
            $table->foreignId('submitted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->foreignId('approved_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->timestamp('locked_at')->nullable()->after('rejection_reason')->comment('Set when approved: nobody can edit until a super admin unlocks it');
            $table->foreignId('unlocked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable()->after('unlocked_by');
            $table->text('unlock_reason')->nullable()->after('unlocked_at');
        });

        DB::table('file_management_systems')->update(['status' => 'approved']);

        $this->seedPermissions();
    }

    public function down(): void
    {
        $this->removePermissions();

        Schema::table('file_management_systems', function (Blueprint $table): void {
            foreach (['submitted_by', 'approved_by', 'rejected_by', 'unlocked_by'] as $column) {
                $table->dropConstrainedForeignId($column);
            }

            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'submitted_at', 'approved_at', 'rejected_at', 'rejection_reason', 'locked_at', 'unlocked_at', 'unlock_reason']);
        });
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Office users make and check each other's records; only a super admin may unlock an approved one.
        foreach (['branch', 'region', 'division', 'head-office'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo(['submit file management systems', 'approve file management systems']);
        }

        Role::where('name', 'super-admin')->first()?->givePermissionTo($this->permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function removePermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name) {
            $permission = Permission::where('name', $name)->first();

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
