<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CUSTOMER DOCUMENTS  (checklist ke against jama shuda kaghzaat)
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #27
| Source : AOF-Individual p.12  |  AOF-Entity p.12, p.13, p.14
|          "Minimum Documentation to be Obtained" + Important Notes
|
| NOTE:
| - `document_requirements` batati hai KYA chahiye, yeh table batati hai KYA
|   mila. Branch staff ka checklist screen inhi dono ko join kar ke banti hai.
| - Document customer ke sath bhi ho sakta hai aur account ke sath bhi
|   (e.g. Board Resolution account-specific hota hai), is liye dono nullable FK.
| - Important Note (b): "must be attested by a Gazetted officer/Nazim/
|   Administrator or an officer of the bank after original seen".
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_opening_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('document_type_id')->constrained()->restrictOnDelete();
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 200)->nullable();
            $table->string('document_number', 80)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_attested')->default(false)->comment('Important Note (b)');
            $table->string('attested_by', 150)->nullable()->comment('Gazetted officer / Nazim / Administrator / Bank officer');
            $table->enum('status', ['pending', 'received', 'verified', 'waived', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_on')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['account_id', 'status']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
    }
};
