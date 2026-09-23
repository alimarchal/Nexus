<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ACCOUNT CHEQUE BOOKS  ("Cheque Book Requisition")
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #25
| Source : AOF-Individual p.5 -- Cheque Book Required Yes/No, Quantity
|          Required, No. of Leaves: 10 Leaves / 25 Leaves / 50 Leaves / Others
|          AOF-Entity p.5 -- Cheque Book Required Yes/No, Quantity Required,
|          No. of Leaves: 25 Leaves / 50 Leaves / 100 Leaves / Others
|
| NOTE: Leaves ke options dono forms mein alag hain, is liye number column
| rakha hai aur allowed values `account_products.allowed_cheque_leaves` se
| validate hoti hain -- design ek hi rehta hai.
| T&C 19: cheques sirf bank ke printed cheque par hi draw ho sakte hain.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_cheque_books', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false)->comment('Cheque Book Required: Yes / No');
            $table->unsignedSmallInteger('quantity_required')->nullable()->comment('Quantity Required');
            $table->unsignedSmallInteger('leaves_count')->nullable()->comment('No. of Leaves: 10 / 25 / 50 / 100');
            $table->string('leaves_other', 60)->nullable()->comment('Others => "(Please Specify)"');
            $table->date('requested_on')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_cheque_books');
    }
};
