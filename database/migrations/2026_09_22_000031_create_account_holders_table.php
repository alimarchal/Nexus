<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ACCOUNT HOLDERS  (customers <-> accounts)   << DONO FORMS KA PUL >>
|--------------------------------------------------------------------------
| AOF SECTION SEQUENCE : #02 (Client/Relationship ID 1-4), #30 (Applicant's
|                        signature boxes)
| Source : AOF-Individual p.1 (Client/Relationship ID (1)..(4)) + p.14
|          (Applicant's (1)..(4) Name / Signature / Thumb Impression)
|          AOF-Entity p.1 (Client/Relationship ID (1)..(4)) + p.15 & p.16
|          (Applicant's (1)..(12) Name / Signature / Company's Stamp)
|
| ===================== YEH TABLE KYUN ZAROORI HAI =====================
| Individual form 4 applicants tak allow karta hai, Entity form 12 tak. Agar
| accounts table mein holder1_id, holder2_id... columns banate to dono forms
| ke liye alag design banana parta. Pivot se:
|   - jitne marzi holders (koi limit nahi),
|   - har holder ka apna role (joint / proprietor / guardian / authorized
|     signatory / mandate holder / trustee / office bearer),
|   - signing order aur designation bhi mehfooz.
| ======================================================================
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_holders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->restrictOnDelete();
            $table->enum('holder_role', [
                'primary',               // pehla / main account holder
                'joint',                 // Joint account holder (Individual AOF)
                'sole_proprietor',       // Sole Proprietorship
                'minor',                 // minor jiske naam account hai
                'guardian',              // minor ka guardian jo operate karta hai
                'authorized_signatory',  // Entity AOF: authorized signatories
                'mandate_holder',        // Operating Instructions = Mandate
                'agent',                 // Agents Accounts (Power of Attorney/Agency Agreement)
                'executor',              // Executors & Administrators
                'trustee',               // Trust / Board of Trustees
                'office_bearer',         // Club / Society / Association ka ohdedar
            ])->default('primary');
            $table->unsignedTinyInteger('applicant_number')->nullable()
                ->comment('Form par "Applicant\'s (1)".."(12)" ka number');
            $table->boolean('is_signatory')->default(true);
            $table->unsignedTinyInteger('signing_order')->nullable();
            $table->foreignUuid('relationship_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Primary holder ke sath rishta (joint / guardian cases)');
            $table->string('designation', 120)->nullable()
                ->comment('Entity: Director / Secretary / DDO / Trustee etc. (governing body list se)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'customer_id', 'holder_role'], 'account_holder_unique');
            $table->index(['account_id', 'is_active']);
            $table->index(['customer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_holders');
    }
};
