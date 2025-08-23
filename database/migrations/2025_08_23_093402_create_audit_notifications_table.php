<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->morphs('notifiable');
            $table->string('channel'); // mail, database, sms, slack
            $table->string('template')->nullable();
            $table->text('subject')->nullable();
            $table->longText('body')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['audit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_notifications');
    }
};
