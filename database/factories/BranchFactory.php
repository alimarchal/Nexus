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
        $regionCount = Region::count();
        if ($regionCount > 0) {
            $region = Region::skip(rand(0, $regionCount - 1))->first();
        } else {
            $region = Region::factory()->create();
        }
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
