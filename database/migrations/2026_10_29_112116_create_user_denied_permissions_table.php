<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permissions a role grants but that are switched off for one particular user
 * (Settings > Users > Edit). Nothing is stored for a user until an admin
 * revokes a role permission for them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_denied_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_denied_permissions');
    }
};
