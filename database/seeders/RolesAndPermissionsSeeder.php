<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create comprehensive permissions for all modules
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Manager Management
            'view managers',
            'create managers',
            'edit managers',
            'delete managers',

            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'assign roles',
            'revoke roles',
            'assign permissions',
            'revoke permissions',

            // Complaint Management
            'view complaints',
            'create complaints',
            'edit complaints',
            'delete complaints',
            'assign complaints',
            'escalate complaints',

            // Audit Management
            'view audits',
            'create audits',
            'edit audits',
            'delete audits',
            'conduct audits',
            'review audits',

            // AKSIC Management
            'view aksics',
            'create aksics',
            'edit aksics',
            'delete aksics',
            'view aksic rules',
            'create aksic rules',
            'edit aksic rules',
            'delete aksic rules',

            // Branch Management
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Region Management
            'view regions',
            'create regions',
            'edit regions',
            'delete regions',

            // Division Management
            'view divisions',
            'create divisions',
            'edit divisions',
            'delete divisions',

            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // File Category Management (Bank FMS - shared master data for branch/region/division)
            'view file categories',
            'create file categories',
            'edit file categories',
            'delete file categories',

            // File Management System (scanned documents - branch/region/division/head-office)
            'view file management systems',
            'create file management systems',
            'edit file management systems',
            'delete file management systems',

            // Report Management
            'view reports',
            'generate reports',
            'export reports',

            // Dashboard Access
            'view dashboard',
            'view analytics',

            // Settings Management
            'view settings',
            'edit settings',
            'system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $branchRole = Role::firstOrCreate(['name' => 'branch']);
        $regionRole = Role::firstOrCreate(['name' => 'region']);
        $divisionRole = Role::firstOrCreate(['name' => 'division']);
        $headOfficeRole = Role::firstOrCreate(['name' => 'head-office']);
        $presidentOfficeRole = Role::firstOrCreate(['name' => 'president-office']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        // Define role permissions
        $rolePermissions = [
            'branch' => [
                'view users',
                'view managers',
                'view complaints',
                'create complaints',
                'edit complaints',
                'view dashboard',
                'view reports',
                'view file categories',
                'view file management systems',
                'create file management systems',
                'edit file management systems',
            ],
            'region' => [
                'view users',
                'edit users',
                'view managers',
                'view complaints',
                'create complaints',
                'edit complaints',
                'assign complaints',
                'view audits',
                'view aksics',
                'view aksic rules',
                'view branches',
                'view dashboard',
                'view reports',
                'generate reports',
                'view file categories',
                'view file management systems',
                'create file management systems',
                'edit file management systems',
            ],
            'division' => [
                'view users',
                'edit users',
                'create users',
                'view managers',
                'create managers',
                'edit managers',
                'view complaints',
                'create complaints',
                'edit complaints',
                'assign complaints',
                'escalate complaints',
                'view audits',
                'create audits',
                'edit audits',
                'view aksics',
                'create aksics',
                'edit aksics',
                'view aksic rules',
                'view branches',
                'edit branches',
                'view dashboard',
                'view reports',
                'generate reports',
                'export reports',
                'view file categories',
                'create file categories',
                'edit file categories',
                'view file management systems',
                'create file management systems',
                'edit file management systems',
            ],
            'head-office' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
                'view managers',
                'create managers',
                'edit managers',
                'delete managers',
                'view roles',
                'edit roles',
                'assign roles',
                'revoke roles',
                'view complaints',
                'create complaints',
                'edit complaints',
                'delete complaints',
                'assign complaints',
                'escalate complaints',
                'view audits',
                'create audits',
                'edit audits',
                'delete audits',
                'conduct audits',
                'review audits',
                'view aksics',
                'create aksics',
                'edit aksics',
                'delete aksics',
                'view aksic rules',
                'create aksic rules',
                'edit aksic rules',
                'delete aksic rules',
                'view branches',
                'edit branches',
                'delete branches',
                'view dashboard',
                'view analytics',
                'view reports',
                'generate reports',
                'export reports',
                'view settings',
                'edit settings',
                'view file categories',
                'create file categories',
                'edit file categories',
                'delete file categories',
                'view file management systems',
                'create file management systems',
                'edit file management systems',
                'delete file management systems',
            ],
            'super-admin' => Permission::all()->pluck('name')->toArray(),
        ];

        $presidentOfficeRole->syncPermissions([
            'view users',
            'view managers',
            'view complaints',
            'view audits',
            'view aksics',
            'view aksic rules',
            'view branches',
            'view dashboard',
            'view analytics',
            'view reports',
            'generate reports',
            'view file categories',
            'view file management systems',
        ]);

        // Assign permissions to roles
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($permissionNames);
            }
        }

        foreach (Branch::all() as $branch) {
            $new_user = User::firstOrCreate(
                ['email' => 'manager'.$branch->code.'@'.'bankajk.com'],
                [
                    'branch_id' => $branch->id,
                    'name' => 'Branch '.$branch->code.'-'.$branch->district_id.'-'.$branch->region_id,
                    'password' => Hash::make('password@999'),
                    'email_verified_at' => now(),
                ]
            );

            $new_user->assignRole('branch');
        }

        // Create super admin
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Ali Raza Marchal',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super-admin');
    }
}
