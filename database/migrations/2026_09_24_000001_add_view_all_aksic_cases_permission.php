<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * AKSIC case visibility by permission:
 *
 *   "view all aksic cases" -> every branch's cases (list, case pages, dashboard, export)
 *   without it             -> the user's own office only (branch, or the branches of their region)
 *
 * Given to super-admin, head-office and division (who saw every branch before),
 * and "view aksics" is given to every office role so branch / region staff can
 * open their own cases. Change either on Settings -> Roles.
 */
return new class extends Migration
{
    private const VIEW_ALL = 'view all aksic cases';

    /** @var array<int, string> */
    private array $viewAllRoles = ['super-admin', 'head-office', 'division'];

    /** @var array<int, string> */
    private array $viewRoles = ['super-admin', 'head-office', 'division', 'region', 'branch'];

    public function up(): void
    {
        $viewAll = Permission::findOrCreate(self::VIEW_ALL, 'web');
        $view = Permission::findOrCreate('view aksics', 'web');

        Role::query()->where('guard_name', 'web')->whereIn('name', $this->viewAllRoles)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($viewAll));
        Role::query()->where('guard_name', 'web')->whereIn('name', $this->viewRoles)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($view));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()->where('name', self::VIEW_ALL)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
