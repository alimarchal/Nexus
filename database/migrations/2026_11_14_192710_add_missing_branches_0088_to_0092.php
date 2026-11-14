<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Branches listed on the bank's branch-network page that were missing from the system (it stopped at 0087).
     * Without them an AKSIC import row naming one of these codes is saved with no branch, so branch and region
     * users cannot see the case. Each branch is added once: a code that already exists is left untouched, and its
     * region comes from its district.
     *
     * @var array<int, array{code: string, name: string, district: string, address: string}>
     */
    private const BRANCHES = [
        // Placeholder: 0088 is not on the bank's branch-network page. AKSIC rows use it for Bhimber / Mirpur, so it is
        // created under Bhimber with a temporary name and address; edit them once the real details are known.
        ['code' => '0088', 'name' => 'EXAMPLE', 'district' => 'Bhimber', 'address' => 'EXAMPLE'],
        ['code' => '0089', 'name' => 'Rahim Kot Branch', 'district' => 'Muzaffarabad', 'address' => 'Rahim Kot (UC Katkair), Tehsil & District Muzaffarabad, Azad Kashmir'],
        ['code' => '0090', 'name' => 'Gulpur Branch', 'district' => 'Kotli', 'address' => 'Mirpur Road, Main Bazar, Gulpur, Tehsil Charhoi, District Kotli, Azad Kashmir'],
        ['code' => '0091', 'name' => 'Mumtazabad Branch', 'district' => 'Haveli', 'address' => 'Main Bazar Mumtazabad (Palangi), Tehsil Mumtazabad, District Haveli, Azad Kashmir'],
        ['code' => '0092', 'name' => 'Khurshidabad Branch', 'district' => 'Haveli', 'address' => 'Main Bazar Khurshidabad, Tehsil Khurshidabad, District Haveli, Azad Kashmir'],
    ];

    public function up(): void
    {
        foreach (self::BRANCHES as $branch) {
            if (DB::table('branches')->where('code', $branch['code'])->exists()) {
                continue;
            }

            $district = DB::table('districts')->where('name', $branch['district'])->first(['id', 'region_id']);

            if ($district === null) {
                continue;
            }

            DB::table('branches')->insert([
                'region_id' => $district->region_id,
                'district_id' => $district->id,
                'code' => $branch['code'],
                'name' => $branch['name'],
                'address' => $branch['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Only branches no case points to are removed, so a rollback never leaves a case without its branch.
        DB::table('branches')
            ->whereIn('code', array_column(self::BRANCHES, 'code'))
            ->whereNotIn('id', DB::table('aksics')->whereNotNull('branch_id')->select('branch_id'))
            ->delete();
    }
};
