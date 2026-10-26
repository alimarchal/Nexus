<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RegionSeeder::class,
            DistrictSeeder::class,
            BranchSeeder::class,
            ContactSeeder::class,
            RolesAndPermissionsSeeder::class,
            DivisionSeeder::class,
            HeadOfficeSeeder::class,
            ComplaintStatusTypeSeeder::class,
            ComplaintCategorySeeder::class,
            CategorySeeder::class,
            AksicBusinessCategorySeeder::class,
            BajkAccountOpeningSeeder::class,
        ]);

        // Sample Account Opening requests for testing: only when asked for in .env (SEED_ACCOUNT_OPENING_DEMO=true).
        // ACCOUNT_OPENING_DEMO_COUNT sets how many. Never run on its own, so a real database is not filled with samples.
        if (filter_var(env('SEED_ACCOUNT_OPENING_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(AccountOpeningDemoSeeder::class);
        }
    }
}
