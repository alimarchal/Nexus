<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            // aksic_rules is created after aksics, so this foreign key can't live in the
            // create_aksics_table migration itself — it must stay a separate, later migration.
            $table->foreignId('aksic_rule_id')->nullable()->after('tehsil_id')->constrained('aksic_rules')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('aksics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aksic_rule_id');
        });
    }
};
