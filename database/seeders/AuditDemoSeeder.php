<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{AuditType, Audit, AuditScope, AuditChecklistItem, AuditItemResponse, AuditFinding, AuditAction, AuditActionUpdate, AuditRisk, AuditTag, AuditMetricsCache, AuditStatusHistory, User};

class AuditDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $type = AuditType::first() ?? AuditType::create(['name' => 'Demo Type', 'code' => 'DEMO', 'is_active' => true]);

        // Checklist items
        if (AuditChecklistItem::where('audit_type_id', $type->id)->count() === 0) {
            foreach ([
                ['reference_code' => 'DEM-01', 'title' => 'Policies approved', 'response_type' => 'yes_no', 'display_order' => 1],
                ['reference_code' => 'DEM-02', 'title' => 'Access review', 'response_type' => 'yes_no', 'display_order' => 2],
            ] as $ci) {
                AuditChecklistItem::create(array_merge($ci, ['audit_type_id' => $type->id, 'is_active' => true]));
            }
        }

        // Audit
        $audit = Audit::create([
            'audit_type_id' => $type->id,
            'reference_no' => generateUniqueId('audit', 'audits', 'reference_no'),
            'title' => 'Demo Seeded Audit',
            'status' => 'planned',
            'risk_overall' => 'medium',
            'created_by' => $user->id,
            'lead_auditor_id' => $user->id,
            'is_template' => false,
        ]);

        // Scope
        AuditScope::create(['audit_id' => $audit->id, 'scope_item' => 'Finance', 'description' => 'Finance Dept']);

        // Responses & score
        $scoreTotal = 0;
        $scoreMax = 0;
        foreach (AuditChecklistItem::where('audit_type_id', $type->id)->get() as $item) {
            $score = 5;
            $scoreTotal += $score;
            $scoreMax += 10;
            AuditItemResponse::create([
                'audit_id' => $audit->id,
                'audit_checklist_item_id' => $item->id,
                'responded_by' => $user->id,
                'response_value' => 'yes',
                'score' => $score,
                'responded_at' => now(),
            ]);
        }
        if ($scoreMax > 0) {
            $audit->score = round($scoreTotal / $scoreMax * 100, 2);
            $audit->save();
        }

        // Finding, action & update
        $finding = AuditFinding::create([
            'audit_id' => $audit->id,
            'reference_no' => generateUniqueId('afd', 'audit_findings', 'reference_no'),
            'category' => 'process',
            'severity' => 'high',
            'status' => 'open',
            'title' => 'Demo Finding',
            'created_by' => $user->id
        ]);
        $action = AuditAction::create([
            'audit_id' => $audit->id,
            'audit_finding_id' => $finding->id,
            'reference_no' => generateUniqueId('act', 'audit_actions', 'reference_no'),
            'title' => 'Remediate',
            'status' => 'open',
            'priority' => 'medium',
            'created_by' => $user->id
        ]);
        AuditActionUpdate::create(['audit_action_id' => $action->id, 'created_by' => $user->id, 'update_text' => 'Planned remediation', 'is_system_generated' => false]);

        // Risk
        AuditRisk::create(['audit_id' => $audit->id, 'title' => 'Seed Risk', 'likelihood' => 'medium', 'impact' => 'medium', 'risk_level' => 'medium', 'status' => 'identified', 'created_by' => $user->id]);

        // Tag
        $tag = AuditTag::firstOrCreate(['slug' => 'seed-demo'], ['name' => 'Seed Demo', 'color' => '#2563eb', 'is_active' => true]);
        $audit->tags()->syncWithoutDetaching($tag->id);

        // Metrics
        foreach (['findings_total' => 1, 'actions_open' => 1, 'risks_total' => 1] as $k => $v) {
            AuditMetricsCache::updateOrCreate(['audit_id' => $audit->id, 'metric_key' => $k], ['numeric_value' => $v, 'metric_value' => $v, 'calculated_at' => now(), 'ttl_seconds' => 3600]);
        }

        // Status history
        AuditStatusHistory::create([
            'auditable_type' => Audit::class,
            'auditable_id' => $audit->id,
            'from_status' => null,
            'to_status' => 'planned',
            'changed_by' => $user->id,
            'changed_at' => now(),
        ]);
    }
}
