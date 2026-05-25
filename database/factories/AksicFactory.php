<?php

namespace Database\Factories;

use App\Models\Aksic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aksic>
 */
class AksicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'cnic' => fake()->unique()->numerify('#####-#######-#'),
            'application_no' => fake()->unique()->bothify('AKSIC-#####'),
            'phone' => fake()->phoneNumber(),
            'business_name' => fake()->company(),
            'business_type' => fake()->randomElement(['Existing', 'New']),
            'is_startup_business' => false,
            'status' => 'Pending',
            'principal_amount' => 1000000,
            'tenure' => 60,
            'disbursement_date' => '2026-05-11',
            'kibor_rate' => 12.00,
            'spread_rate' => 2.04,
            'total_rate' => 14.04,
        ];
    }
}
