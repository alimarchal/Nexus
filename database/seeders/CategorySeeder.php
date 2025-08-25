<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Forms',
            'Staff Advances',
            'Staff Advances Policy',
            'Code Of Conduct',
            'Rules',
            'Leave Rules',
            "Operation's Manual",
            'Basic Credit Guide',
        ];

        foreach ($categories as $name) {
            \Illuminate\Support\Facades\DB::table('categories')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
