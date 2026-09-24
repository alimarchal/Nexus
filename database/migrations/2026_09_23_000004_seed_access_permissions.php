<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Access permissions (grants go to roles that already use each module, so nobody loses access):
 *   - AKSIC case permissions (view / create / edit / delete aksics)
 *   - "view all aksic cases": every branch instead of own branch / region
 *   - one dashboard permission per section (AKSIC, file management, account opening)
 *   - "export file management systems": CSV and ZIP export of the file list
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->seedAksicCasePermissions();
        $this->seedViewAllAksicCases();
        $this->seedDashboardSectionPermissions();
        $this->seedFileExportPermission();
    }

    public function down(): void
    {
        $this->removeFileExportPermission();
        $this->removeDashboardSectionPermissions();
        $this->removeViewAllAksicCases();
        $this->removeAksicCasePermissions();
    }

    // ------------------------------------------------------------------
    // AksicCases (was 2026_09_23_000006_seed_aksic_case_permissions.php)
    // ------------------------------------------------------------------
    /**
     * The AKSIC case screens check "view / create / edit / delete aksics", but these
     * permissions were never created, so only super-admin could open the list.
     * Creates them (no role is given them here -- assign on Settings -> Roles).
     */
    private array $permissions = ['view aksics', 'create aksics', 'edit aksics', 'delete aksics'];

    private function seedAksicCasePermissions(): void
    {
        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removeAksicCasePermissions(): void
    {
        Permission::query()->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ------------------------------------------------------------------
    // ViewAll (was 2026_09_24_000001_add_view_all_aksic_cases_permission.php)
    // ------------------------------------------------------------------
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
    private const VIEW_ALL = 'view all aksic cases';

    /** @var array<int, string> */
    private array $viewAllRoles = ['super-admin', 'head-office', 'division'];

    /** @var array<int, string> */
    private array $viewRoles = ['super-admin', 'head-office', 'division', 'region', 'branch'];

    private function seedViewAllAksicCases(): void
    {
        $viewAll = Permission::findOrCreate(self::VIEW_ALL, 'web');
        $view = Permission::findOrCreate('view aksics', 'web');

        Role::query()->where('guard_name', 'web')->whereIn('name', $this->viewAllRoles)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($viewAll));
        Role::query()->where('guard_name', 'web')->whereIn('name', $this->viewRoles)->get()
            ->each(fn (Role $role) => $role->givePermissionTo($view));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removeViewAllAksicCases(): void
    {
        Permission::query()->where('name', self::VIEW_ALL)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ------------------------------------------------------------------
    // Dashboard (was 2026_09_24_000002_add_dashboard_section_permissions.php)
    // ------------------------------------------------------------------
    /**
     * One permission per dashboard section, so each section can be shown or hidden
     * per role on Settings -> Roles. A section appears only when the user has BOTH
     * its dashboard permission and the module's view permission.
     *
     * Each dashboard permission is given to the roles that can already view that
     * module, so nothing disappears from anyone's dashboard after migrating.
     */
    /** @var array<string, string> dashboard permission => module view permission */
    private array $sections = [
        'view aksic dashboard' => 'view aksics',
        'view file management dashboard' => 'view file management systems',
        'view account opening dashboard' => 'view account openings',
    ];

    private function seedDashboardSectionPermissions(): void
    {
        foreach ($this->sections as $dashboard => $module) {
            $permission = Permission::findOrCreate($dashboard, 'web');

            Role::query()->where('guard_name', 'web')
                ->whereHas('permissions', fn ($q) => $q->where('name', $module))
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removeDashboardSectionPermissions(): void
    {
        Permission::query()->whereIn('name', array_keys($this->sections))->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ------------------------------------------------------------------
    // Export (was 2026_09_24_000003_add_export_file_management_permission.php)
    // ------------------------------------------------------------------
    /**
     * "export file management systems": download the filtered file list as CSV
     * and as a ZIP of all scanned pages. Given to the roles that can already view
     * files; remove it on Settings -> Roles from any role that should not bulk
     * download documents.
     */
    private const PERMISSION = 'export file management systems';

    private function seedFileExportPermission(): void
    {
        $permission = Permission::findOrCreate(self::PERMISSION, 'web');

        Role::query()->where('guard_name', 'web')
            ->whereHas('permissions', fn ($q) => $q->where('name', 'view file management systems'))
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removeFileExportPermission(): void
    {
        Permission::query()->where('name', self::PERMISSION)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
