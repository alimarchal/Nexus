<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComplaintMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Complaint::with(['metrics', 'assignments', 'escalations', 'histories'])
            ->chunk(100, function ($complaints) {
                foreach ($complaints as $complaint) {
                    $metrics = $complaint->metrics()->firstOrCreate([
                        'complaint_id' => $complaint->id
                    ], [
                        'time_to_first_response' => null,
                        'time_to_resolution' => null,
                        'reopened_count' => 0,
                        'escalation_count' => 0,
                        'assignment_count' => 0,
                        'customer_satisfaction_score' => null,
                    ]);

                    $assignmentCount = $complaint->assignments->count();
                    $escalationCount = $complaint->escalations->count();
                    $reopenedCount = $complaint->histories->where('action_type', 'Reopened')->count();

                    $firstNonCreated = $complaint->histories
                        ->where('action_type', '!=', 'Created')
                        ->sortBy('performed_at')
                        ->first();
                    $timeToFirstResponse = $firstNonCreated ? $complaint->created_at->diffInMinutes($firstNonCreated->performed_at) : $metrics->time_to_first_response;

                    $timeToResolution = $metrics->time_to_resolution;
                    if (in_array($complaint->status, ['Resolved', 'Closed']) && $complaint->resolved_at) {
                        $timeToResolution = $complaint->created_at->diffInMinutes($complaint->resolved_at);
                    }

                    $metrics->update([
                        'assignment_count' => $assignmentCount,
                        'escalation_count' => $escalationCount,
                        'reopened_count' => $reopenedCount,
                        'time_to_first_response' => $timeToFirstResponse,
                        'time_to_resolution' => $timeToResolution,
                    ]);
                }
            });
    }
}
