<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\District;
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


        // foreach(Branch::all() as $branch) {
        //     $new_user = User::create([
        //         'branch_id' => $branch->id,
        //         'name' => 'Branch ' . $branch->code . '-' . $branch->district_id . '-' . $branch->region_id,
        //         'email' => 'manager' . $branch->code . '@' . 'bankajk.com',
        //         'password' => Hash::make('password'),
        //         'email_verified_at' => now(),
        //     ]);

        //     $new_user->assignRole('branch');
        // }

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
