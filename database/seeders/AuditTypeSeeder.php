<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditType;

class AuditTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Internal Control Review', 'code' => 'ICR', 'description' => 'Review of internal controls', 'is_active' => true],
            ['name' => 'Financial Audit', 'code' => 'FIN', 'description' => 'Financial statements and processes', 'is_active' => true],
            ['name' => 'Operational Audit', 'code' => 'OPS', 'description' => 'Operational efficiency and adherence', 'is_active' => true],
            ['name' => 'Compliance Audit', 'code' => 'COM', 'description' => 'Regulatory and policy compliance', 'is_active' => true],
            ['name' => 'IT Security Audit', 'code' => 'ITS', 'description' => 'Information security controls', 'is_active' => true],
        ];
        foreach ($types as $t) {
            AuditType::firstOrCreate(['code' => $t['code']], $t);
        }
    }
}
