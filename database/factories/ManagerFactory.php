<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manager>
 */
class ManagerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'division_id' => Division::factory(),
            'manager_user_id' => User::factory(),
            'title' => $this->faker->jobTitle(),
            'created_by_user_id' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
