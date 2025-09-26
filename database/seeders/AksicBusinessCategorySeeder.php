<?php

namespace Database\Seeders;

use App\Models\AksicBusinessCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AksicBusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // IT & Digital Sector
            ['id' => 1, 'parent_id' => 0, 'name' => 'IT & Digital Sector'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Information Technology (by professionals)'],
            ['id' => 3, 'parent_id' => 1, 'name' => 'E-Commerce'],
            ['id' => 4, 'parent_id' => 1, 'name' => 'Digital Marketing'],
            ['id' => 5, 'parent_id' => 1, 'name' => 'Software Houses'],
            ['id' => 6, 'parent_id' => 1, 'name' => 'Call Centres'],
            ['id' => 7, 'parent_id' => 1, 'name' => 'TEVT Sector'],

            // Handicrafts & Cottage Industries
            ['id' => 8, 'parent_id' => 0, 'name' => 'Handicrafts & Cottage Industries'],
            ['id' => 9, 'parent_id' => 8, 'name' => 'Hand knotted carpet weaving'],
            ['id' => 10, 'parent_id' => 8, 'name' => 'Walnut wood carving'],
            ['id' => 11, 'parent_id' => 8, 'name' => 'Kashmiri Shawls & Embroidery'],
            ['id' => 12, 'parent_id' => 8, 'name' => 'Papier Machie'],
            ['id' => 13, 'parent_id' => 8, 'name' => 'Chain-stitch, Numda & Gabba'],
            ['id' => 14, 'parent_id' => 8, 'name' => 'Kangi Making'],
            ['id' => 15, 'parent_id' => 8, 'name' => 'Phiren & Embroidered Dresses'],

            // Agriculture & Livestock
            ['id' => 16, 'parent_id' => 0, 'name' => 'Agriculture & Livestock'],
            ['id' => 17, 'parent_id' => 16, 'name' => 'Agri-Businesses'],
            ['id' => 18, 'parent_id' => 16, 'name' => 'Livestock, Dairy & Poultry Farming'],
            ['id' => 19, 'parent_id' => 16, 'name' => 'Horticulture & Rose Farming'],
            ['id' => 20, 'parent_id' => 16, 'name' => 'Olive Plantation'],
            ['id' => 21, 'parent_id' => 16, 'name' => 'Off-season Vegetable Farming'],

            // Tourism & Hospitality
            ['id' => 22, 'parent_id' => 0, 'name' => 'Tourism & Hospitality'],
            ['id' => 23, 'parent_id' => 22, 'name' => 'Traditional Food Courts'],
            ['id' => 24, 'parent_id' => 22, 'name' => 'Paying Guest Units'],
            ['id' => 25, 'parent_id' => 22, 'name' => 'Hotels & Restaurants'],

            // SMEs & Local Industry
            ['id' => 26, 'parent_id' => 0, 'name' => 'SMEs & Local Industry'],
            ['id' => 27, 'parent_id' => 26, 'name' => 'Silk Rearing & Processing'],
            ['id' => 28, 'parent_id' => 26, 'name' => 'Gem Stone Cutting'],
            ['id' => 29, 'parent_id' => 26, 'name' => 'Stone Carving & Crushing'],
            ['id' => 30, 'parent_id' => 26, 'name' => 'Sports Goods Manufacturing'],
            ['id' => 31, 'parent_id' => 26, 'name' => 'Light Engineering Sector'],
            ['id' => 32, 'parent_id' => 26, 'name' => 'Auto Repair & Workshops'],

            // Women Entrepreneurs & Misc.
            ['id' => 33, 'parent_id' => 0, 'name' => 'Women Entrepreneurs & Misc.'],
            ['id' => 34, 'parent_id' => 33, 'name' => 'Boutiques & Tailoring'],
            ['id' => 35, 'parent_id' => 33, 'name' => 'Home Baking Units'],
            ['id' => 36, 'parent_id' => 33, 'name' => 'Pickle, Jam & Jelly Making'],
            ['id' => 37, 'parent_id' => 33, 'name' => 'Garments / Stitching Units'],
            ['id' => 38, 'parent_id' => 33, 'name' => 'Mineral Water Plants'],
            ['id' => 39, 'parent_id' => 33, 'name' => 'Soap & Detergent Production'],
            ['id' => 40, 'parent_id' => 33, 'name' => 'New Innovations'],
        ];

        foreach ($categories as $category) {
            AksicBusinessCategory::create([
                'id' => $category['id'],
                'parent_id' => $category['parent_id'],
                'name' => $category['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}