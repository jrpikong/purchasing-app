<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PurchaseRequest;
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
        return $authUser->can('Update:PurchaseRequest');
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
        if ($authUser->hasRole(['admin', 'super_admin'])) {
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
        if ($authUser->hasRole(['admin', 'super_admin'])) {
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

        // Check if user is the current approver
        if ((int)$purchaseRequest->current_approver_id !== $authUser->id) {
            return false;
        }

        // Check if user has approver role
        return $authUser->hasRole([
            'section_head',
            'division_head',
            'finance_admin',
            'treasurer',
            'admin',
            'super_admin',
        ]);
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
        if ($authUser->hasRole(['admin', 'super_admin'])) {
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
        if (!$authUser->hasRole(['admin', 'super_admin'])) {
            return false;
        }

        // Must be approved first
        return $purchaseRequest->getStatusValue() === 'approved';
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

        if ((int)$purchaseRequest->current_approver_id !== $authUser->id) {
            return false;
        }

        return $authUser->hasRole([
            'section_head',
            'division_head',
            'finance_admin',
            'treasurer',
            'admin',
            'super_admin',
        ]);
    }

}