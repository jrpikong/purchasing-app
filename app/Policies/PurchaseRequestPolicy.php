<?php

namespace App\Policies;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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

        // Direct involvement checks
        if (
            $purchaseRequest->requester_id === $user->id
            || $purchaseRequest->assigned_pic_id === $user->id
            || $purchaseRequest->current_approver_id === $user->id
            || $purchaseRequest->final_approver_id === $user->id
        ) {
            return true;
        }

        // In approval history?
        $wasInvolved = $purchaseRequest->approvalHistories()
            ->where(function ($query) use ($user) {
                $query->where('actor_id', $user->id)
                    ->orWhere('next_approver_id', $user->id);
            })
            ->exists();

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

        return $status === PurchaseRequestStatus::WAITING_APPROVAL->value
            && $purchaseRequest->current_approver_id === $user->id;
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
