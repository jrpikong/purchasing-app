<?php

namespace App\Console\Commands;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestOverdueNotification;
use Illuminate\Console\Command;

class CheckOverdueApprovals extends Command
{
    protected $signature = 'approvals:check-overdue';

    protected $description = 'Notify current approvers of purchase requests that have passed their approval deadline';

    public function handle(): int
    {
        $overdue = PurchaseRequest::query()
            ->where('status', PurchaseRequestStatus::WAITING_APPROVAL->value)
            ->whereNotNull('approval_deadline')
            ->where('approval_deadline', '<', now())
            ->whereNotNull('current_approver_id')
            ->with('currentApprover')
            ->get();

        foreach ($overdue as $pr) {
            if ($pr->currentApprover) {
                $pr->currentApprover->notify(new PurchaseRequestOverdueNotification($pr));
            }
        }

        $this->info("Checked overdue approvals: {$overdue->count()} notification(s) sent.");

        return self::SUCCESS;
    }
}
