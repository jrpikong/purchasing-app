<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index(); // who did the action
            $table->string('action'); // sent_for_approval, approved, rejected, assigned, forwarded, commented
            $table->text('comment')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->unsignedBigInteger('next_approver_id')->nullable()->index();
            $table->timestamp('acted_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('next_approver_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};
