<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * The AKSIC case screens check "view / create / edit / delete aksics", but these
 * permissions were never created, so only super-admin could open the list.
 * Creates them (no role is given them here -- assign on Settings -> Roles).
 */
return new class extends Migration
{
    private array $permissions = ['view aksics', 'create aksics', 'edit aksics', 'delete aksics'];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
