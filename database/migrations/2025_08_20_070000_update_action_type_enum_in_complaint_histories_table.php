<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Extend action_type enum to include transfer actions
        DB::statement("ALTER TABLE `complaint_histories` MODIFY `action_type` ENUM(
            'Created',
            'Assigned',
            'Reassigned',
            'Status Changed',
            'Comment Added',
            'File Attached',
            'Resolved',
            'Reopened',
            'Closed',
            'Priority Changed',
            'Category Changed',
            'Feedback',
            'Escalated',
            'Branch Transfer',
            'Region Transfer',
            'Division Transfer'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Revert enum back to original set
        DB::statement("ALTER TABLE `complaint_histories` MODIFY `action_type` ENUM(
            'Created',
            'Assigned',
            'Reassigned',
            'Status Changed',
            'Comment Added',
            'File Attached',
            'Resolved',
            'Reopened',
            'Closed',
            'Priority Changed',
            'Category Changed',
            'Feedback',
            'Escalated'
        ) NOT NULL");
    }
};
