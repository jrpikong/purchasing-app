<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PurchaseRequest');
    }

    public function view(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $authUser->can('View:PurchaseRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PurchaseRequest');
    }

    public function update(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Only draft / need_revision PRs may be edited — editing while waiting for
        // approval (or after) would let amount/content change under the approvers.
        return $authUser->can('Update:PurchaseRequest')
            && $purchaseRequest->isEditable();
    }

    public function delete(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $authUser->can('Delete:PurchaseRequest');
    }

    public function restore(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $authUser->can('Restore:PurchaseRequest');
    }

    public function forceDelete(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $authUser->can('ForceDelete:PurchaseRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PurchaseRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PurchaseRequest');
    }

    public function replicate(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $authUser->can('Replicate:PurchaseRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PurchaseRequest');
    }

    /**
     * Determine if user can assign PIC
     */
    public function assign(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Only admins can assign PIC
        if ($this->isAdmin($authUser)) {
            return $purchaseRequest->isDraft() || $purchaseRequest->isWaitingApproval();
        }
        return false;
    }

    /**
     * Determine if user can send for approval
     */
    public function sendForApproval(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Only admins can send for approval
        if ($this->isAdmin($authUser)) {
            return in_array($purchaseRequest->getStatusValue(), [
                'draft',
                'in_review',
                'need_revision',
            ], true);
        }
        return false;
    }

    /**
     * Determine if user can approve
     */
    public function approve(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Check if status allows approval
        if (!$purchaseRequest->canBeApproved()) {
            return false;
        }

        // Being the assigned current approver is what grants the right to decide —
        // a role whitelist would lock out "specific user" / "role based" levels
        // whose approver holds a role outside it (e.g. "approver").
        return $this->isCurrentApproverButNotRequester($authUser, $purchaseRequest);
    }

    /**
     * Determine if user can reject
     */
    public function reject(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Same logic as approve
        return $this->approve($authUser, $purchaseRequest);
    }

    /**
     * Determine if user can cancel
     */
    public function cancel(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Admin can cancel any PR that's not completed or already cancelled
        if ($this->isAdmin($authUser)) {
            return $purchaseRequest->isCancellable();
        }

        // Requester can cancel their own PR if it's still in draft/revision
        if ((int)$purchaseRequest->requester_id === $authUser->id) {
            return in_array($purchaseRequest->getStatusValue(), [
                'draft',
                'need_revision',
            ], true);
        }

        return false;
    }

    /**
     * Determine if user can mark as completed
     */
    public function markCompleted(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Only admins can mark as completed
        if (!$this->isAdmin($authUser)) {
            return false;
        }

        // Must be approved first
        return $purchaseRequest->getStatusValue() === 'approved';
    }

    /**
     * Determine if user can resubmit after revision (requester only)
     */
    public function resubmit(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return (int) $purchaseRequest->requester_id === $authUser->id
            && $purchaseRequest->getStatusValue() === 'need_revision';
    }

    /**
     * Determine if user can resend the approval email to the current approver (admin only)
     */
    public function resendApprovalLink(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return $this->isAdmin($authUser)
            && $purchaseRequest->isWaitingApproval()
            && $purchaseRequest->current_approver_id !== null;
    }

    /**
     * Determine if user can request revision
     */
    public function requestRevision(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        // Approvers can request revision instead of rejecting
        if (!$purchaseRequest->canBeApproved()) {
            return false;
        }

        return $this->isCurrentApproverButNotRequester($authUser, $purchaseRequest);
    }

    /**
     * The current approver may decide — unless they requested the PR themselves
     * (no self-approval; the service skips such levels when routing).
     */
    private function isCurrentApproverButNotRequester(AuthUser $authUser, PurchaseRequest $purchaseRequest): bool
    {
        return (int) $purchaseRequest->current_approver_id === (int) $authUser->id
            && (int) $purchaseRequest->requester_id !== (int) $authUser->id;
    }

    private function isAdmin(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isAdmin();
    }
}