<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountProduct;
use App\Models\CardType;
use App\Models\OperatingInstruction;
use App\Models\StatementDeliveryMode;
use App\Models\StatementFrequency;
use App\Models\ZakatExemptionReason;
use Illuminate\Database\Seeder;

/**
 * AOF #16..#18, #10, #23, #24 -- products, operating instructions, zakat
 * exemption codes, debit card, statement delivery & frequency.
 *
 * Dono forms ke farq yahin data mein handle hue hain:
 *   PPRSA + Debit Card      -> available_for = individual
 *   Hold Mail Facility      -> available_for = entity
 *   Cheque leaves 10/25/50  -> Individual products
 *   Cheque leaves 25/50/100 -> Entity products
 */
class ProductReferenceSeeder extends Seeder
{
    public function run(): void
    {

        $indLeaves = json_encode([10, 25, 50]);
        $entLeaves = json_encode([25, 50, 100]);

        $products = [
            // code, name, name_ur, class, currency_type, available_for, zakat, debit card, leaves, sort
            ['BCA', 'BAJK Current Account', 'بی اے جے کے کرنٹ اکاؤنٹ', 'current', 'local', 'both', false, true, $indLeaves, 1],
            ['PLS', 'PLS Saving Account (PLS)', 'پی ایل ایس سیونگ اکاؤنٹ', 'saving', 'local', 'both', true, true, $indLeaves, 2],
            ['SDA', 'Special Deposit Account (SDA)', 'سپیشل ڈپازٹ اکاؤنٹ', 'saving', 'local', 'both', true, true, $indLeaves, 3],
            ['BMBA', 'Bemisal Mahana Bachat Account (BMBA)', 'بے مثال ماہانہ بچت اکاؤنٹ', 'saving', 'local', 'both', true, true, $indLeaves, 4],
            ['PPRSA', 'Premium Plus Remittance Saving Account (PPRSA)', 'پریمیم پلس ریمیٹنس سیونگ اکاؤنٹ', 'saving', 'local', 'individual', true, true, $indLeaves, 5],
            ['BCA_FCY', 'BAJK Current Account - FCY', 'بی اے جے کے کرنٹ اکاؤنٹ ایف سی وائی', 'current', 'foreign', 'both', false, false, $entLeaves, 6],
            ['BSA_FCY', 'BAJK Saving Account - FCY', 'بی اے جے کے سیونگ اکاؤنٹ ایف سی وائی', 'saving', 'foreign', 'both', false, false, $entLeaves, 7],
        ];

        foreach ($products as [$code, $name, $nameUr, $class, $curr, $for, $zakat, $card, $leaves, $sort]) {
            AccountProduct::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr,
                'product_class' => $class, 'currency_type' => $curr, 'available_for' => $for,
                'is_zakat_applicable' => $zakat, 'allows_debit_card' => $card,
                'allowed_cheque_leaves' => $leaves, 'is_other' => false,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #18 Operating Instructions */
        $instructions = [
            ['SIGNING_AUTHORITY', 'Signing Authority', 'دستخط کنندہ', false, false, false, 1],
            ['SINGLY', 'Singly', 'واحد مجاز', false, false, false, 2],
            ['EITHER_OR_SURVIVOR', 'Either or Survivor', 'کوئی ایک یا باقی ماندہ', true, false, false, 3],
            ['JOINTLY', 'Jointly', 'مشترکہ', true, false, false, 4],
            ['MANDATE', 'Mandate', 'اختیار', false, true, false, 5],
            ['OI_OTHER', 'Other', 'دیگر', false, false, true, 6],
        ];

        foreach ($instructions as [$code, $name, $nameUr, $multi, $mandate, $other, $sort]) {
            OperatingInstruction::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr,
                'requires_multiple_holders' => $multi, 'requires_mandate_form' => $mandate,
                'is_other' => $other, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #10 Zakat Exemption Codes */
        $zakatReasons = [
            ['NON_MUSLIM', 'Non-Muslim', 'غیر مسلم', false, 1],
            ['FOREIGNER', 'Foreigner', 'غیر ملکی', false, 2],
            ['DUE_TO_FIQAH', 'Due to Fiqah', 'فقہ کی وجہ سے', false, 3],
            ['ZAKAT_OTHERS', 'Others', 'دیگر', true, 4],
        ];

        foreach ($zakatReasons as [$code, $name, $nameUr, $other, $sort]) {
            ZakatExemptionReason::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'is_other' => $other,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #24 Debit Card */
        CardType::updateOrCreate(['code' => 'BAJK_DEBIT'], [
            'name' => 'BAJK Debit Card (Enhanced ATM & POS limits)',
            'name_ur' => 'بی اے جے کے ڈیبٹ کارڈ',
            'name_on_card_max_length' => 19,
            'available_for' => 'individual',
            'sort_order' => 1, 'is_active' => true,
        ]);

        /* #23 Statement delivery modes */
        $modes = [
            ['E_STATEMENT', 'BAJK E-Statement', 'بی اے جے کے ای اسٹیٹمنٹ', true, 'both', 1],
            ['POST_COURIER', 'Mail by Post/Courier', 'بذریعہ ڈاک یا کورئیر', false, 'both', 2],
            ['HOLD_MAIL', 'Hold Mail Facility', 'ڈاک روکنے کی سہولت', false, 'entity', 3],
        ];

        foreach ($modes as [$code, $name, $nameUr, $email, $for, $sort]) {
            StatementDeliveryMode::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'requires_email' => $email,
                'available_for' => $for, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #23 Statement frequency */
        $freq = [
            ['MONTHLY', 'Monthly', 'ماہانہ', 1, 'both', 1],
            ['QUARTERLY', 'Quarterly', 'سہ ماہی', 3, 'both', 2],
            ['SEMI_ANNUAL', 'Six Monthly / Semi-Annually', 'ششماہی', 6, 'both', 3],
            ['YEARLY', 'Yearly', 'سالانہ', 12, 'individual', 4],
        ];

        foreach ($freq as [$code, $name, $nameUr, $months, $for, $sort]) {
            StatementFrequency::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'months_interval' => $months,
                'available_for' => $for, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }
    }
}
