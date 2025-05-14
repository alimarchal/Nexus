<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name' => 'Leave Rules',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Rules',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Code of Conduct',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staff Advances Policy',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Forms',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Basic Credit Guide',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'HR Documents',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Operation Manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert categories
        DB::table('categories')->insert($categories);
    }
}
