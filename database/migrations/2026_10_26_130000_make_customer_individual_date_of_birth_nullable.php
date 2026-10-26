<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The old paper form does not always carry a date of birth, but an approved old request now creates its customer
     * from the paper form. Allow the date to be empty; every existing value is kept.
     */
    public function up(): void
    {
        Schema::table('customer_individuals', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Left nullable: turning it back to NOT NULL would fail as soon as one customer has no date of birth.
    }
};
