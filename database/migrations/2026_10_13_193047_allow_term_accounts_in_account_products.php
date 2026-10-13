<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The paper form has a Foreign Currency "Term A/c", so products and accounts can now be of class "term".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_products', function (Blueprint $table): void {
            $table->enum('product_class', ['current', 'saving', 'term'])
                ->comment('Form headings: "Current Products" / "Saving Products" / "Term Products"')
                ->change();
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->enum('account_class', ['current', 'saving', 'term'])
                ->comment('FCY box par Current / Saving / Term ka alag tick')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('account_products', function (Blueprint $table): void {
            $table->enum('product_class', ['current', 'saving'])->change();
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->enum('account_class', ['current', 'saving'])->change();
        });
    }
};
