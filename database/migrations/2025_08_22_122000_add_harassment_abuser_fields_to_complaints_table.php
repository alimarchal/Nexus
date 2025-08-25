<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('harassment_abuser_employee_number', 50)->nullable()->after('harassment_employee_phone');
            $table->string('harassment_abuser_name', 150)->nullable()->after('harassment_abuser_employee_number');
            $table->string('harassment_abuser_phone', 50)->nullable()->after('harassment_abuser_name');
            $table->string('harassment_abuser_email', 150)->nullable()->after('harassment_abuser_phone');
            $table->string('harassment_abuser_relationship', 100)->nullable()->after('harassment_abuser_email');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'harassment_abuser_employee_number',
                'harassment_abuser_name',
                'harassment_abuser_phone',
                'harassment_abuser_email',
                'harassment_abuser_relationship',
            ]);
        });
    }
};
