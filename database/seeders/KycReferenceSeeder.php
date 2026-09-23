<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountPurpose;
use App\Models\ControllingPersonType;
use App\Models\CounterPartyType;
use App\Models\CrsEntityClassification;
use App\Models\IncomeSource;
use App\Models\TransactionMode;
use App\Models\WealthSource;
use Illuminate\Database\Seeder;

/**
 * AOF #13, #31, #33 -- CDD aur FATCA/CRS ke tamam checkbox lists.
 *
 * income_sources mein Individual CDD aur Business CDD dono ke options hain,
 * `applies_to` se alag ho jate hain -- isi wajah se ek hi CDD table dono
 * forms ko cover karti hai.
 */
class KycReferenceSeeder extends Seeder
{
    public function run(): void
    {

        /* #31/#33 Source of Income/Occupation/Profession */
        $income = [
            // code, name, name_ur, applies_to, employer, fund_provider, country, other, sort
            ['SALARIED', 'Salaried', 'تنخواہ دار', 'individual', true, false, false, false, 1],
            ['PENSIONER', 'Pensioner', 'پنشنر', 'individual', true, false, false, false, 2],
            ['STUDENT', 'Student', 'طالب علم', 'individual', false, true, false, false, 3],
            ['HOUSE_WIFE', 'House Wife', 'خاتون خانہ', 'individual', false, true, false, false, 4],
            ['UNEMPLOYED', 'Unemployed', 'بے روزگار', 'individual', false, true, false, false, 5],
            ['SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', 'both', false, false, false, false, 6],
            ['LABOR_DAILY_WAGES', 'Labor/Daily Wages', 'مزدور/یومیہ اجرت', 'individual', false, false, false, false, 7],
            ['AGRICULTURIST', 'Agriculturist', 'زمیندار', 'individual', false, false, false, false, 8],
            ['STOCK_INVESTMENT', 'Stock/Investment', 'اسٹاک/سرمایہ کاری', 'individual', false, false, false, false, 9],
            ['RENTED_PROPERTY', 'Rented Property', 'کرایہ کی جائیداد', 'both', false, false, false, false, 10],
            ['HOME_REMITTANCE', 'Home Remittance', 'ترسیلات زر', 'individual', false, false, true, false, 11],
            ['EXPORT_PROCEEDS', 'Export Proceeds', 'برآمدی آمدن', 'entity', false, false, false, false, 12],
            ['PROPERTY_REAL_ESTATE', 'Property/Real Estate', 'جائیداد/رئیل اسٹیٹ', 'entity', false, false, false, false, 13],
            ['FDI', 'Foreign Direct Investment (FDI)', 'براہ راست غیر ملکی سرمایہ کاری', 'entity', false, false, false, false, 14],
            ['LOCAL_TRADING', 'Local Trading', 'مقامی تجارت', 'entity', false, false, false, false, 15],
            ['EQUITY_FX_TRADING', 'Equity/FX Trading', 'ایکویٹی/ایف ایکس ٹریڈنگ', 'entity', false, false, false, false, 16],
            ['CHARITY_DONATIONS', 'Charity & Funds Donations', 'خیرات و عطیات', 'entity', false, false, false, false, 17],
            ['INCOME_OTHERS', 'Others', 'دیگر', 'both', false, false, false, true, 18],
        ];

        foreach ($income as [$code, $name, $nameUr, $for, $emp, $fund, $country, $other, $sort]) {
            IncomeSource::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'applies_to' => $for,
                'requires_employer_details' => $emp,
                'requires_fund_provider_details' => $fund,
                'requires_country' => $country,
                'is_other' => $other, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #31 Source of Wealth */
        $wealth = [
            ['GIFT', 'Gift', 'تحفہ', false, 1],
            ['PERSONAL_SAVINGS', 'Personal Savings', 'ذاتی بچت', false, 2],
            ['RENTED_PROPERTY_SALES', 'Rented Property/Property Sales', 'کرایہ/جائیداد کی فروخت', false, 3],
            ['INHERITANCE', 'Inheritance', 'وراثت', false, 4],
            ['WEALTH_OTHERS', 'Others', 'دیگر', true, 5],
        ];

        foreach ($wealth as [$code, $name, $nameUr, $other, $sort]) {
            WealthSource::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'is_other' => $other,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #31/#33 Usual Mode of Credit & Debit Transaction */
        $modes = [
            ['CASH', 'Cash', 'نقد', false, 1],
            ['CLEARING', 'Clearing', 'کلیئرنگ', false, 2],
            ['REMITTANCE', 'Remittance', 'ترسیل', false, 3],
            ['COLLECTION', 'Collection', 'کلیکشن', false, 4],
            ['MODE_OTHERS', 'Others', 'دیگر', true, 5],
        ];

        foreach ($modes as [$code, $name, $nameUr, $other, $sort]) {
            TransactionMode::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'is_other' => $other,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #31/#33 Purpose of Account */
        $purposes = [
            ['SAVING', 'Saving', 'بچت', 'both', false, 1],
            ['BUSINESS', 'Business', 'کاروبار', 'both', false, 2],
            ['TRANSACTIONAL', 'Transactional', 'لین دین', 'individual', false, 3],
            ['CREDIT_FACILITY', 'Credit Facility', 'قرض کی سہولت', 'entity', false, 4],
            ['PURPOSE_OTHERS', 'Others', 'دیگر', 'both', true, 5],
        ];

        foreach ($purposes as [$code, $name, $nameUr, $for, $other, $sort]) {
            AccountPurpose::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'applies_to' => $for,
                'is_other' => $other, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #33 Expected Type of Counter Parties */
        $counter = [
            ['FINANCIAL_INSTITUTION', 'Financial Institution', 'مالیاتی ادارہ', false, 1],
            ['LIMITED_COMPANY', 'Limited Company', 'لمیٹڈ کمپنی', false, 2],
            ['PROPRIETORSHIP', 'Proprietorship', 'پروپرائٹرشپ', false, 3],
            ['GOVT_ENTITY', 'Govt. Entity', 'سرکاری ادارہ', false, 4],
            ['NPO', 'NPO', 'این پی او', false, 5],
            ['COUNTER_OTHERS', 'Others', 'دیگر', true, 6],
        ];

        foreach ($counter as [$code, $name, $nameUr, $other, $sort]) {
            CounterPartyType::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'is_other' => $other,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #13 CRS Entity Classification -- Entity AOF p.3 & p.4 ke options (a)..(i) */
        $crs = [
            ['INV_ENTITY_NP_COUNTRY', 'a', 'Investment Entity located in a Non-Participating Country and managed by another Financial Institution', false, true, false, 1],
            ['OTHER_INV_ENTITY', 'b', 'Other Investment Entity', true, false, false, 2],
            ['FI_DEPOSITORY', 'c', 'Financial Institution - Depository Institution, Custodial Institution or Specified Insurance Company', true, false, false, 3],
            ['ACTIVE_NFE_LISTED', 'd', 'An Active NFE, stock of which is regularly traded on an established securities market, or a corporation which is a related entity of such a corporation', false, false, true, 4],
            ['GOVT_ENTITY_CENTRAL_BANK', 'e', 'Government Entity or Central Bank', false, false, false, 5],
            ['INTERNATIONAL_ORG', 'f', 'International Organization', false, false, false, 6],
            ['NGO_NPO_STARTUP_NFE', 'g', 'NGO/NPO or a startup NFE or Non Profit NFE', false, false, false, 7],
            ['ACTIVE_NFE_INCOME', 'h', 'Active NFE - having 50% and above income derived from core business activity', false, false, false, 8],
            ['PASSIVE_NFE', 'i', 'Passive NFE', false, true, false, 9],
        ];

        foreach ($crs as [$code, $opt, $name, $giin, $cp, $exchange, $sort]) {
            CrsEntityClassification::updateOrCreate(['code' => $code], [
                'form_option' => $opt, 'name' => $name,
                'requires_giin' => $giin, 'requires_controlling_persons' => $cp,
                'requires_stock_exchange' => $exchange,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* #13 Type of Controlling Person */
        $cpTypes = [
            ['OWNER', 'Owner (direct or indirect)', 'مالک (بلاواسطہ یا بالواسطہ)', 1],
            ['CONTROL_OTHER_MEANS', 'Controlling Person by other means', 'کنٹرول کرنے والا دیگر ذرائع سے', 2],
            ['SENIOR_MGMT', 'Senior Management Official', 'سینئر مینجمنٹ آفیشل', 3],
            ['BENEFICIARY', 'Beneficiary', 'بینیفشری', 4],
            ['SETTLOR', 'Settlor', 'سیٹلر', 5],
            ['TRUSTEE', 'Trustee', 'ٹرسٹی', 6],
            ['PROTECTOR', 'Protector', 'پروٹیکٹر', 7],
            ['SETTLOR_EQUIVALENT', 'Settlor-equivalent', 'سیٹلر مساوی', 8],
            ['TRUSTEE_EQUIVALENT', 'Trustee-equivalent', 'ٹرسٹی مساوی', 9],
            ['PROTECTOR_EQUIVALENT', 'Protector-equivalent', 'پروٹیکٹر مساوی', 10],
            ['BENEFICIARY_EQUIVALENT', 'Beneficiary-equivalent', 'بینیفشری مساوی', 11],
            ['OTHER_EQUIVALENT', 'Other-equivalent', 'دیگر مساوی', 12],
        ];

        foreach ($cpTypes as [$code, $name, $nameUr, $sort]) {
            ControllingPersonType::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }
    }
}
