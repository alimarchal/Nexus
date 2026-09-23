<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Countries + Currencies.
 *
 * NOTE: Currencies bilkul AOF ke "Type of Account Foreign Currency" box se
 * hain ($USD, GBP, AED, SAR, Euro) + PKR.
 * Countries sirf wo daale gaye hain jo forms mein explicitly aate hain
 * (Pakistan, USA -- FATCA, aur FCY wale mulk). Poori ISO list baad mein add
 * ki ja sakti hai; seeder idempotent hai (code par upsert).
 */
class CommonReferenceSeeder extends Seeder
{
    public function run(): void
    {

        $countries = [
            ['iso2' => 'PK', 'iso3' => 'PAK', 'name' => 'Pakistan', 'name_ur' => 'پاکستان', 'dial_code' => '+92', 'sort_order' => 1],
            ['iso2' => 'US', 'iso3' => 'USA', 'name' => 'United States of America', 'name_ur' => 'امریکہ', 'dial_code' => '+1', 'sort_order' => 2],
            ['iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'name_ur' => 'برطانیہ', 'dial_code' => '+44', 'sort_order' => 3],
            ['iso2' => 'AE', 'iso3' => 'ARE', 'name' => 'United Arab Emirates', 'name_ur' => 'متحدہ عرب امارات', 'dial_code' => '+971', 'sort_order' => 4],
            ['iso2' => 'SA', 'iso3' => 'SAU', 'name' => 'Saudi Arabia', 'name_ur' => 'سعودی عرب', 'dial_code' => '+966', 'sort_order' => 5],
        ];

        foreach ($countries as $row) {
            Country::updateOrCreate(
                ['iso2' => $row['iso2']],
                $row + ['is_active' => true]
            );
        }

        $currencies = [
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => 'Rs', 'is_local' => true, 'sort_order' => 1],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_local' => false, 'sort_order' => 2],
            ['code' => 'GBP', 'name' => 'Great Britain Pound', 'symbol' => '£', 'is_local' => false, 'sort_order' => 3],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_local' => false, 'sort_order' => 4],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_local' => false, 'sort_order' => 5],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'is_local' => false, 'sort_order' => 6],
        ];

        foreach ($currencies as $row) {
            Currency::updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_active' => true]
            );
        }
    }
}
