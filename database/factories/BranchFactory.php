<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        // Create or get existing region and district for proper relationship
        $region = Region::inRandomOrder()->first() ?: Region::factory()->create();
        $district = District::where('region_id', $region->id)->inRandomOrder()->first() 
                   ?: District::factory()->create(['region_id' => $region->id]);
        
        return [
            'region_id' => $region->id,
            'district_id' => $district->id,
            'code' => strtoupper($this->faker->unique()->lexify('??###')),
            'name' => $this->faker->streetName() . ' Branch',
            'address' => $this->faker->streetAddress(),
        ];
    }
}
