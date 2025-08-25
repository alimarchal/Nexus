<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComplaintCategory;

class ComplaintCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Service Issue',
                'description' => 'Issues related to service quality or delivery delays',
                'default_priority' => 'Medium',
                'sla_hours' => 48,
                'is_active' => true,
            ],
            [
                'category_name' => 'Product Quality',
                'description' => 'Complaints concerning product defects or quality concerns',
                'default_priority' => 'High',
                'sla_hours' => 24,
                'is_active' => true,
            ],
            [
                'category_name' => 'Billing Error',
                'description' => 'Incorrect charges, invoices or payment issues',
                'default_priority' => 'High',
                'sla_hours' => 12,
                'is_active' => true,
            ],
            [
                'category_name' => 'Technical Problem',
                'description' => 'System outages, application bugs or login/access problems',
                'default_priority' => 'Critical',
                'sla_hours' => 4,
                'is_active' => true,
            ],
            [
                'category_name' => 'Feedback / Suggestion',
                'description' => 'Non-urgent customer suggestions or general feedback',
                'default_priority' => 'Low',
                'sla_hours' => 72,
                'is_active' => true,
            ],
            [
                'category_name' => 'Harassment',
                'description' => 'Reports related to harassment, abuse, or inappropriate conduct requiring sensitive handling. Escalates to senior management; ensure clear supporting evidence (screenshots, emails, logs) is attached and incident details are documented professionally.',
                'default_priority' => 'High',
                'sla_hours' => 12, // escalate faster than standard service issues
                'is_active' => true,
            ],
        ];

        foreach ($categories as $data) {
            ComplaintCategory::firstOrCreate(
                ['category_name' => $data['category_name'], 'complaint_id' => null],
                array_merge($data, ['complaint_id' => null])
            );
        }
    }
}
