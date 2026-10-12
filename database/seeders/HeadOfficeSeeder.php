<?php

namespace Database\Seeders;

use App\Models\HeadOffice;
use Illuminate\Database\Seeder;

class HeadOfficeSeeder extends Seeder
{
    /**
     * Seed the head office that file transfers and head-office users point to.
     * Safe to run repeatedly: an existing "Head Office" row is left untouched.
     */
    public function run(): void
    {
        HeadOffice::firstOrCreate(['name' => 'Head Office']);
    }
}
