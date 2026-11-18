<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot which approval flow a PR was routed into and which level it is
     * currently on, so the chain advances by level order (not by matching the
     * approver's user id) and doesn't drift if total_amount changes mid-approval.
     */
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('approval_flow_id')
                ->nullable()
                ->after('current_approver_id')
                ->constrained('approval_flows')
                ->nullOnDelete();

            $table->unsignedSmallInteger('current_level_order')
                ->nullable()
                ->after('approval_flow_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approval_flow_id');
            $table->dropColumn('current_level_order');
        });
    }
};
