<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dateTime('harassment_incident_date')->nullable()->after('expected_resolution_date');
            $table->string('harassment_location', 150)->nullable()->after('harassment_incident_date');
            $table->string('harassment_witnesses', 255)->nullable()->after('harassment_location');
            $table->string('harassment_reported_to', 150)->nullable()->after('harassment_witnesses');
            $table->text('harassment_details')->nullable()->after('harassment_reported_to');
            $table->boolean('harassment_confidential')->default(false)->after('harassment_details');
            $table->string('harassment_sub_category', 150)->nullable()->after('harassment_confidential');
            $table->string('harassment_employee_number', 50)->nullable()->after('harassment_sub_category');
            $table->string('harassment_employee_phone', 50)->nullable()->after('harassment_employee_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'harassment_incident_date',
                'harassment_location',
                'harassment_witnesses',
                'harassment_reported_to',
                'harassment_details',
                'harassment_confidential',
            ]);
        });
    }
};
