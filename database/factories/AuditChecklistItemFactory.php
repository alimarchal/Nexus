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
            'description' => $this->faker->paragraph(),
            'response_type' => $this->faker->randomElement(['yes_no', 'text', 'numeric', 'multiple_choice']),
            'display_order' => $this->faker->numberBetween(1, 100),
            'max_score' => $this->faker->numberBetween(5, 20),
            'is_required' => $this->faker->boolean(80), // 80% required
            'is_active' => true,
        ];
    }
}
