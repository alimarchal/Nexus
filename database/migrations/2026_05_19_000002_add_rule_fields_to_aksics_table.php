<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->foreignId('aksic_rule_id')->nullable()->after('tehsil_id')->constrained('aksic_rules')->nullOnDelete()->cascadeOnUpdate();
            $table->string('gender')->nullable()->after('quota');
            $table->boolean('is_startup_business')->default(false)->after('business_type');
            $table->boolean('site_visit_completed')->default(false)->after('sanction_date');
            $table->date('site_visit_date')->nullable()->after('site_visit_completed');
        });
    }

    public function down(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aksic_rule_id');
            $table->dropColumn(['gender', 'is_startup_business', 'site_visit_completed', 'site_visit_date']);
        });
    }
};
