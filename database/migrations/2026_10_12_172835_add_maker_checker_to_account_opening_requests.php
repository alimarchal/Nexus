<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Maker-checker for Account Opening requests: who submitted / approved / rejected,
 * the approval lock and the super-admin unlock, a per-request history of every
 * change, and the permissions that drive it.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'submit account openings',
        'unlock account openings',
    ];

    public function up(): void
    {
        Schema::table('account_opening_requests', function (Blueprint $table): void {
            $table->foreignId('submitted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->foreignId('approved_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->timestamp('locked_at')->nullable()->after('rejected_at')->comment('Set when approved: nobody can edit until a super admin unlocks it');
            $table->foreignId('unlocked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable()->after('unlocked_by');
            $table->text('unlock_reason')->nullable()->after('unlocked_at');
        });

        Schema::create('account_opening_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_opening_request_id')->constrained()->cascadeOnDelete();
            $table->string('event', 30)->comment('created, step_saved, submitted, approved, rejected, unlocked');
            $table->string('step', 30)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('changes')->nullable()->comment('{"Field label": {"old": "...", "new": "..."}}');
            $table->text('note')->nullable();
            $table->timestamp('created_at', 6)->useCurrent();

            $table->index(['account_opening_request_id', 'created_at'], 'aof_history_request_time');
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        $this->removePermissions();

        Schema::dropIfExists('account_opening_histories');

        Schema::table('account_opening_requests', function (Blueprint $table): void {
            foreach (['submitted_by', 'approved_by', 'rejected_by', 'unlocked_by'] as $column) {
                $table->dropConstrainedForeignId($column);
            }

            $table->dropColumn(['submitted_at', 'approved_at', 'rejected_at', 'locked_at', 'unlocked_at', 'unlock_reason']);
        });
    }

    private function seedPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Makers submit; only a super admin may unlock an approved form.
        foreach (['branch', 'region', 'division', 'head-office'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo('submit account openings');
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
