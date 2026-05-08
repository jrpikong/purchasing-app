<?php

namespace App\Services;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\ApprovalHistory;
use App\Notifications\PurchaseRequestCreatedNotification;
use App\Notifications\PurchaseRequestAssignedNotification;
use App\Notifications\PurchaseRequestSentForApprovalNotification;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Notifications\PurchaseRequestRejectedNotification;
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

            // Notify all admins (customize role check as needed)
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new PurchaseRequestCreatedNotification($pr));
            }

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
        User $approver,
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

        return DB::transaction(function () use ($pr, $approver, $actor, $deadline, $currentStatus) {
            // Generate approval token
            $token = Str::random(64);
            $tokenExpiry = now()->addDays(7); // Token valid for 7 days

            $pr->update([
                // assign enum; model cast will persist string
                'status' => PurchaseRequestStatus::WAITING_APPROVAL,
                'current_approver_id' => $approver->id,
                'sent_for_approval_at' => now(),
                'approval_deadline' => $deadline,
                'approval_token' => $token,
                'approval_token_expires_at' => $tokenExpiry,
            ]);

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

        return DB::transaction(function () use ($pr, $actor, $comment) {
            // Lock the row for update
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();

            // Double-check status after lock
            if (! $pr->canBeApproved()) {
                $status = $pr->getStatusValue();
                throw new Exception("Request has already been processed. Current status: {$status}");
            }

            // Update current level approval timestamp
            $this->updateApproverTimestamp($pr, $actor);

            // Find next approver
            $nextApprover = $pr->getNextApprover();

            if ($nextApprover) {
                // More approval levels needed - forward to next approver
                $pr->update([
                    'current_approver_id' => $nextApprover->id,
                    // Generate new token for next approver
                    'approval_token' => Str::random(64),
                    'approval_token_expires_at' => now()->addDays(7),
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

            } else {
                // This is the final approver - mark as fully approved
                $pr->update([
                    'status' => PurchaseRequestStatus::APPROVED,
                    'approved_at' => now(),
                    'final_approver_id' => $actor->id,
                    'current_approver_id' => null,
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

        // Get role value - handle both enum and string
        $actorRole = $actor->role;
        if (is_object($actorRole) && method_exists($actorRole, 'value')) {
            $actorRole = $actorRole->value;
        } elseif (is_object($actorRole)) {
            $actorRole = (string)$actorRole;
        }

        // Try Spatie roles as fallback
        if (empty($actorRole) || $actorRole === null) {
            $spatieRoles = $actor->getRoleNames();
            $actorRole = $spatieRoles->first() ?? $actorRole;
        }

        // Update based on role type
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
        User $actor,
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
            'actor_id' => $actor->id,
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
        if ($pr->approval_token !== $token) {
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
            $pr->requester->notify(new \App\Notifications\PurchaseRequestRevisionNotification($pr));

            return $pr->fresh();
        });
    }
}
