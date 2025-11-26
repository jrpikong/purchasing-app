<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_request_id',
        'recipient_id',
        'email_type',
        'email_to',
        'email_cc',
        'email_subject',
        'email_body',
        'is_sent',
        'sent_at',
        'error_message',
        'retry_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Email type constants
     */
    const TYPE_APPROVAL_REQUEST = 'approval_request';
    const TYPE_APPROVED = 'approved';
    const TYPE_REJECTED = 'rejected';
    const TYPE_REVISED = 'revised';
    const TYPE_REMINDER = 'reminder';
    const TYPE_CANCELLED = 'cancelled';

    /**
     * Get the purchase request.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the recipient.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Mark as sent
     */
    public function markAsSent()
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'is_sent' => false,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Check if should retry
     */
    public function shouldRetry()
    {
        return !$this->is_sent && $this->retry_count < 3;
    }

    /**
     * Get email type label
     */
    public function getEmailTypeLabelAttribute()
    {
        return [
            self::TYPE_APPROVAL_REQUEST => 'Approval Request',
            self::TYPE_APPROVED => 'Approved',
            self::TYPE_REJECTED => 'Rejected',
            self::TYPE_REVISED => 'Need Revision',
            self::TYPE_REMINDER => 'Reminder',
            self::TYPE_CANCELLED => 'Cancelled',
        ][$this->email_type] ?? $this->email_type;
    }

    /**
     * Get email type color
     */
    public function getEmailTypeColorAttribute()
    {
        return [
            self::TYPE_APPROVAL_REQUEST => 'primary',
            self::TYPE_APPROVED => 'success',
            self::TYPE_REJECTED => 'danger',
            self::TYPE_REVISED => 'warning',
            self::TYPE_REMINDER => 'info',
            self::TYPE_CANCELLED => 'secondary',
        ][$this->email_type] ?? 'dark';
    }

    /**
     * Scope for pending emails
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope for sent emails
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('is_sent', false)
            ->whereNotNull('error_message');
    }
}
