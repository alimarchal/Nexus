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
        Schema::table('aksic_applications', function (Blueprint $table) {
            $table->text('challan_image_url')->nullable()->comment('Original API URL for challan image');
            $table->text('cnic_front_url')->nullable()->comment('Original API URL for CNIC front image');
            $table->text('cnic_back_url')->nullable()->comment('Original API URL for CNIC back image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aksic_applications', function (Blueprint $table) {
            $table->dropColumn('challan_image_url');
            $table->dropColumn('cnic_front_url');
            $table->dropColumn('cnic_back_url');
        });
    }
};
