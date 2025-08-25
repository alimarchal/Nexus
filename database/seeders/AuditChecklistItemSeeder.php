<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditChecklistItem;
use App\Models\AuditType;

class AuditChecklistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = AuditType::first();
        if (!$type)
            return;
        $items = [
            [
                'audit_type_id' => $type->id,
                'reference_code' => 'GOV-01',
                'title' => 'Documented policies exist and are approved',
                'criteria' => 'Policies approved within last 12 months',
                'guidance' => 'Check latest approval dates',
                'response_type' => 'yes_no',
                'display_order' => 1,
                'is_active' => true
            ],
            [
                'audit_type_id' => $type->id,
                'reference_code' => 'SEC-02',
                'title' => 'User access reviews performed quarterly',
                'criteria' => 'Quarterly review evidence exists',
                'guidance' => 'Sample 5 users',
                'response_type' => 'yes_no',
                'display_order' => 2,
                'is_active' => true
            ],
            [
                'audit_type_id' => $type->id,
                'reference_code' => 'OPS-03',
                'title' => 'Change management logs maintained',
                'criteria' => 'All changes logged with approvals',
                'guidance' => 'Inspect change tickets',
                'response_type' => 'yes_no',
                'display_order' => 3,
                'is_active' => true
            ],
        ];
        foreach ($items as $i) {
            AuditChecklistItem::firstOrCreate([
                'audit_type_id' => $i['audit_type_id'],
                'title' => $i['title']
            ], $i);
        }
    }
}
