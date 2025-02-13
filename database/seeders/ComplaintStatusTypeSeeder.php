<?php

namespace Database\Seeders;

use App\Models\Complaint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert default status types
        DB::table('complaint_status_types')->insert([
            ['name' => 'Submitted', 'code' => 'SUBMITTED', 'description' => 'Complaint has been received'],
            ['name' => 'Under Review', 'code' => 'UNDER_REVIEW', 'description' => 'Complaint is being evaluated'],
            ['name' => 'Investigation', 'code' => 'INVESTIGATION', 'description' => 'Detailed investigation in progress'],
            ['name' => 'Pending Response', 'code' => 'PENDING_RESPONSE', 'description' => 'Awaiting response from relevant parties'],
            ['name' => 'Resolution Proposed', 'code' => 'RESOLUTION_PROPOSED', 'description' => 'Solution has been proposed'],
            ['name' => 'Resolved', 'code' => 'RESOLVED', 'description' => 'Complaint has been resolved'],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Complaint case has been closed'],
            ['name' => 'Escalated', 'code' => 'ESCALATED', 'description' => 'Complaint has been escalated to higher authority'],
            ['name' => 'On Hold', 'code' => 'ON_HOLD', 'description' => 'Complaint processing temporarily suspended'],
        ]);
    }


    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'status_id');
    }
}
