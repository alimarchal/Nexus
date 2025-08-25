<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditRisk;
use App\Models\Audit;

class AuditRiskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed if there is at least one audit
        $audit = Audit::first();
        if (!$audit) {
            return; // audits will create risks later
        }
        $risks = [
            ['audit_id' => $audit->id, 'title' => 'Access Control Weakness', 'description' => 'User access not reviewed quarterly', 'likelihood' => 'medium', 'impact' => 'high', 'risk_level' => 'high'],
            ['audit_id' => $audit->id, 'title' => 'Data Backup Gaps', 'description' => 'Backups not validated for 6 months', 'likelihood' => 'low', 'impact' => 'high', 'risk_level' => 'high'],
        ];
        foreach ($risks as $r) {
            AuditRisk::create($r);
        }
    }
}
