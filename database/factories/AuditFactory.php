<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Audit;
use App\Models\AuditType;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Audit>
 */
class AuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = AuditType::inRandomOrder()->first();
        if (!$type) {
            $type = AuditType::create([
                'name' => 'Seeded Type',
                'code' => 'SEED',
                'description' => 'Auto created for factory',
                'is_active' => true,
            ]);
        }
        $user = User::inRandomOrder()->first();
        return [
            'audit_type_id' => $type->id,
            'reference_no' => 'TMP-' . strtoupper($this->faker->bothify('??##')), // replaced post-create
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'scope_summary' => $this->faker->sentence(),
            'planned_start_date' => now()->addDays(rand(1, 15))->toDateString(),
            'planned_end_date' => now()->addDays(rand(16, 30))->toDateString(),
            'status' => 'planned',
            'risk_overall' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'created_by' => $user?->id ?? 1,
            'lead_auditor_id' => $user?->id,
            'auditee_user_id' => null,
            'is_template' => false,
        ];
    }
}
