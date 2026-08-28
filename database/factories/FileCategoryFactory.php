<?php

namespace Database\Factories;

use App\Models\FileCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileCategory>
 */
class FileCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_code' => 'FC-'.$this->faker->unique()->numberBetween(100, 999),
            'category_name' => $this->faker->unique()->words(3, true),
            'is_active' => '1',
        ];
    }
}
