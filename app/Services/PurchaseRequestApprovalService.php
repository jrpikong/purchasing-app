<?php

namespace App\Services;

use App\Enums\PurchaseRequestStatus;
use App\Enums\RoleEnum;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\ApprovalHistory;
use App\Notifications\PurchaseRequestCreatedNotification;
use App\Notifications\PurchaseRequestAssignedNotification;
use App\Notifications\PurchaseRequestSentForApprovalNotification;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Notifications\PurchaseRequestRejectedNotification;
use App\Notifications\PurchaseRequestProgressNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class PurchaseRequestApprovalService
{
    /**
     * Create new Purchase Request and notify admins
     */
    public function create(array $data, User $requester): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $requester) {
            // Use enum for default status
            $status = PurchaseRequestStatus::DRAFT;

            // Create PR (model casts will convert enum to string if configured)
            $pr = PurchaseRequest::create([
                ...$data,
                'requester_id' => $requester->id,
                'status' => $status,
                'submitted_at' => now(),
                'submitted_from' => request()?->ip() ?? null,
            ]);

            // Log history
            $this->logHistory(
                pr: $pr,
                actor: $requester,
                action: 'created',
                fromStatus: null,
                toStatus: $status,
                comment: 'Purchase request created'
            );

            // Notify all admins
            $this->notifyUsers($this->admins(), new PurchaseRequestCreatedNotification($pr));

            return $pr->fresh();
        });
    }

    /**
     * Requester resubmits a PR after making the requested revisions.
     * Moves need_revision → in_review and tells the PIC / admin to send it for approval again.
     */
    public function resubmit(PurchaseRequest $pr, User $actor, ?string $comment = null): PurchaseRequest
    {
        if ($pr->getStatusValue() !== PurchaseRequestStatus::NEED_REVISION->value) {
            throw new Exception("Cannot resubmit. Current status: {$pr->getStatusValue()}");
        }

        if ((int) $pr->requester_id !== $actor->id) {
            throw new Exception('Only the requester can resubmit this purchase request.');
        }

        return DB::transaction(function () use ($pr, $actor, $comment) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            if ($pr->getStatusValue() !== PurchaseRequestStatus::NEED_REVISION->value) {
                throw new Exception("Request has already been processed. Current status: {$pr->getStatusValue()}");
            }

            $pr->update(['status' => PurchaseRequestStatus::IN_REVIEW]);

            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'resubmitted',
                fromStatus: PurchaseRequestStatus::NEED_REVISION,
                toStatus: PurchaseRequestStatus::IN_REVIEW,
                comment: $comment ?: 'Revised and resubmitted by requester'
            );

            // PIC / admin who sent it last; if nobody handled it yet, all admins
            $recipients = $this->processorsFor($pr);
            if ($recipients->isEmpty()) {
                $recipients = $this->admins();
            }

            $this->notifyUsers($recipients, new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::RESUBMITTED,
                ['actor' => $actor->name, 'note' => $comment]
            ), except: $actor);

            return $pr->fresh();
        });
    }

    /**
     * Assign PIC to Purchase Request
     */
    public function assignPic(PurchaseRequest $pr, User $pic, User $actor): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $pic, $actor) {
            $oldPicId = $pr->assigned_pic_id;

            $pr->update([
                'assigned_pic_id' => $pic->id,
            ]);

            // Log history (store current status as from/to)
            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'assigned_pic',
                fromStatus: $pr->getStatusValue(),
                toStatus: $pr->getStatusValue(),
                comment: "Assigned to {$pic->name}",
                nextApproverId: $pic->id,
                meta: ['old_pic_id' => $oldPicId]
            );

            // Notify the assigned PIC
            $pic->notify(new PurchaseRequestAssignedNotification($pr));

            return $pr->fresh();
        });
    }

    /**
     * Send Purchase Request for approval
     */
    public function sendForApproval(
        PurchaseRequest $pr,
        ?User $approver,
        User $actor,
        ?Carbon $deadline = null
    ): PurchaseRequest {
        // Normalize current status to string
        $currentStatus = $pr->getStatusValue();

        // Validate state (allow draft, in_review, need_revision)
        if (! in_array($currentStatus, [
            PurchaseRequestStatus::DRAFT->value,
            PurchaseRequestStatus::IN_REVIEW->value,
            PurchaseRequestStatus::NEED_REVISION->value,
        ], true)) {
            throw new Exception("Cannot send for approval. Current status: {$currentStatus}");
        }

        // Snapshot the flow (by the amount at send time) and start at its first level.
        // Re-resolved on every (re)send, so a revised amount picks the right tier.
        $flow = $pr->resolveApprovalFlow();

        if (! $flow) {
            throw new Exception('Tidak ditemukan approval flow yang sesuai untuk departemen/nominal PR ini. Hubungi admin untuk mengatur Approval Flow.');
        }

        try {
            $step = $pr->findApprovalStep($flow);
        } catch (\RuntimeException $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }

        if (! $step['level']) {
            // Every level would be decided by the requester themselves
            throw new Exception("Semua level di approval flow \"{$flow->name}\" dipegang oleh requester sendiri. Atur approval flow agar ada approver lain.");
        }

        $firstLevel = $step['level'];
        $expectedApprover = $step['approver'];

        if ($approver && (int) $expectedApprover->id !== (int) $approver->id) {
            throw new Exception("Approver tidak sesuai dengan level pertama approval flow \"{$flow->name}\".");
        }
        $approver = $expectedApprover;

        return DB::transaction(function () use ($pr, $approver, $actor, $deadline, $currentStatus, $flow, $firstLevel, $step) {
            // Generate approval token
            $token = Str::random(64);
            $tokenExpiry = now()->addDays(self::TOKEN_TTL_DAYS);

            $pr->update([
                // assign enum; model cast will persist string
                'status' => PurchaseRequestStatus::WAITING_APPROVAL,
                'current_approver_id' => $approver->id,
                'approval_flow_id' => $flow->id,
                'current_level_order' => $firstLevel->level_order,
                'current_level_started_at' => now(),
                'last_reminded_at' => null,
                'reminder_count' => 0,
                'sent_for_approval_at' => now(),
                'approval_deadline' => $deadline,
                'approval_token' => $token,
                'approval_token_expires_at' => $tokenExpiry,
            ]);

            $this->logSkippedLevels($pr, $actor, $step['skipped']);

            // Log history: from previous status -> waiting_approval
            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'sent_for_approval',
                fromStatus: $currentStatus,
                toStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                comment: "Sent to {$approver->name} for approval",
                nextApproverId: $approver->id
            );

            // Send email notification to approver (include token + expiry)
            $approver->notify(new PurchaseRequestSentForApprovalNotification($pr, $token, $tokenExpiry));

            // Let the requester know their PR is now in the approval chain
            $this->notifyUsers([$pr->requester], new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::SENT_FOR_APPROVAL,
                ['actor' => $actor->name, 'next_approver' => $approver->name]
            ), except: $actor);

            return $pr->fresh();
        });
    }

    /**
     * Approve Purchase Request (Multi-level approval support)
     */
    public function approve(
        PurchaseRequest $pr,
        User $actor,
        ?string $comment = null
    ): PurchaseRequest {
        // Validate state using normalized value
        if (! $pr->canBeApproved()) {
            $status = $pr->getStatusValue();
            throw new Exception("Cannot approve. Current status: {$status}");
        }

        // Validate approver
        if ((int)$pr->current_approver_id !== $actor->id) {
            throw new Exception("You are not authorized to approve this request.");
        }

        $this->guardAgainstSelfDecision($pr, $actor);

        return DB::transaction(function () use ($pr, $actor, $comment) {
            // Lock the row for update
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            // Double-check status after lock
            if (! $pr->canBeApproved()) {
                $status = $pr->getStatusValue();
                throw new Exception("Request has already been processed. Current status: {$status}");
            }

            $approvedLevel = $pr->current_level_order;

            // Resolve the next level BEFORE recording anything: if the chain position is
            // unknown or the next level has no active approver, stop with an error instead
            // of silently skipping levels / marking the PR fully approved.
            try {
                $step = $pr->getNextApprovalStep();
            } catch (\RuntimeException $e) {
                throw new Exception($e->getMessage(), 0, $e);
            }

            $nextLevel = $step['level'];
            $nextApprover = $step['approver'];

            // Update current level approval timestamp
            $this->updateApproverTimestamp($pr, $actor);

            $this->logSkippedLevels($pr, $actor, $step['skipped']);

            if ($nextLevel) {
                // More approval levels needed - forward to next approver
                $pr->update([
                    'current_approver_id' => $nextApprover->id,
                    'current_level_order' => $nextLevel->level_order,
                    'current_level_started_at' => now(),
                    'last_reminded_at' => null,
                    'reminder_count' => 0,
                    // Generate new token for next approver
                    'approval_token' => Str::random(64),
                    'approval_token_expires_at' => now()->addDays(self::TOKEN_TTL_DAYS),
                ]);

                // Log history
                $this->logHistory(
                    pr: $pr,
                    actor: $actor,
                    action: 'approved',
                    fromStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                    toStatus: PurchaseRequestStatus::WAITING_APPROVAL, // Still waiting
                    comment: $comment ?? 'Approved - forwarded to next level',
                    nextApproverId: $nextApprover->id
                );

                // Notify next approver
                $nextApprover->notify(new PurchaseRequestSentForApprovalNotification(
                    $pr,
                    $pr->approval_token,
                    $pr->approval_token_expires_at
                ));

                // Tell the requester their PR passed this level
                $this->notifyUsers([$pr->requester], new PurchaseRequestProgressNotification(
                    $pr,
                    PurchaseRequestProgressNotification::LEVEL_APPROVED,
                    [
                        'actor' => $actor->name,
                        'next_approver' => $nextApprover->name,
                        'level' => $approvedLevel,
                        'note' => $comment,
                    ]
                ), except: $actor);

            } else {
                // This is the final approver - mark as fully approved
                $pr->update([
                    'status' => PurchaseRequestStatus::APPROVED,
                    'approved_at' => now(),
                    'final_approver_id' => $actor->id,
                    'current_approver_id' => null,
                    'current_level_order' => null,
                    'approval_token' => null,
                    'approval_token_expires_at' => null,
                ]);

                // Log history
                $this->logHistory(
                    pr: $pr,
                    actor: $actor,
                    action: 'approved',
                    fromStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                    toStatus: PurchaseRequestStatus::APPROVED,
                    comment: $comment ?? 'Purchase request fully approved'
                );

                // Notify requester
                $pr->requester->notify(new PurchaseRequestApprovedNotification($pr));

                // Notify whoever processes the purchase (PIC / admin who sent it)
                $this->notifyUsers($this->processorsFor($pr), new PurchaseRequestProgressNotification(
                    $pr,
                    PurchaseRequestProgressNotification::APPROVED,
                    ['actor' => $actor->name, 'note' => $comment]
                ), except: $actor, alsoExcept: $pr->requester);
            }

            return $pr->fresh();
        });
    }

    /**
     * Update the appropriate approver timestamp based on actor's role
     */
    private function updateApproverTimestamp(PurchaseRequest $pr, User $actor): void
    {
        $now = now();

        $actorRole = $actor->role instanceof RoleEnum
            ? $actor->role->value
            : $actor->role;

        // Try Spatie roles as fallback
        if (empty($actorRole)) {
            $spatieRoles = $actor->getRoleNames();
            $actorRole = $spatieRoles->first();
        }

        switch ($actorRole) {
            case 'section_head':
                $pr->update([
                    'section_head_id' => $actor->id,
                    'section_head_approved_at' => $now,
                ]);
                break;

            case 'division_head':
                $pr->update([
                    'division_head_id' => $actor->id,
                    'division_head_approved_at' => $now,
                ]);
                break;

            case 'finance_admin':
                $pr->update([
                    'finance_admin_id' => $actor->id,
                    'finance_admin_approved_at' => $now,
                ]);
                break;

            case 'treasurer':
                $pr->update([
                    'treasurer_id' => $actor->id,
                    'treasurer_approved_at' => $now,
                ]);
                break;
        }
    }

    /**
     * Reject Purchase Request
     */
    public function reject(
        PurchaseRequest $pr,
        User $actor,
        string $reason,
        ?string $comment = null
    ): PurchaseRequest {
        // Validate state
        if (! $pr->canBeRejected()) {
            $status = $pr->getStatusValue();
            throw new Exception("Cannot reject. Current status: {$status}");
        }

        // Validate approver
        if ((int)$pr->current_approver_id !== $actor->id) {
            throw new Exception("You are not authorized to reject this request.");
        }

        $this->guardAgainstSelfDecision($pr, $actor);

        return DB::transaction(function () use ($pr, $actor, $reason, $comment) {
            // Lock the row for update
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            // Double-check status after lock
            if (! $pr->canBeRejected()) {
                $status = $pr->getStatusValue();
                throw new Exception("Request has already been processed. Current status: {$status}");
            }

            $pr->update([
                'status' => PurchaseRequestStatus::REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'final_approver_id' => $actor->id,
                'current_approver_id' => null,
                'current_level_order' => null,
                'approval_token' => null,
                'approval_token_expires_at' => null,
            ]);

            // Log history
            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'rejected',
                fromStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                toStatus: PurchaseRequestStatus::REJECTED,
                comment: $comment ?? $reason
            );

            // Notify requester
            $pr->requester->notify(new PurchaseRequestRejectedNotification($pr));

            $this->notifyUsers($this->processorsFor($pr), new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::REJECTED,
                ['actor' => $actor->name, 'note' => $reason]
            ), except: $actor, alsoExcept: $pr->requester);

            return $pr->fresh();
        });
    }

    /**
     * Cancel Purchase Request
     */
    public function cancel(PurchaseRequest $pr, User $actor, string $reason): PurchaseRequest
    {
        if (! $pr->isCancellable()) {
            $status = $pr->getStatusValue();
            throw new Exception("Cannot cancel. Current status: {$status}");
        }

        return DB::transaction(function () use ($pr, $actor, $reason) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            if (! $pr->isCancellable()) {
                $status = $pr->getStatusValue();
                throw new Exception("Request cannot be cancelled. Current status: {$status}");
            }

            $previousStatus = $pr->getStatusValue();
            $pendingApprover = $pr->currentApprover; // their email link stops working — tell them

            $pr->update([
                'status' => PurchaseRequestStatus::CANCELLED,
                'current_approver_id' => null,
                'current_level_order' => null,
                'approval_token' => null,
                'approval_token_expires_at' => null,
                'notes' => ($pr->notes ? $pr->notes . "\n\n" : '') . "Cancelled: " . $reason,
            ]);

            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'cancelled',
                fromStatus: $previousStatus,
                toStatus: PurchaseRequestStatus::CANCELLED,
                comment: $reason
            );

            $this->notifyUsers(
                [$pr->requester, ...$this->processorsFor($pr), $pendingApprover],
                new PurchaseRequestProgressNotification(
                    $pr,
                    PurchaseRequestProgressNotification::CANCELLED,
                    ['actor' => $actor->name, 'note' => $reason]
                ),
                except: $actor
            );

            return $pr->fresh();
        });
    }

    /**
     * Mark Purchase Request as completed
     */
    public function markCompleted(PurchaseRequest $pr, User $actor): PurchaseRequest
    {
        if ($pr->getStatusValue() !== PurchaseRequestStatus::APPROVED->value) {
            $status = $pr->getStatusValue();
            throw new Exception("Cannot mark as completed. Current status: {$status}");
        }

        return DB::transaction(function () use ($pr, $actor) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            if ($pr->getStatusValue() !== PurchaseRequestStatus::APPROVED->value) {
                $status = $pr->getStatusValue();
                throw new Exception("Request cannot be marked as completed. Current status: {$status}");
            }

            $previousStatus = $pr->getStatusValue();

            $pr->update([
                'status' => PurchaseRequestStatus::COMPLETED,
                'notes' => ($pr->notes ? $pr->notes . "\n\n" : '') .
                    "Marked as completed by {$actor->name} at " . now()->format('Y-m-d H:i'),
            ]);

            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'marked_completed',
                fromStatus: $previousStatus,
                toStatus: PurchaseRequestStatus::COMPLETED,
                comment: 'Purchase request marked as completed'
            );

            $this->notifyUsers([$pr->requester], new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::COMPLETED,
                ['actor' => $actor->name]
            ), except: $actor);

            return $pr->fresh();
        });
    }

    /**
     * Log approval history
     *
     * Accepts $fromStatus and $toStatus as either string|null or PurchaseRequestStatus|null.
     */
    private function logHistory(
        PurchaseRequest $pr,
        ?User $actor,
        string $action,
        PurchaseRequestStatus|string|null $fromStatus,
        PurchaseRequestStatus|string|null $toStatus,
        ?string $comment = null,
        ?int $nextApproverId = null,
        ?array $meta = null
    ): ApprovalHistory {
        // Normalize enums to string values for DB storage
        $normalize = function ($status) {
            if ($status instanceof PurchaseRequestStatus) {
                return $status->value;
            }
            return $status;
        };

        $from = $normalize($fromStatus);
        $to = $normalize($toStatus);

        return ApprovalHistory::create([
            'purchase_request_id' => $pr->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'comment' => $comment,
            'from_status' => $from,
            'to_status' => $to,
            'next_approver_id' => $nextApproverId,
            'acted_at' => now(),
            'meta' => $meta,
        ]);
    }

    /**
     * Validate approval token
     */
    public function validateToken(PurchaseRequest $pr, string $token): bool
    {
        if (! $pr->approval_token || ! hash_equals($pr->approval_token, $token)) {
            return false;
        }

        if ($pr->approval_token_expires_at && $pr->approval_token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Request revision for Purchase Request
     */
    public function requestRevision(
        PurchaseRequest $pr,
        User $actor,
        string $revisionNotes
    ): PurchaseRequest {
        // Validate state
        if (! $pr->canBeApproved()) {
            $status = $pr->getStatusValue();
            throw new Exception("Cannot request revision. Current status: {$status}");
        }

        // Validate approver
        if ((int)$pr->current_approver_id !== $actor->id) {
            throw new Exception("You are not authorized to request revision for this request.");
        }

        $this->guardAgainstSelfDecision($pr, $actor);

        return DB::transaction(function () use ($pr, $actor, $revisionNotes) {
            // Lock the row for update
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            // Double-check status after lock
            if (! $pr->canBeApproved()) {
                $status = $pr->getStatusValue();
                throw new Exception("Request has already been processed. Current status: {$status}");
            }

            $previousStatus = $pr->getStatusValue();

            $pr->update([
                'status' => PurchaseRequestStatus::NEED_REVISION,
                'current_approver_id' => null,
                'current_level_order' => null,
                'approval_token' => null,
                'approval_token_expires_at' => null,
                'notes' => ($pr->notes ? $pr->notes . "\n\n" : '') .
                    "Revision requested by {$actor->name}: " . $revisionNotes,
            ]);

            // Log history
            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'revision_requested',
                fromStatus: $previousStatus,
                toStatus: PurchaseRequestStatus::NEED_REVISION,
                comment: $revisionNotes
            );

            // Notify requester
            // Pass only THIS round's notes — `notes` accumulates every past revision/cancel note
            $pr->requester->notify(new \App\Notifications\PurchaseRequestRevisionNotification(
                $pr,
                $revisionNotes,
                $actor->name
            ));

            $this->notifyUsers($this->processorsFor($pr), new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::REVISION_REQUESTED,
                ['actor' => $actor->name, 'note' => $revisionNotes]
            ), except: $actor, alsoExcept: $pr->requester);

            return $pr->fresh();
        });
    }

    /**
     * Issue a fresh approval link to the current approver (e.g. the old one expired
     * or the email got lost). Invalidates the previous link.
     */
    public function resendApprovalLink(PurchaseRequest $pr, ?User $actor, bool $isReminder = false): PurchaseRequest
    {
        if (! $pr->isWaitingApproval() || ! $pr->current_approver_id) {
            throw new Exception("Cannot resend approval link. Current status: {$pr->getStatusValue()}");
        }

        return DB::transaction(function () use ($pr, $actor, $isReminder) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            $approver = $pr->currentApprover;

            if (! $pr->isWaitingApproval() || ! $approver) {
                throw new Exception("Cannot resend approval link. Current status: {$pr->getStatusValue()}");
            }

            $token = Str::random(64);
            $expiry = now()->addDays(self::TOKEN_TTL_DAYS);

            $pr->update([
                'approval_token' => $token,
                'approval_token_expires_at' => $expiry,
                ...($isReminder ? [
                    'last_reminded_at' => now(),
                    'reminder_count' => (int) $pr->reminder_count + 1,
                ] : []),
            ]);

            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: $isReminder ? 'reminder_sent' : 'approval_link_resent',
                fromStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                toStatus: PurchaseRequestStatus::WAITING_APPROVAL,
                comment: ($isReminder ? 'Reminder' : 'New approval link') . " sent to {$approver->name}",
                nextApproverId: $approver->id
            );

            $approver->notify(new PurchaseRequestSentForApprovalNotification($pr, $token, $expiry, $isReminder));

            return $pr->fresh();
        });
    }

    /**
     * Reminders have run out and the approver still hasn't acted: tell the people
     * processing the PR (PIC / sender, else all admins) once, so a human follows up.
     */
    public function escalateStalledApproval(PurchaseRequest $pr): PurchaseRequest
    {
        return DB::transaction(function () use ($pr) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            $approver = $pr->currentApprover;

            $pr->update([
                'last_reminded_at' => now(),
                'reminder_count' => self::MAX_REMINDERS + 1, // marks "escalated" — not repeated
            ]);

            $this->logHistory(
                pr: $pr,
                actor: null,
                action: 'approval_stalled',
                fromStatus: $pr->getStatusValue(),
                toStatus: $pr->getStatusValue(),
                comment: 'No decision after ' . self::MAX_REMINDERS . ' reminders — escalated to PIC/admin',
                nextApproverId: $approver?->id
            );

            $recipients = $this->processorsFor($pr);
            if ($recipients->isEmpty()) {
                $recipients = $this->admins();
            }

            $this->notifyUsers($recipients, new PurchaseRequestProgressNotification(
                $pr,
                PurchaseRequestProgressNotification::APPROVAL_STALLED,
                ['next_approver' => $approver?->name]
            ));

            return $pr->fresh();
        });
    }

    /**
     * Approval link lifetime.
     */
    public const TOKEN_TTL_DAYS = 7;

    /**
     * Automatic reminders per approval level before escalating to PIC/admin.
     */
    public const MAX_REMINDERS = 3;

    /**
     * No self-approval: the requester can never decide on their own PR.
     * (Routing already skips such levels; this guards legacy/hand-edited data.)
     */
    private function guardAgainstSelfDecision(PurchaseRequest $pr, User $actor): void
    {
        if ((int) $pr->requester_id === (int) $actor->id) {
            throw new Exception('Requester tidak dapat memutuskan (approve/reject/revisi) PR miliknya sendiri.');
        }
    }

    /**
     * Record levels skipped because their approver is the requester.
     *
     * @param \App\Models\ApprovalLevel[] $levels
     */
    private function logSkippedLevels(PurchaseRequest $pr, User $actor, array $levels): void
    {
        foreach ($levels as $level) {
            $this->logHistory(
                pr: $pr,
                actor: $actor,
                action: 'level_skipped',
                fromStatus: $pr->getStatusValue(),
                toStatus: $pr->getStatusValue(),
                comment: "Level {$level->level_order} ({$level->approver_display_name}) dilewati: approver adalah requester sendiri",
                meta: ['level_order' => $level->level_order, 'approval_level_id' => $level->id]
            );
        }
    }

    /**
     * All active admins.
     */
    private function admins(): Collection
    {
        return User::query()->admins()->get();
    }

    /**
     * The people processing this PR: the assigned PIC, otherwise the admin who
     * last sent it for approval. Empty when nobody has handled it yet.
     */
    private function processorsFor(PurchaseRequest $pr): Collection
    {
        if ($pr->assignedPic) {
            return collect([$pr->assignedPic]);
        }

        $sender = ApprovalHistory::query()
            ->where('purchase_request_id', $pr->id)
            ->where('action', 'sent_for_approval')
            ->latest('acted_at')
            ->latest('id')
            ->first()
            ?->actor;

        return collect([$sender])->filter();
    }

    /**
     * Notify each distinct user once, skipping nulls and the given users
     * (usually the actor — nobody needs an email about their own action).
     */
    private function notifyUsers(iterable $users, Notification $notification, ?User $except = null, ?User $alsoExcept = null): void
    {
        $skip = collect([$except?->id, $alsoExcept?->id])->filter()->all();

        collect($users)
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => in_array($user->id, $skip, true))
            ->each(fn (User $user) => $user->notify($notification));
    }
}
