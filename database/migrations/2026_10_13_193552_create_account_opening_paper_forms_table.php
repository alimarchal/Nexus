<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The old BAJK paper Account Opening Form. One row per "old" account opening request; the form itself is
 * kept apart from the new form's customer/account tables so the two never mix.
 * The Key/Secret Word is a secret, so it has its own column and is stored encrypted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_opening_paper_forms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_opening_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name', 200)->nullable()->comment('Title of account / company name, for the request list and search');
            $table->text('key_word')->nullable()->comment('Key/Secret Word, encrypted');
            $table->json('data')->nullable()->comment('Everything else the paper form holds');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_opening_paper_forms');
    }
};
