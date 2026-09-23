<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions for AKSIC claim lodging (Portal Change #5).
 * Mirrors AKSIC itself: head-office lodges and settles, region/division view.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'view aksic claims',
        'lodge aksic claims',
        'settle aksic claims',
        'delete aksic claims',
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'region' => ['view aksic claims'],
            'division' => ['view aksic claims'],
            'head-office' => $this->permissions,
            'super-admin' => $this->permissions,
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
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
