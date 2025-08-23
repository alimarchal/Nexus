<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Audit, AuditType, AuditTag, User, AuditStatusHistory};

class AuditSampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $types = AuditType::all();
        if ($types->isEmpty()) {
            $types = collect([
                AuditType::create(['name' => 'Financial Audit', 'code' => 'FIN', 'is_active' => true]),
                AuditType::create(['name' => 'Operational Audit', 'code' => 'OPS', 'is_active' => true]),
            ]);
        }
        $tags = AuditTag::all();
        if ($tags->isEmpty()) {
            $tags = collect([
                AuditTag::create(['name' => 'High Impact', 'slug' => 'high-impact', 'color' => '#dc2626', 'is_active' => true]),
                AuditTag::create(['name' => 'Quick Win', 'slug' => 'quick-win', 'color' => '#16a34a', 'is_active' => true]),
            ]);
        }
        $statuses = ['planned', 'in_progress', 'reporting', 'issued', 'closed'];
        $risks = ['low', 'medium', 'high', 'critical'];

        // Avoid creating duplicates on repeated seeding (idempotent by reference_no uniqueness attempt)
        $already = Audit::count();
        $target = 10;
        if ($already >= $target) {
            return;
        }

        for ($i = $already; $i < $target; $i++) {
            $type = $types->random();
            $status = $statuses[$i % count($statuses)];
            $risk = $risks[$i % count($risks)];
            $audit = Audit::create([
                'audit_type_id' => $type->id,
                'reference_no' => generateUniqueId('audit', 'audits', 'reference_no'),
                'title' => ucfirst($risk) . ' ' . $type->name . ' Cycle ' . ($i + 1),
                'status' => $status,
                'risk_overall' => $risk,
                'created_by' => $user->id,
                'lead_auditor_id' => $user->id,
                'planned_start_date' => now()->subDays(rand(10, 40)),
                'planned_end_date' => now()->subDays(rand(1, 9)),
                'actual_start_date' => $status !== 'planned' ? now()->subDays(rand(8, 20)) : null,
                'actual_end_date' => in_array($status, ['issued', 'closed']) ? now()->subDays(rand(0, 5)) : null,
                'score' => in_array($status, ['reporting', 'issued', 'closed']) ? rand(60, 95) : null,
                'is_template' => false,
            ]);
            // Tag assignment
            $audit->tags()->syncWithoutDetaching($tags->random()->id);

            // Minimal status history
            AuditStatusHistory::create([
                'auditable_type' => Audit::class,
                'auditable_id' => $audit->id,
                'from_status' => null,
                'to_status' => $status,
                'changed_by' => $user->id,
                'changed_at' => now()->subMinutes(rand(1, 500)),
            ]);
        }
    }
}
