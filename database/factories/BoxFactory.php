<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Box>
 */
class BoxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'box_number' => 'BOX-'.now()->year.'-'.$this->faker->unique()->numerify('###'),
            'boxable_type' => 'branch',
            'boxable_id' => Branch::factory(),
            'status' => 'open',
            'location' => $this->faker->word(),
            'file_count' => 0,
            'capacity' => 100,
            'created_by' => User::factory(),
        ];
    }
}
