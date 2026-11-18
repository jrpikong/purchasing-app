<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reminder bookkeeping per approval level (see approvals:check-overdue)
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->timestamp('current_level_started_at')->nullable()->after('current_level_order');
            $table->timestamp('last_reminded_at')->nullable()->after('current_level_started_at');
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('last_reminded_at');
        });

        // Email audit trail written by App\Listeners\LogPurchaseRequestEmail
        Schema::table('email_logs', function (Blueprint $table) {
            $table->uuid('notification_id')->nullable()->after('recipient_id')->index();
            // was an enum of 6 values — notifications now have more types than that
            $table->string('email_type', 50)->change();
            $table->text('email_body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropIndex(['notification_id']);
            $table->dropColumn('notification_id');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['current_level_started_at', 'last_reminded_at', 'reminder_count']);
        });
    }
};
