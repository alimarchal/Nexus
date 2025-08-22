<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('grievance_employee_id', 50)->nullable();
            $table->string('grievance_department_position', 150)->nullable();
            $table->string('grievance_supervisor_name', 150)->nullable();
            $table->date('grievance_employment_start_date')->nullable();

            $table->string('grievance_type', 100)->nullable();
            $table->string('grievance_policy_violated', 255)->nullable();
            $table->enum('grievance_previous_attempts', ['Yes', 'No'])->nullable();
            $table->text('grievance_previous_attempts_details')->nullable();
            $table->text('grievance_desired_outcome')->nullable();

            $table->string('grievance_subject_name', 150)->nullable();
            $table->string('grievance_subject_position', 150)->nullable();
            $table->string('grievance_subject_relationship', 100)->nullable();

            $table->boolean('grievance_union_representation')->default(false);
            $table->boolean('grievance_anonymous')->default(false);
            $table->boolean('grievance_acknowledgment')->default(false);

            $table->date('grievance_first_occurred_date')->nullable();
            $table->date('grievance_most_recent_date')->nullable();
            $table->string('grievance_pattern_frequency', 50)->nullable();
            $table->string('grievance_performance_effect', 50)->nullable();

            // Optional simple index for reporting
            $table->index('grievance_type');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['grievance_type']);
            $table->dropColumn([
                'grievance_employee_id',
                'grievance_department_position',
                'grievance_supervisor_name',
                'grievance_employment_start_date',
                'grievance_type',
                'grievance_policy_violated',
                'grievance_previous_attempts',
                'grievance_previous_attempts_details',
                'grievance_desired_outcome',
                'grievance_subject_name',
                'grievance_subject_position',
                'grievance_subject_relationship',
                'grievance_union_representation',
                'grievance_anonymous',
                'grievance_acknowledgment',
                'grievance_first_occurred_date',
                'grievance_most_recent_date',
                'grievance_pattern_frequency',
                'grievance_performance_effect'
            ]);
        });
    }
};
