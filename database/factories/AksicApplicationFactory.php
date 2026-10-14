<?php

namespace Database\Factories;

use App\Models\AksicApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AksicApplication>
 */
class AksicApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => $this->faker->unique()->numberBetween(1000, 99999),
            'name' => $this->faker->name(),
            'fatherName' => $this->faker->name('male'),
            'cnic' => $this->faker->unique()->numerify('#############'),
            'application_no' => 'APP-'.$this->faker->unique()->numberBetween(100000, 999999),
            'fee_status' => 'unpaid',
            'status' => 'Pending',
        ];
    }
}
