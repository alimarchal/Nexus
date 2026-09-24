<?php

namespace Database\Seeders;

use App\Models\Aksic;
use App\Models\AksicAmortization;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Services\AksicAmortizationScheduleGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Random demo AKSIC cases for testing the list, filters, export and print.
 *
 *   php artisan db:seed --class=AksicDemoCasesSeeder              (520 cases)
 *   AKSIC_DEMO_COUNT=2000 php artisan db:seed --class=AksicDemoCasesSeeder
 *   AKSIC_DEMO_COUNT=0 php artisan db:seed --class=AksicDemoCasesSeeder   (remove demo data only)
 *
 * Every demo case has an application no starting "DEMO-" so it can be removed
 * safely; re-running the seeder first deletes the previous demo cases and any
 * rows imported from the sample Excel file ("IMP-").
 * About 65% are approved with a real repayment schedule from the generator;
 * all cases carry the data the scheme rules need (site visit, consent, security).
 */
class AksicDemoCasesSeeder extends Seeder
{
    private const PREFIX = 'DEMO-';

    private const SAMPLE_PREFIX = 'IMP-';

    public function run(AksicAmortizationScheduleGenerator $generator): void
    {
        $count = (int) env('AKSIC_DEMO_COUNT', 520);

        $this->removeExisting();
        if ($count <= 0) {
            $this->command?->info('Demo AKSIC cases removed.');

            return;
        }

        $rules = AksicRule::query()->where('is_active', true)->get()->keyBy('district_id');
        $branches = Branch::query()->whereIn('district_id', $rules->keys())->get(['id', 'code', 'district_id'])->groupBy('district_id');
        $subCategories = AksicBusinessCategory::query()->where('parent_id', '!=', 0)->get(['id', 'parent_id']);

        if ($rules->isEmpty() || $branches->isEmpty() || $subCategories->isEmpty()) {
            $this->command?->error('Need active AKSIC rules, branches and business categories first.');

            return;
        }

        // Weighted by each district's population share, like the real scheme.
        $districtPool = $rules->flatMap(fn ($rule) => array_fill(0, max(1, (int) round($rule->population_percentage)), $rule->district_id))
            ->filter(fn ($id) => $branches->has($id))->values();

        $male = ['Muhammad Ali', 'Ahmed Raza', 'Usman Khan', 'Bilal Hussain', 'Zeeshan Abbasi', 'Faisal Mir', 'Kamran Awan', 'Imran Qureshi',
            'Hamza Rathore', 'Adeel Chaudhry', 'Sajid Mughal', 'Waqas Butt', 'Tariq Sudhan', 'Naveed Gilani', 'Shahid Kiani', 'Asad Rajput'];
        $female = ['Ayesha Bibi', 'Fatima Noor', 'Sana Kausar', 'Rabia Khan', 'Mehwish Abbasi', 'Hina Shah', 'Nazia Parveen', 'Saima Akhtar',
            'Iqra Batool', 'Amna Riaz', 'Kiran Naz', 'Zainab Gillani', 'Sadia Mir', 'Maryam Awan', 'Asma Rathore', 'Nida Chaudhry'];
        $fathers = ['Muhammad Aslam', 'Ghulam Rasool', 'Abdul Rehman', 'Muhammad Sadiq', 'Raja Akbar', 'Sardar Iqbal', 'Muhammad Yousaf', 'Abdul Majeed'];
        $amounts = [500000, 750000, 1000000, 1000000, 1500000, 2000000];

        mt_srand(20260923); // same demo data every run
        $userId = DB::table('users')->orderBy('id')->value('id');
        $bar = $this->command?->getOutput()->createProgressBar($count);

        for ($i = 1; $i <= $count; $i++) {
            DB::transaction(function () use ($i, $generator, $rules, $branches, $subCategories, $districtPool, $male, $female, $fathers, $amounts, $userId): void {
                $districtId = $districtPool[mt_rand(0, $districtPool->count() - 1)];
                $rule = $rules[$districtId];
                $branch = $branches[$districtId]->random();
                $sub = $subCategories->random();

                $roll = mt_rand(1, 100);
                $quota = $roll <= 48 ? 'Male' : ($roll <= 96 ? 'Female' : ($roll <= 98 ? 'Disabled' : 'Transgender'));
                $gender = $quota === 'Disabled' ? (mt_rand(0, 1) ? 'Male' : 'Female') : $quota;
                $name = ($gender === 'Female' ? $female : $male)[mt_rand(0, 15)];
                $principal = $amounts[mt_rand(0, count($amounts) - 1)];
                $created = Carbon::create(2026, 1, 1)->addDays(mt_rand(0, 250))->setTime(mt_rand(9, 16), mt_rand(0, 59));
                $disbursed = $created->copy()->addDays(mt_rand(3, 25))->startOfDay();
                $approved = mt_rand(1, 100) <= 65;
                // Every demo case is complete per the district rule (site visit, consent, security),
                // so any pending one can be approved from the case page.
                $siteVisit = $created->copy()->addDays(mt_rand(1, max(1, $created->diffInDays($disbursed) - 1)))->startOfDay();
                $consent = $siteVisit->copy()->addDay();
                $village = ['Chattar', 'Upper Plate', 'Gojra', 'Tariqabad', 'Naluchi', 'Ranjata', 'Main Bazar', 'Sathra'][mt_rand(0, 7)];

                $aksic = Aksic::query()->create([
                    'application_no' => self::PREFIX.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'account_no' => mt_rand(1, 10) <= 7 ? $branch->code.'-'.mt_rand(1000000, 9999999) : null,
                    'name' => strtoupper($name),
                    'father_name' => strtoupper($fathers[mt_rand(0, count($fathers) - 1)]),
                    'cnic' => sprintf('8%04d-%07d-%d', mt_rand(1000, 9999), mt_rand(1000000, 9999999), $gender === 'Female' ? 2 * mt_rand(0, 4) : 2 * mt_rand(0, 4) + 1),
                    'dob' => Carbon::create(mt_rand(1970, 2002), mt_rand(1, 12), mt_rand(1, 28))->toDateString(),
                    'cnic_issue_date' => Carbon::create(mt_rand(2016, 2024), mt_rand(1, 12), mt_rand(1, 28))->toDateString(),
                    'permanent_address' => 'House '.mt_rand(1, 400).', '.$village.', '.$rule->district_name,
                    'business_address' => 'Shop '.mt_rand(1, 90).', '.$village.' Market, '.$rule->district_name,
                    'phone' => '03'.mt_rand(0, 4).mt_rand(0, 9).'-'.mt_rand(1000000, 9999999),
                    'business_name' => 'Demo Business '.$i,
                    'business_type' => mt_rand(0, 1) ? 'Existing' : 'New',
                    'is_startup_business' => false,
                    'quota' => $quota,
                    'gender' => $gender,
                    'business_category_id' => $sub->parent_id,
                    'business_sub_category_id' => $sub->id,
                    'tier' => $principal <= 500000 ? 1 : ($principal <= 1500000 ? 2 : 3),
                    'amount' => $principal,
                    'district_id' => $districtId,
                    'district_name' => $rule->district_name,
                    'aksic_rule_id' => $rule->id,
                    'branch_id' => $branch->id,
                    'status' => $approved ? 'Approved' : 'Pending',
                    'principal_amount' => $principal,
                    'tenure' => 60,
                    'disbursement_date' => $disbursed->toDateString(),
                    'site_visit_completed' => true,
                    'site_visit_date' => $siteVisit->toDateString(),
                    'consent_entry' => 'Yes',
                    'consent_date' => $consent->min($disbursed)->toDateString(),
                    'liquid_security' => 'Post-dated cheques for 60 instalments',
                    'personal_guarantees' => 'Personal guarantee of '.strtoupper($fathers[mt_rand(0, count($fathers) - 1)]),
                    'mortgage' => $principal > 1000000 ? 'Equitable mortgage of residential property, '.$village : null,
                    'kibor_rate' => 12.00,
                    'spread_rate' => 2.00,
                    'total_rate' => 14.00,
                    'total_interest' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                // Keep the realistic entry date (created_at is not fillable).
                $aksic->forceFill(['created_at' => $created, 'updated_at' => $created])->saveQuietly();

                if ($approved) {
                    $rows = $generator->generate((string) $principal, 60, $disbursed->toDateString(), '12.00', '2.00');
                    $now = now();
                    AksicAmortization::query()->insert(array_map(fn (array $row) => $row + [
                        'aksic_id' => $aksic->id, 'created_by' => $userId, 'updated_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
                    ], $rows));
                    $aksic->forceFill(['total_interest' => array_sum(array_column($rows, 'total_interest'))])->saveQuietly();
                }
            });
            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();
        Cache::forever(Aksic::STATS_VERSION_KEY, (string) microtime(true));
        $this->command?->info("{$count} demo AKSIC cases created (application no ".self::PREFIX.'00001 onwards).');
    }

    private function removeExisting(): void
    {
        // Also clears rows imported from the 500-case sample file (application no "IMP-").
        $ids = Aksic::withTrashed()
            ->where(fn ($q) => $q->where('application_no', 'like', self::PREFIX.'%')->orWhere('application_no', 'like', self::SAMPLE_PREFIX.'%'))
            ->pluck('id');

        foreach ($ids->chunk(500) as $chunk) {
            DB::table('aksic_amortizations')->whereIn('aksic_id', $chunk)->delete();
            DB::table('aksic_claim_items')->whereIn('aksic_id', $chunk)->delete();
            DB::table('aksics')->whereIn('id', $chunk)->delete();
        }
    }
}
