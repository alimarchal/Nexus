<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed', 'Reopened'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $sources = ['Phone', 'Email', 'Portal', 'Walk-in', 'Other'];
        $categories = ['Service Quality', 'Billing', 'Product', 'Staff Behavior', 'Technical', 'Policy', 'Harassment', 'Grievance'];
        
        $status = $this->faker->randomElement($statuses);
        $isResolved = in_array($status, ['Resolved', 'Closed']);
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement($categories),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $status,
            'source' => $this->faker->randomElement($sources),
            'complainant_name' => $this->faker->name(),
            'complainant_email' => $this->faker->safeEmail(),
            'complainant_phone' => $this->faker->phoneNumber(),
            'complainant_account_number' => $this->faker->numerify('ACC-########'),
            'expected_resolution_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'sla_breached' => $this->faker->boolean(20), // 20% chance of SLA breach
            'resolution' => $isResolved ? $this->faker->paragraph() : null,
            'resolved_at' => $isResolved ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'closed_at' => $status === 'Closed' ? $this->faker->dateTimeBetween('-20 days', 'now') : null,
        ];
    }

    /**
     * Create a harassment complaint with specific fields
     */
    public function harassment(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Harassment',
                'harassment_incident_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'harassment_location' => $this->faker->address(),
                'harassment_witnesses' => $this->faker->name() . ', ' . $this->faker->name(),
                'harassment_reported_to' => $this->faker->name(),
                'harassment_details' => $this->faker->paragraphs(3, true),
                'harassment_confidential' => $this->faker->boolean(),
                'harassment_sub_category' => $this->faker->randomElement(['Sexual', 'Verbal', 'Physical', 'Psychological']),
                'harassment_employee_number' => $this->faker->numerify('EMP-####'),
                'harassment_employee_phone' => $this->faker->phoneNumber(),
                'harassment_abuser_employee_number' => $this->faker->numerify('EMP-####'),
                'harassment_abuser_name' => $this->faker->name(),
                'harassment_abuser_phone' => $this->faker->phoneNumber(),
                'harassment_abuser_email' => $this->faker->safeEmail(),
                'harassment_abuser_relationship' => $this->faker->randomElement(['Supervisor', 'Colleague', 'Subordinate', 'Client', 'Other']),
            ];
        });
    }

    /**
     * Create a grievance complaint with specific fields
     */
    public function grievance(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Grievance',
                'grievance_employee_id' => $this->faker->numerify('EMP-####'),
                'grievance_department_position' => $this->faker->jobTitle(),
                'grievance_supervisor_name' => $this->faker->name(),
                'grievance_employment_start_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
                'grievance_type' => $this->faker->randomElement(['Discrimination', 'Workplace Policy', 'Working Conditions', 'Benefits', 'Other']),
                'grievance_policy_violated' => 'Policy Section ' . $this->faker->numberBetween(1, 20),
                'grievance_previous_attempts' => $this->faker->randomElement(['Yes', 'No']),
                'grievance_previous_attempts_details' => $this->faker->paragraph(),
                'grievance_desired_outcome' => $this->faker->sentence(),
                'grievance_subject_name' => $this->faker->name(),
                'grievance_subject_position' => $this->faker->jobTitle(),
                'grievance_subject_relationship' => $this->faker->randomElement(['Supervisor', 'Colleague', 'HR', 'Management']),
                'grievance_union_representation' => $this->faker->boolean(),
                'grievance_anonymous' => $this->faker->boolean(),
                'grievance_acknowledgment' => true,
                'grievance_first_occurred_date' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
                'grievance_most_recent_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'grievance_pattern_frequency' => $this->faker->randomElement(['Daily', 'Weekly', 'Monthly', 'Rarely']),
                'grievance_performance_effect' => $this->faker->randomElement(['None', 'Minor', 'Moderate', 'Significant']),
            ];
        });
    }

    /**
     * Create an assigned complaint
     */
    public function assigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }

    /**
     * Create an overdue complaint
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'In Progress',
                'expected_resolution_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
                'resolved_at' => null,
                'sla_breached' => true,
            ];
        });
    }

    /**
     * Create a high priority complaint
     */
    public function critical(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'Critical',
                'expected_resolution_date' => $this->faker->dateTimeBetween('now', '+1 day'),
            ];
        });
    }

    /**
     * Create a resolved complaint
     */
    public function resolved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Resolved',
                'resolution' => $this->faker->paragraphs(2, true),
                'resolved_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            ];
        });
    }

    /**
     * Create a closed complaint
     */
    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Closed',
                'resolution' => $this->faker->paragraphs(2, true),
                'resolved_at' => $this->faker->dateTimeBetween('-20 days', '-10 days'),
                'closed_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            ];
        });
    }
}
