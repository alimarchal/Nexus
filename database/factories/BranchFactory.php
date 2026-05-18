<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Region;
use App\Models\District;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'district_id' => District::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('??###')),
            'name' => $this->faker->streetName() . ' Branch',
            'address' => $this->faker->streetAddress(),
        ];
    }
}
