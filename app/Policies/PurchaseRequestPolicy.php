<?php

namespace App\Policies;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Filament Resource already applies a visibility scope (visibleToUser),
     * but returning true here lets index page render (query will be filtered).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Admin sees everything
        if ($user->is_admin) {
            return true;
        }

        // Normalize ids to integers to avoid loose type mismatches
        $requesterId = $purchaseRequest->requester_id !== null ? (int) $purchaseRequest->requester_id : null;
        $assignedPicId = $purchaseRequest->assigned_pic_id !== null ? (int) $purchaseRequest->assigned_pic_id : null;
        $currentApproverId = $purchaseRequest->current_approver_id !== null ? (int) $purchaseRequest->current_approver_id : null;
        $finalApproverId = $purchaseRequest->final_approver_id !== null ? (int) $purchaseRequest->final_approver_id : null;
        $userId = (int) $user->id;

        // Direct involvement checks (creator, PIC, current approver, final approver)
        if (
            $requesterId === $userId
            || $assignedPicId === $userId
            || $currentApproverId === $userId
            || $finalApproverId === $userId
        ) {
            return true;
        }

        // Check approval history involvement
        $wasInvolved = $purchaseRequest->approvalHistories()
            ->where(function($query) use ($userId) {
                $query->where('actor_id', $userId)
                    ->orWhere('next_approver_id', $userId);
            })
            ->exists();

        if (! $wasInvolved) {
            Log::warning('PR view denied', [
                'user_id' => $user->id,
                'pr_id' => $purchaseRequest->id,
                'requester_id' => $purchaseRequest->requester_id,
                'assigned_pic_id' => $purchaseRequest->assigned_pic_id,
                'current_approver_id' => $purchaseRequest->current_approver_id,
                'final_approver_id' => $purchaseRequest->final_approver_id,
            ]);
        }

        return (bool) $wasInvolved;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users may create PRs
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Admin can always update
        if ($user->is_admin) {
            return true;
        }

        // Requester can update only when draft or need_revision
        if ($purchaseRequest->requester_id === $user->id) {
            $status = $this->statusValue($purchaseRequest);
            return in_array($status, [
                PurchaseRequestStatus::DRAFT->value,
                PurchaseRequestStatus::NEED_REVISION->value,
            ], true);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($user->is_admin) {
            return true;
        }

        $status = $this->statusValue($purchaseRequest);
        return $purchaseRequest->requester_id === $user->id
            && $status === PurchaseRequestStatus::DRAFT->value;
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
        $status = $this->statusValue($purchaseRequest);

        if (! in_array($status, [
            PurchaseRequestStatus::DRAFT->value,
            PurchaseRequestStatus::NEED_REVISION->value,
        ], true)) {
            return false;
        }

        // admin or assigned PIC can send for approval
        return $user->is_admin || $purchaseRequest->assigned_pic_id === $user->id;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        $status = $this->statusValue($purchaseRequest);

        \Illuminate\Support\Facades\Log::debug('Policy.approve check', [
            'user_id' => $user->id,
            'pr_id' => $purchaseRequest->id,
            'current_approver_id' => $purchaseRequest->current_approver_id,
            'status_raw' => $purchaseRequest->status,
            'status_value' => $status,
            'expected' => PurchaseRequestStatus::WAITING_APPROVAL->value,
            'result_condition' => ($status === PurchaseRequestStatus::WAITING_APPROVAL->value && $purchaseRequest->current_approver_id == $user->id),
        ]);

        return $status === PurchaseRequestStatus::WAITING_APPROVAL->value
            && $purchaseRequest->current_approver_id == $user->id;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->approve($user, $purchaseRequest);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseRequest $purchaseRequest): bool
    {
        $status = $this->statusValue($purchaseRequest);

        if (in_array($status, [
            PurchaseRequestStatus::COMPLETED->value,
            PurchaseRequestStatus::CANCELLED->value,
        ], true)) {
            return false;
        }

        return $user->is_admin || $purchaseRequest->requester_id === $user->id;
    }

    /**
     * Helper: return status as string value
     * Accepts enum instance (PurchaseRequestStatus), or string, or null.
     */
    private function statusValue(PurchaseRequest $purchaseRequest): ?string
    {
        // Prefer model helper if exists
        if (method_exists($purchaseRequest, 'getStatusValue')) {
            return $purchaseRequest->getStatusValue();
        }

        // Fallback (should rarely be used)
        $status = $purchaseRequest->status ?? null;

        if ($status instanceof PurchaseRequestStatus) {
            return $status->value;
        }

        return $status === null ? null : (string) $status;
    }
}
