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
        'actor_id',
        'action',
        'comment',
        'from_status',
        'to_status',
        'next_approver_id',
        'acted_at',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'acted_at' => 'datetime',
        'meta'     => 'array',
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
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function nextApprover()
    {
        return $this->belongsTo(User::class, 'next_approver_id');
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
}
