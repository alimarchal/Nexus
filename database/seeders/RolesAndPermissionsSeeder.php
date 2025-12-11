<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create comprehensive permissions for all modules
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

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
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        // Define role permissions
        $rolePermissions = [
            'branch' => [
                'view users',
                'view complaints',
                'create complaints',
                'edit complaints',
                'view dashboard',
                'view reports'
            ],
            'region' => [
                'view users',
                'edit users',
                'view complaints',
                'create complaints',
                'edit complaints',
                'assign complaints',
                'view audits',
                'view branches',
                'view dashboard',
                'view reports',
                'generate reports'
            ],
            'division' => [
                'view users',
                'edit users',
                'create users',
                'view complaints',
                'create complaints',
                'edit complaints',
                'assign complaints',
                'escalate complaints',
                'view audits',
                'create audits',
                'edit audits',
                'view branches',
                'edit branches',
                'view dashboard',
                'view reports',
                'generate reports',
                'export reports'
            ],
            'head-office' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
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
                'view branches',
                'create branches',
                'edit branches',
                'delete branches',
                'view dashboard',
                'view analytics',
                'view reports',
                'generate reports',
                'export reports',
                'view settings',
                'edit settings'
            ],
            'super-admin' => Permission::all()->pluck('name')->toArray()
        ];

        // Assign permissions to roles
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($permissionNames);
            }
        }

        foreach (Branch::all() as $branch) {
            $new_user = User::create([
                'branch_id' => $branch->id,
                'name' => 'Branch ' . $branch->code . '-' . $branch->district_id . '-' . $branch->region_id,
                'email' => 'manager' . $branch->code . '@' . 'bankajk.com',
                'password' => Hash::make('password@999'),
                'email_verified_at' => now(),
            ]);

            $new_user->assignRole('branch');
        }

        // Create super admin
        $user = User::create([
            'name' => 'Ali Raza Marchal',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super-admin');
    }
}
