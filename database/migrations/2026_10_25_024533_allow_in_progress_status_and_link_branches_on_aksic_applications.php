<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = ['NotCompleted', 'Pending', 'In Progress', 'Forwarded', 'Approved', 'Rejected'];

    /**
     * The status sent back to the AKSIC API is "In Progress", so the application status must accept it,
     * and applications already synced get their branch from the branch code the applicant chose.
     */
    public function up(): void
    {
        Schema::table('aksic_applications', function (Blueprint $table) {
            $table->enum('status', self::STATUSES)->default('Pending')->change();
        });

        DB::table('aksic_applications')
            ->whereNull('branch_id')
            ->whereNotNull('applicant_choosed_branch_code')
            ->orderBy('id')
            ->chunkById(500, function ($applications) {
                foreach ($applications as $application) {
                    $branchId = DB::table('branches')->where('code', $application->applicant_choosed_branch_code)->value('id');

                    if ($branchId !== null) {
                        DB::table('aksic_applications')->where('id', $application->id)->update(['branch_id' => $branchId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('aksic_applications', function (Blueprint $table) {
            $table->enum('status', ['NotCompleted', 'Pending', 'Forwarded', 'Approved', 'Rejected'])->default('Pending')->change();
        });
    }
};
