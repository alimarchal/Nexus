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
        DB::table('complaint_status_types')->insert([
            ['name' => 'Open', 'code' => 'OPEN', 'description' => 'Complaint is open and awaiting action'],
            ['name' => 'In Progress', 'code' => 'IN_PROGRESS', 'description' => 'Complaint is actively being worked on'],
            ['name' => 'Pending', 'code' => 'PENDING', 'description' => 'Complaint is pending further action or information'],
            ['name' => 'Resolved', 'code' => 'RESOLVED', 'description' => 'Complaint has been resolved'],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Complaint case has been closed'],
            ['name' => 'Reopened', 'code' => 'REOPENED', 'description' => 'Previously closed complaint has been reopened'],
        ]);
    }


    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'status_id');
    }
}
