<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * BAJK Account Opening module ka master seeder.
 *
 * Apne project ke DatabaseSeeder mein sirf yeh line add karein:
 *     $this->call(\Database\Seeders\BajkAccountOpeningSeeder::class);
 *
 * Tarteeb ahem hai: documents ka mapping customer_categories par depend karta
 * hai, is liye DocumentReferenceSeeder aakhir mein chalta hai.
 */
class BajkAccountOpeningSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CommonReferenceSeeder::class,     // countries, currencies
            CustomerReferenceSeeder::class,   // CIF lists (dono forms ki categories)
            ProductReferenceSeeder::class,    // products, operating instructions, cards, statements
            KycReferenceSeeder::class,        // CDD + FATCA/CRS lists
            DocumentReferenceSeeder::class,   // documentation checklist (categories ke baad)
        ]);
    }
}
