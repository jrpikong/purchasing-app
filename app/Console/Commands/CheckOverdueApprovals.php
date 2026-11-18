<?php

namespace App\Console\Commands;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Daily approval follow-up (scheduled in routes/console.php):
 *
 *  - Reminds the current approver — with a fresh approval link — when the PR is past its
 *    deadline, has waited STALE_AFTER_HOURS without a deadline, or its link is about to expire.
 *  - At most once per MIN_HOURS_BETWEEN and MAX_REMINDERS times per level; after that it
 *    escalates once to the PIC / sender / admins instead of emailing the approver forever.
 */
class CheckOverdueApprovals extends Command
{
    protected $signature = 'approvals:check-overdue';

    protected $description = 'Remind approvers of pending purchase requests (overdue, stale or expiring link) and escalate stalled ones';

    public const STALE_AFTER_HOURS = 48;
    public const MIN_HOURS_BETWEEN = 20;
    public const REFRESH_LINK_BEFORE_HOURS = 24;

    public function handle(PurchaseRequestApprovalService $service): int
    {
        $now = now();
        $reminded = $escalated = $refreshed = $failed = 0;

        PurchaseRequest::query()
            ->where('status', PurchaseRequestStatus::WAITING_APPROVAL->value)
            ->whereNotNull('current_approver_id')
            ->with('currentApprover')
            ->chunkById(100, function ($prs) use ($service, $now, &$reminded, &$escalated, &$refreshed, &$failed) {
                foreach ($prs as $pr) {
                    if (! $pr->currentApprover) {
                        continue;
                    }

                    if ($pr->last_reminded_at && $pr->last_reminded_at->gt($now->copy()->subHours(self::MIN_HOURS_BETWEEN))) {
                        continue;
                    }

                    $since = $pr->current_level_started_at ?? $pr->sent_for_approval_at ?? $pr->updated_at;
                    $overdue = $pr->approval_deadline && $pr->approval_deadline->isPast();
                    $stale = ! $pr->approval_deadline && $since && $since->lte($now->copy()->subHours(self::STALE_AFTER_HOURS));
                    $linkExpiring = ! $pr->approval_token_expires_at
                        || $pr->approval_token_expires_at->lte($now->copy()->addHours(self::REFRESH_LINK_BEFORE_HOURS));

                    $count = (int) $pr->reminder_count;

                    try {
                        if ($count < PurchaseRequestApprovalService::MAX_REMINDERS && ($overdue || $stale || $linkExpiring)) {
                            $service->resendApprovalLink($pr, null, isReminder: true);
                            $reminded++;
                        } elseif ($count === PurchaseRequestApprovalService::MAX_REMINDERS && ($overdue || $stale)) {
                            $service->escalateStalledApproval($pr);
                            $escalated++;
                        } elseif ($linkExpiring) {
                            // Reminders exhausted, but keep the approver's link usable
                            $service->resendApprovalLink($pr, null);
                            $refreshed++;
                        }
                    } catch (Throwable $e) {
                        $failed++;
                        report($e);
                        $this->error("PR {$pr->pr_number}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Approval follow-up: {$reminded} reminder(s), {$escalated} escalation(s), {$refreshed} link refresh(es), {$failed} failure(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
