<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('recipient_id');
            $table->enum('email_type', [
                'approval_request',
                'approved',
                'rejected',
                'revised',
                'reminder',
                'cancelled'
            ]);
            $table->string('email_to', 255);
            $table->string('email_cc', 500)->nullable();
            $table->string('email_subject', 500);
            $table->text('email_body');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users');

            $table->index('purchase_request_id');
            $table->index('recipient_id');
            $table->index('email_type');
            $table->index('is_sent');
            $table->index(['is_sent', 'retry_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
