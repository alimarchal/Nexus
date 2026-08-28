<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\FileCategory;
use App\Models\FileManagementSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileManagementSystem>
 */
class FileManagementSystemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'digital_id' => 'FMS-'.$this->faker->unique()->numberBetween(100000, 999999),
            'file_category_id' => FileCategory::factory(),
            'fileable_type' => 'branch',
            'fileable_id' => Branch::factory(),
            'document_date' => $this->faker->dateTimeBetween('-1 year')->format('Y-m-d'),
            'title' => $this->faker->sentence(4),
        ];
    }
}
