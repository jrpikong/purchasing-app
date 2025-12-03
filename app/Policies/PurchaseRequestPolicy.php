<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User can view if:
        // - They are admin
        // - They are the requester
        // - They are assigned PIC
        // - They are current approver
        return $user->is_admin
            || $purchaseRequest->requester_id === $user->id
            || $purchaseRequest->assigned_pic_id === $user->id
            || $purchaseRequest->current_approver_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create PR
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User can update if:
        // - They are admin
        // - They are the requester AND status is draft or need_revision
        if ($user->is_admin) {
            return true;
        }

        if ($purchaseRequest->requester_id === $user->id) {
            return in_array($purchaseRequest->status->value, [
                PurchaseRequest::STATUS_DRAFT,
                PurchaseRequest::STATUS_NEED_REVISION,
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Only admin or requester (if draft) can delete
        if ($user->is_admin) {
            return true;
        }

        return $purchaseRequest->requester_id === $user->id
            && $purchaseRequest->status->value === PurchaseRequest::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can assign PIC.
     */
    public function assign(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Only admin can assign PIC
        return $user->is_admin;
    }

    /**
     * Determine whether the user can send for approval.
     */
    public function sendForApproval(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Admin or assigned PIC can send for approval
        // Status must be draft or need_revision
        if (!in_array($purchaseRequest->status->value, [
            PurchaseRequest::STATUS_DRAFT,
            PurchaseRequest::STATUS_NEED_REVISION,
        ])) {
            return false;
        }

        return $user->is_admin || $purchaseRequest->assigned_pic_id === $user->id;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // User can approve if:
        // - Status is waiting_approval
        // - User is the current approver
        return $purchaseRequest->status->value === PurchaseRequest::STATUS_WAITING_APPROVAL
            && $purchaseRequest->current_approver_id === $user->id;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Same as approve
        return $this->approve($user, $purchaseRequest);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Admin or requester can cancel
        // Cannot cancel if already completed or cancelled
        if (in_array($purchaseRequest->status->value, [
            PurchaseRequest::STATUS_COMPLETED,
            PurchaseRequest::STATUS_CANCELLED,
        ])) {
            return false;
        }

        return $user->is_admin || $purchaseRequest->requester_id === $user->id;
    }
}
