<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            if (! Schema::hasColumn('file_management_systems', 'current_custodian_id')) {
                $table->foreignId('current_custodian_id')
                    ->nullable()
                    ->after('updated_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('file_management_systems', 'box_id')) {
                $table->foreignUuid('box_id')
                    ->nullable()
                    ->after('current_custodian_id')
                    ->constrained('boxes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('file_management_systems', 'is_archived')) {
                $table->boolean('is_archived')
                    ->default(false)
                    ->after('box_id');
            }

            if (! Schema::hasColumn('file_management_systems', 'archived_at')) {
                $table->timestamp('archived_at')
                    ->nullable()
                    ->after('is_archived');
            }

            if (! Schema::hasColumn('file_management_systems', 'position_in_box')) {
                $table->integer('position_in_box')
                    ->nullable()
                    ->after('archived_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_management_systems', function (Blueprint $table) {
            $columns = ['position_in_box', 'archived_at', 'is_archived', 'box_id', 'current_custodian_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('file_management_systems', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
