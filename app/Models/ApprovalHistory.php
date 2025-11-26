<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_request_id',
        'approver_id',
        'approval_level',
        'action',
        'comments',
        'approved_at',
        'token',
        'token_expired_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approved_at' => 'datetime',
        'token_expired_at' => 'datetime',
        'approval_level' => 'integer',
    ];

    /**
     * Action constants
     */
    const ACTION_PENDING = 'pending';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_REVISED = 'revised';

    /**
     * Get the purchase request.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Check if token is valid
     */
    public function isTokenValid()
    {
        return $this->token_expired_at > now() &&
            $this->action === self::ACTION_PENDING;
    }

    /**
     * Mark as approved
     */
    public function markAsApproved($comments = null, $ipAddress = null, $userAgent = null)
    {
        $this->update([
            'action' => self::ACTION_APPROVED,
            'comments' => $comments,
            'approved_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Update PR status
        $pr = $this->purchaseRequest;

        // Check if there's a next approver
        $nextApprover = $pr->getNextApprover();

        if ($nextApprover) {
            // Move to next approver
            $pr->update([
                'current_approver_id' => $nextApprover->id,
                'status' => PurchaseRequest::STATUS_WAITING_APPROVAL,
            ]);
        } else {
            // All approvals done
            $pr->update([
                'status' => PurchaseRequest::STATUS_APPROVED,
                'approved_at' => now(),
                'current_approver_id' => null,
            ]);
        }
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected($comments = null, $ipAddress = null, $userAgent = null)
    {
        $this->update([
            'action' => self::ACTION_REJECTED,
            'comments' => $comments,
            'approved_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Update PR status
        $this->purchaseRequest->update([
            'status' => PurchaseRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $comments,
            'current_approver_id' => null,
        ]);
    }

    /**
     * Mark as need revision
     */
    public function markAsRevised($comments = null, $ipAddress = null, $userAgent = null)
    {
        $this->update([
            'action' => self::ACTION_REVISED,
            'comments' => $comments,
            'approved_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Update PR status
        $this->purchaseRequest->update([
            'status' => PurchaseRequest::STATUS_NEED_REVISION,
            'current_approver_id' => null,
        ]);
    }

    /**
     * Get action badge color
     */
    public function getActionColorAttribute()
    {
        return [
            self::ACTION_PENDING => 'warning',
            self::ACTION_APPROVED => 'success',
            self::ACTION_REJECTED => 'danger',
            self::ACTION_REVISED => 'info',
        ][$this->action] ?? 'secondary';
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        return ucfirst($this->action);
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('action', self::ACTION_PENDING);
    }

    /**
     * Scope for valid tokens
     */
    public function scopeValidToken($query)
    {
        return $query->where('token_expired_at', '>', now())
            ->where('action', self::ACTION_PENDING);
    }
}
