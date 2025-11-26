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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number', 50)->unique();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('department_id');
            $table->date('request_date');
            $table->date('required_date');
            $table->text('purpose');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', [
                'draft',
                'waiting_approval',
                'approved',
                'rejected',
                'need_revision',
                'completed',
                'cancelled'
            ])->default('draft');
            $table->unsignedBigInteger('current_approver_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('requester_id')->references('id')->on('users');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('current_approver_id')->references('id')->on('users')->onDelete('set null');

            $table->index('pr_number');
            $table->index('status');
            $table->index('request_date');
            $table->index('priority');
            $table->index(['requester_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['current_approver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
