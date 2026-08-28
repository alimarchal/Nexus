<?php

namespace Database\Factories;

use App\Models\HeadOffice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeadOffice>
 */
class HeadOfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city().' Head Office',
        ];
    }
}
