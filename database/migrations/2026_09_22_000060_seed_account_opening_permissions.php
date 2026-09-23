<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions guarding the BAJK Account Opening (AOF) module.
 *
 * Follows the same pattern as the File Management System module: branch, region
 * and division staff capture and edit account opening requests; only head-office
 * and super-admin may approve or delete them.
 */
return new class extends Migration
{
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

    public function up(): void
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
