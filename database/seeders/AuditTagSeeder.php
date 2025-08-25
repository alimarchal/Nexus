<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditTag;

class AuditTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'High Impact', 'slug' => 'high-impact', 'color' => '#dc2626', 'is_active' => true],
            ['name' => 'Follow Up', 'slug' => 'follow-up', 'color' => '#2563eb', 'is_active' => true],
            ['name' => 'Quick Win', 'slug' => 'quick-win', 'color' => '#16a34a', 'is_active' => true],
            ['name' => 'Documentation', 'slug' => 'documentation', 'color' => '#6b7280', 'is_active' => true],
        ];
        foreach ($tags as $t) {
            AuditTag::firstOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
