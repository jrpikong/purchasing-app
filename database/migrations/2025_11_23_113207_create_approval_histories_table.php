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
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('approver_id');
            $table->integer('approval_level');
            $table->enum('action', ['pending', 'approved', 'rejected', 'revised']);
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('token', 100)->unique()->nullable();
            $table->timestamp('token_expired_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users');

            $table->index('purchase_request_id');
            $table->index('approver_id');
            $table->index('action');
            $table->index('token');
            $table->index(['purchase_request_id', 'approval_level']);
            $table->index(['approver_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};
