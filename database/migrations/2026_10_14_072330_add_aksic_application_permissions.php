<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The AKSIC 2025 applications (list, details, PDF), the sync with the government AKSIC system and the
 * status update that is sent to it each get their own permission, so they are no longer open to every user.
 * Whoever could view AKSIC cases may view the applications; whoever could approve AKSIC cases may sync and update status.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'view aksic applications',
        'sync aksic applications',
        'update aksic application status',
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            if ($role->name === 'super-admin') {
                $role->givePermissionTo($this->permissions);

                continue;
            }

            if ($role->hasPermissionTo('view aksics')) {
                $role->givePermissionTo('view aksic applications');
            }

            if ($role->hasPermissionTo('approve aksics')) {
                $role->givePermissionTo(['sync aksic applications', 'update aksic application status']);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
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
