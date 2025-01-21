<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::create(['name' => 'branch']);
        Role::create(['name' => 'region']);
        Role::create(['name' => 'division']);
        Role::create(['name' => 'head-office']);
        $superAdmin = Role::create(['name' => 'super-admin']);

        // Create super admin
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super-admin');
    }
}