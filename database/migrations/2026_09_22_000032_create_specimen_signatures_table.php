<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SPECIMEN SIGNATURES  (SS Card)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #30
| Source : AOF-Individual p.14 -- "Applicant's Signature/Thumb Impression",
|          "Company's/Organisation's Stamp"
|          AOF-Entity p.15 & p.16 -- Applicant's (1)..(12) ke signature boxes
|          AOF Important Note (c) -- shaky/immature signature par 2 passport
|          sized attested photographs + left & right thumb impressions on SS Card
|          AOF-Individual p.12 -- "Obtaining Undertaking from Customer for
|          Acceptance of Different Specimen Signatures for Account Operation"
|          T&C clause 21 -- signature change hone par tasdeeq lazmi
|
| NOTE: Ek holder ke multiple specimen ho sakte hain (English + Urdu), is liye
| alag table. Purana specimen `is_active = false` ke sath record par rehta hai,
| delete nahi hota.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specimen_signatures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_holder_id')->constrained()->cascadeOnDelete();
            $table->string('ss_card_number', 40)->nullable()->comment('SS Card number');
            $table->unsignedTinyInteger('specimen_number')->default(1)
                ->comment('"Different Specimen Signatures" undertaking ke tehat 1 se zyada');
            $table->enum('specimen_type', ['signature', 'thumb_impression', 'stamp'])->default('signature');
            $table->string('signature_image_path', 255)->nullable();
            $table->string('thumb_left_image_path', 255)->nullable()->comment('Important Note (c)');
            $table->string('thumb_right_image_path', 255)->nullable()->comment('Important Note (c)');
            $table->string('organization_stamp_path', 255)->nullable()
                ->comment("Company's/Organisation's Stamp");
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('T&C 21: signature change ki tasdeeq');
            $table->timestamps();

            $table->index(['account_holder_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specimen_signatures');
    }
};
