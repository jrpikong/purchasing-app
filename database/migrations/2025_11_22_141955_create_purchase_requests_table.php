<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            // Basic identification
            $table->string('pr_number', 50)->unique();
            $table->unsignedBigInteger('requester_id')->index(); // FK users.id
            $table->unsignedBigInteger('department_id')->index(); // FK departments.id

            // Dates & meta
            $table->date('request_date')->nullable();
            $table->date('required_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_from')->nullable(); // e.g. google_form, web

            // Purpose and amounts
            $table->text('purpose')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);

            // Vendor info
            $table->string('preferred_vendor_name')->nullable();
            $table->unsignedBigInteger('preferred_vendor_id')->nullable()->index(); // FK vendors.id (optional)
            $table->text('preferred_vendor_reason')->nullable();
            $table->string('vendor_marketplace_link_1')->nullable();
            $table->string('vendor_marketplace_link_2')->nullable();
            $table->json('quotation_files')->nullable(); // optional pointer (use attachments table instead)

            // Workflow / approval
            $table->enum('status', [
                'draft',
                'waiting_approval',
                'in_review',
                'approved',
                'rejected',
                'need_revision',
                'completed',
                'cancelled'
            ])->default('draft')->index();

            $table->unsignedBigInteger('current_approver_id')->nullable()->index(); // user id
            $table->unsignedBigInteger('assigned_pic_id')->nullable()->index(); // PIC assigned by admin
            $table->timestamp('sent_for_approval_at')->nullable();
            $table->timestamp('approval_deadline')->nullable();

            // Per-role approval tracking (optional, helpful for reporting)
            $table->unsignedBigInteger('section_head_id')->nullable()->index();
            $table->timestamp('section_head_approved_at')->nullable();

            $table->unsignedBigInteger('division_head_id')->nullable()->index();
            $table->timestamp('division_head_approved_at')->nullable();

            $table->unsignedBigInteger('finance_admin_id')->nullable()->index();
            $table->timestamp('finance_admin_approved_at')->nullable();

            $table->unsignedBigInteger('treasurer_id')->nullable()->index();
            $table->timestamp('treasurer_approved_at')->nullable();

            // final approver + timestamps
            $table->unsignedBigInteger('final_approver_id')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // rejection / notes
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            // token for signed link (optional)
            $table->string('approval_token', 120)->nullable();
            $table->timestamp('approval_token_expires_at')->nullable();

            // priority and misc
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium')->index();

            // soft deletes, timestamps
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys (optional -> ensure referenced tables exist)
            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('preferred_vendor_id')->references('id')->on('vendors')->nullOnDelete();

            $table->foreign('current_approver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_pic_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('section_head_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('division_head_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('finance_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('treasurer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('final_approver_id')->references('id')->on('users')->nullOnDelete();

            // Useful indexes (some already created above)
            $table->index(['requester_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['current_approver_id', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('purchase_requests');
    }
};
