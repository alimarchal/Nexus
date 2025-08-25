<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditChecklistItem>
 */
class AuditChecklistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(),
            'criteria' => $this->faker->paragraph(),
            'guidance' => $this->faker->sentence(),
            'response_type' => $this->faker->randomElement(['yes_no', 'compliant_noncompliant', 'rating', 'text', 'numeric', 'evidence']),
            'max_score' => $this->faker->numberBetween(5, 20),
            'display_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
            'is_required' => $this->faker->boolean(70), // 70% chance of being required
        ];
    }
}
