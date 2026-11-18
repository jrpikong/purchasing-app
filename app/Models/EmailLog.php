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
        'notification_id',
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
        // Keep showing the PR number even if the PR was soft-deleted
        return $this->belongsTo(PurchaseRequest::class)->withTrashed();
    }

    /**
     * Get the recipient.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id')->withTrashed();
    }

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';

    /**
     * sent → delivered; failed → error recorded; pending → queued / in flight.
     */
    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->is_sent => self::STATUS_SENT,
            filled($this->error_message) => self::STATUS_FAILED,
            default => self::STATUS_PENDING,
        };
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_PENDING => 'Menunggu',
        ];
    }

    /**
     * Human labels for every email_type the app writes (see EmailLogRecorder::typeOf()).
     */
    public static function typeLabels(): array
    {
        return [
            'created' => 'PR Baru (ke Admin)',
            'assigned' => 'PIC Ditugaskan',
            self::TYPE_APPROVAL_REQUEST => 'Permintaan Approval',
            self::TYPE_REMINDER => 'Reminder Approval',
            self::TYPE_APPROVED => 'Approved (ke Requester)',
            self::TYPE_REJECTED => 'Rejected (ke Requester)',
            self::TYPE_REVISED => 'Perlu Revisi (ke Requester)',
            self::TYPE_CANCELLED => 'Dibatalkan',
            'progress_sent_for_approval' => 'Progres: Dikirim ke Approval',
            'progress_level_approved' => 'Progres: Lolos Satu Level',
            'progress_approved' => 'Progres: Final Approved (ke PIC)',
            'progress_rejected' => 'Progres: Ditolak (ke PIC)',
            'progress_revision_requested' => 'Progres: Diminta Revisi (ke PIC)',
            'progress_resubmitted' => 'Progres: Submit Ulang',
            'progress_cancelled' => 'Progres: Dibatalkan',
            'progress_completed' => 'Progres: Selesai',
            'progress_approval_stalled' => 'Eskalasi: Approval Macet',
        ];
    }

    public function scopeStatus($query, string $status)
    {
        return match ($status) {
            self::STATUS_SENT => $query->where('is_sent', true),
            self::STATUS_FAILED => $query->where('is_sent', false)->whereNotNull('error_message'),
            self::STATUS_PENDING => $query->where('is_sent', false)->whereNull('error_message'),
            default => $query,
        };
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
        return static::typeLabels()[$this->email_type]
            ?? \Illuminate\Support\Str::headline((string) $this->email_type);
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
