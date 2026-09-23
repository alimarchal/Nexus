<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ACCOUNT DEBIT CARDS  ("BAJK Debit Card")
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #24
| Source : AOF-Individual p.5 -- "Please tick any of the following card type
|          (for Individual & sole proprietors only)": BAJK Debit Card
|          (Enhanced ATM & POS limits), Name on Card ("Maximum length is 19
|          characters with spaces")
|
| NOTE:
| - Entity AOF par debit card ka box nahi hai. Table phir bhi generic hai aur
|   `card_types.available_for` control karta hai -- design ek hi rehta hai.
| - Card account ke kisi ek holder ke naam par jaari hota hai, is liye
|   account_holder_id bhi rakha hai (joint account mein 2 cards mumkin).
| - T&C 47: debit card ki information/OTP mobile number par jati hai.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_debit_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_holder_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Kis holder ke naam par card banega');
            $table->foreignUuid('card_type_id')->constrained()->restrictOnDelete();
            $table->string('name_on_card', 19)->comment('Name on Card -- max 19 characters with spaces');
            $table->date('requested_on')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_debit_cards');
    }
};
