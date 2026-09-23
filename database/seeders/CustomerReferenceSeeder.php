<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BusinessNature;
use App\Models\CustomerCategory;
use App\Models\EducationLevel;
use App\Models\Gender;
use App\Models\IdentificationDocumentType;
use App\Models\MaritalStatus;
use App\Models\Profession;
use App\Models\Relationship;
use App\Models\SpecialCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * CIF ke tamam checkbox/dropdown lists -- DONO PDFs se bilkul waise hi.
 *
 * customer_categories mein Individual AOF aur Entity AOF dono ki categories
 * ek hi table mein hain (`applies_to` se alag hoti hain).
 */
class CustomerReferenceSeeder extends Seeder
{
    public function run(): void
    {

        /* ---------- #04 Customer Category ---------- */
        // i = individual profile chahiye, o = organization profile chahiye
        $categories = [
            // ---- AOF-Individual p.2 ----
            ['INDIVIDUAL', 'Individual', 'انفرادی', 'individual', true, false, false, 1],
            ['SOLE_PROP', 'Sole Proprietorship', 'سول پروپرائٹر شپ', 'individual', true, true, false, 2],
            ['JOINT', 'Joint', 'مشترکہ', 'individual', true, false, false, 3],
            ['IND_OTHERS', 'Others', 'دیگر', 'individual', true, false, true, 4],
            // ---- AOF-Entity p.2 ----
            ['PARTNERSHIP', 'Partnership (Registered/Unregistered)', 'شراکت (رجسٹرڈ/غیر رجسٹرڈ)', 'entity', false, true, false, 10],
            ['LIMITED_CO', 'Limited Companies', 'لمیٹڈ کمپنیاں', 'entity', false, true, false, 11],
            ['PUBLIC_LISTED', 'Public Listed', 'پبلک لسٹڈ', 'entity', false, true, false, 12],
            ['PUBLIC_UNLISTED', 'Public Unlisted', 'پبلک ان لسٹڈ', 'entity', false, true, false, 13],
            ['PRIVATE', 'Private', 'پرائیویٹ', 'entity', false, true, false, 14],
            ['FOREIGN_MISSION', 'Foreign Missions/Diplomates', 'غیر ملکی سفارت خانے/سفارتکار', 'entity', false, true, false, 15],
            ['GOVT_INSTITUTION', 'Government Institution (Federal/Provisional/Local)', 'سرکاری ادارہ (وفاقی/صوبائی/مقامی)', 'entity', false, true, false, 16],
            ['SOCIETY', 'Societies', 'سوسائٹیز', 'entity', false, true, false, 17],
            ['CLUB', 'Club', 'کلب', 'entity', false, true, false, 18],
            ['TRUST', 'Trust', 'ٹرسٹ', 'entity', false, true, false, 19],
            ['ASSOCIATION', 'Associations', 'ایسوسی ایشن', 'entity', false, true, false, 20],
            ['LOCAL_ZAKAT', 'Local Zakat Committee', 'لوکل زکوٰۃ کمیٹی', 'entity', false, true, false, 21],
            ['MONEY_EXCHANGE', 'Business - Money Exchange Company', 'منی ایکسچینج کمپنی', 'entity', false, true, false, 22],
            ['FOREIGN_BRANCH', 'Branch Office or Liaison Office of Foreign Companies', 'غیر ملکی کمپنیوں کا برانچ/رابطہ دفتر', 'entity', false, true, false, 23],
            ['NGO_NPO', "NGO/NPO's Charities", 'این جی او/این پی او/خیراتی ادارے', 'entity', false, true, false, 24],
            ['AGENT', 'Agents Accounts', 'ایجنٹ اکاؤنٹ', 'entity', false, true, false, 25],
            ['EXECUTOR', 'Executors & Administrators', 'مختار کنندہ اور منتظم', 'entity', false, true, false, 26],
            ['ENT_OTHERS', 'Others', 'دیگر', 'entity', false, true, true, 27],
        ];

        foreach ($categories as [$code, $name, $nameUr, $appliesTo, $ind, $org, $isOther, $sort]) {
            CustomerCategory::updateOrCreate(['code' => $code], [
                'name' => $name,
                'name_ur' => $nameUr,
                'applies_to' => $appliesTo,
                'requires_individual_profile' => $ind,
                'requires_organization_profile' => $org,
                'is_other' => $isOther,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }

        /* ---------- #05 Special Category of Account (AOF-Ind p.2) ---------- */
        $special = [
            ['BAJK_STAFF', 'BAJK Staff', 'بی اے جے کے کا عملہ', false, null, false, 1],
            ['MINOR', 'Minor', 'نابالغ', false, null, false, 2],
            ['PHOTO_ACCOUNT', 'Photo Account', 'فوٹو اکاؤنٹ', false, null, false, 3],
            ['MUSTAHIQEEN_ZAKAT', 'Mustahiqeen-e-Zakat', 'مستحقین زکوٰۃ', false, null, false, 4],
            ['PARDA_NASHEEN', 'Parda Nasheen', 'پردہ نشین', false, null, false, 5],
            ['STUDENT', 'Student', 'طالب علم', false, null, false, 6],
            ['VISUALLY_IMPAIRED', 'Visually Impaired Person/Blind', 'کمزور بصارت/نابینا افراد', false, null, false, 7],
            ['DISASTER_IDP', 'Disaster Affectees/IDPs', 'آفت زدہ/نقل مکانی کرنے والے', false, null, false, 8],
            ['REGISTERED_ALIEN', 'Registered Alien', 'رجسٹرڈ غیر ملکی', false, null, false, 9],
            ['WIDOW', 'Widow', 'بیوہ', false, null, false, 10],
            ['PENSIONERS', 'Pensioners', 'پنشنرز', false, null, false, 11],
            ['SENIOR_CITIZEN', 'Senior Citizen', 'بزرگ شہری', false, null, false, 12],
            ['GOVT_EMP', 'Govt. Emp/Semi Govt. Emp', 'سرکاری/نیم سرکاری ملازم', false, null, false, 13],
            ['PENSION', 'Pension', 'پنشن', false, null, false, 14],
            ['SALARY', 'Salary', 'تنخواہ', false, null, false, 15],
            ['PHYSICAL_HANDICAP', 'Physically Handicapped', 'معذور افراد', false, null, false, 16],
            ['NON_RESIDENT', 'Non-Resident (in years)', 'بیرون ملک رہائشی', true, 'Time since residing out side Pakistan (in years)', false, 17],
            ['SPECIAL_OTHERS', 'Others', 'دیگر', false, null, true, 18],
        ];

        foreach ($special as [$code, $name, $nameUr, $extra, $label, $isOther, $sort]) {
            SpecialCategory::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr,
                'requires_extra_value' => $extra, 'extra_value_label' => $label,
                'is_other' => $isOther, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* ---------- Simple code lists ---------- */
        // Gender table mein `is_other` column nahi hai -- alag se seed hota hai.
        $genders = [
            ['MALE', 'Male', 'مرد', 1],
            ['FEMALE', 'Female', 'عورت', 2],
            ['TRANSGENDER', 'Trans Gender', 'خواجہ سرا', 3],
        ];

        foreach ($genders as [$code, $name, $nameUr, $sort]) {
            Gender::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'sort_order' => $sort,
                'is_active' => true,
            ]);
        }

        /**
         * Table code => Eloquent model, for the simple `code/name/name_ur/is_other`
         * lists that share an identical shape across both AOF forms.
         *
         * @var array<string, class-string<Model>>
         */
        $models = [
            'marital_statuses' => MaritalStatus::class,
            'education_levels' => EducationLevel::class,
            'professions' => Profession::class,
            'business_natures' => BusinessNature::class,
        ];

        $simple = [
            'marital_statuses' => [
                ['MARRIED', 'Married', 'شادی شدہ', false, 1],
                ['SINGLE', 'Single', 'غیر شادی شدہ', false, 2],
                ['MS_OTHERS', 'Others', 'دیگر', true, 3],
            ],
            'education_levels' => [
                ['NO_EDUCATION', 'No Education', 'نا خواندہ', false, 1],
                ['BELOW_MATRIC', 'Below Matric', 'میٹرک سے کم', false, 2],
                ['MATRIC_O_LEVEL', 'Matric/O level', 'میٹرک/او لیول', false, 3],
                ['INTER_A_LEVEL', 'Intermediate/A level', 'انٹرمیڈیٹ/اے لیول', false, 4],
                ['GRADUATE', 'Graduate', 'گریجویٹ', false, 5],
                ['POSTGRADUATE', 'Postgraduate', 'پوسٹ گریجویٹ', false, 6],
                ['EDU_OTHERS', 'Others', 'دیگر', true, 7],
            ],
            'professions' => [
                ['GOVT_SERVICE', 'Government Service', 'سرکاری ملازمت', false, 1],
                ['PRIVATE_SERVICE', 'Private Service', 'پرائیویٹ ملازمت', false, 2],
                ['HOUSEWIFE', 'Housewife', 'خاتون خانہ', false, 3],
                ['SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', false, 4],
                ['UNEMPLOYED', 'Unemployed', 'بے روزگار', false, 5],
                ['AGRICULTURE', 'Agriculture', 'زراعت', false, 6],
                ['PROF_STUDENT', 'Student', 'طالب علم', false, 7],
                ['PROF_OTHERS', 'Others', 'دیگر', true, 8],
            ],
            'business_natures' => [
                ['IMPORT_EXPORT', 'Import/Export', 'درآمد/برآمد', false, 1],
                ['AGRICULTURE', 'Agriculture', 'زراعت', false, 2],
                ['MANUFACTURING', 'Manufacturing', 'مینوفیکچرنگ', false, 3],
                ['EXCHANGE_COMPANY', 'Exchange Company', 'ایکسچینج کمپنی', false, 4],
                ['NBFI', 'NBFI', 'این بی ایف آئی', false, 5],
                ['SCHEDULED_BANK', 'Scheduled Bank', 'شیڈول بینک', false, 6],
                ['RETAIL_BUSINESS', 'Retail Business', 'ریٹیل کاروبار', false, 7],
                ['BN_OTHERS', 'Others', 'دیگر', true, 8],
            ],
        ];

        foreach ($simple as $table => $rows) {
            foreach ($rows as [$code, $name, $nameUr, $isOther, $sort]) {
                $models[$table]::updateOrCreate(['code' => $code], [
                    'name' => $name, 'name_ur' => $nameUr, 'is_other' => $isOther,
                    'sort_order' => $sort, 'is_active' => true,
                ]);
            }
        }

        /* ---------- Relationships (form ki har jagah ke liye) ---------- */
        $relationships = [
            ['FATHER', 'Father (S/o)', 'والد', true, true, true, true, true, 1],
            ['MOTHER', 'Mother', 'والدہ', false, true, true, true, true, 2],
            ['HUSBAND', 'Husband (W/o)', 'شوہر', true, true, true, true, true, 3],
            ['WIFE', 'Wife', 'بیوی', false, true, true, true, true, 4],
            ['SON', 'Son', 'بیٹا', false, true, true, true, true, 5],
            ['DAUGHTER', 'Daughter (D/o)', 'بیٹی', true, true, true, true, true, 6],
            ['BROTHER', 'Brother', 'بھائی', false, true, true, true, true, 7],
            ['SISTER', 'Sister', 'بہن', false, true, true, true, true, 8],
            ['GUARDIAN', 'Guardian', 'سرپرست', false, true, true, true, false, 9],
            ['EMPLOYER', 'Employer', 'آجر', false, false, false, true, false, 10],
            ['REL_OTHER', 'Other', 'دیگر', false, true, true, true, true, 11],
        ];

        foreach ($relationships as [$code, $name, $nameUr, $p, $g, $n, $f, $u, $sort]) {
            Relationship::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr,
                'context_parentage' => $p, 'context_guardian' => $g,
                'context_next_of_kin' => $n, 'context_fund_provider' => $f,
                'context_ubo' => $u, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* ---------- #07 Identification document types ---------- */
        $idTypes = [
            ['CNIC', 'Computerized National Identity Card (CNIC)', 'کمپیوٹرائزڈ قومی شناختی کارڈ', 'NADRA', false, false, 1],
            ['SNIC', 'Smart National Identity Card (SNIC)', 'سمارٹ قومی شناختی کارڈ', 'NADRA', false, false, 2],
            ['NICOP', 'National Identity Card for Overseas Pakistanis (NICOP)', 'نائیکوپ', 'NADRA', false, true, 3],
            ['POC', 'Pakistan Origin Card (POC)', 'پاکستان اوریجن کارڈ', 'NADRA', false, true, 4],
            ['ARC', 'Alien Registration Card (AR+D47C)', 'ایلین رجسٹریشن کارڈ', 'NARA, Ministry of Interior', false, true, 5],
            ['POR', 'Proof of Registration Card (PoR)', 'پی او آر کارڈ', 'NADRA', false, true, 6],
            ['PASSPORT', 'Passport (Foreign Individual Only)', 'پاسپورٹ', 'Passport Authority', false, true, 7],
            ['CRC_B_FORM', 'CRC / B-Form', 'سی آر سی/بی فارم', 'NADRA', true, false, 8],
            ['BIRTH_CERT', 'Birth Certificate (Government Authority)', 'برتھ سرٹیفکیٹ', 'Government Authority', true, false, 9],
            ['STUDENT_ID', 'Student ID Card', 'طالب علم کارڈ', 'Educational Institution', true, false, 10],
        ];

        foreach ($idTypes as [$code, $name, $nameUr, $authority, $minor, $foreign, $sort]) {
            IdentificationDocumentType::updateOrCreate(['code' => $code], [
                'name' => $name, 'name_ur' => $nameUr, 'issuing_authority' => $authority,
                'is_for_minor' => $minor, 'is_for_foreigner' => $foreign,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /*
        | Economic Sector Codes jaan boojh kar khali chhoray gaye hain:
        | yeh SBP ki official list se aate hain, AOF par sirf khali box hai.
        | Apni list yahan add kar dein:
        |   EconomicSector::updateOrCreate(['code' => '1001'], [...]);
        */
    }
}
