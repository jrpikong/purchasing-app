<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\Priority;
use App\Enums\PurchaseRequestStatus;
use App\Policies\PurchaseRequestPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(PurchaseRequestPolicy::class)]
class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // identification
        'pr_number',
        'requester_id',
        'department_id',

        // dates & meta
        'request_date',
        'required_date',
        'submitted_at',
        'submitted_from',

        // purpose & amounts
        'purpose',
        'total_amount',

        // vendor info
        'preferred_vendor_name',
        'preferred_vendor_id',
        'preferred_vendor_reason',
        'vendor_marketplace_link_1',
        'vendor_marketplace_link_2',
        'quotation_files',

        // workflow / approval
        'status',
        'current_approver_id',
        'assigned_pic_id',
        'sent_for_approval_at',
        'approval_deadline',

        // per-role tracking
        'section_head_id',
        'section_head_approved_at',
        'division_head_id',
        'division_head_approved_at',
        'finance_admin_id',
        'finance_admin_approved_at',
        'treasurer_id',
        'treasurer_approved_at',

        // final & timestamps
        'final_approver_id',
        'approved_at',
        'rejected_at',

        // rejection / notes
        'rejection_reason',
        'notes',

        // token
        'approval_token',
        'approval_token_expires_at',

        // priority
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'submitted_at' => 'datetime',
        'sent_for_approval_at' => 'datetime',
        'approval_deadline' => 'datetime',
        'section_head_approved_at' => 'datetime',
        'division_head_approved_at' => 'datetime',
        'finance_admin_approved_at' => 'datetime',
        'treasurer_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approval_token_expires_at' => 'datetime',
        'quotation_files' => 'array',
        'total_amount' => 'decimal:2',
        'status' => PurchaseRequestStatus::class,
        'priority' => Priority::class,
    ];

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_WAITING_APPROVAL = 'waiting_approval';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEED_REVISION = 'need_revision';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Priority constants
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /*
     |--------------------------------------------------------------------------
     | Boot / Events
     |--------------------------------------------------------------------------
     */

    /**
     * Boot method to auto-generate PR number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseRequest) {
            if (empty($purchaseRequest->pr_number)) {
                $purchaseRequest->pr_number = static::generatePRNumber();
            }

            if (empty($purchaseRequest->requester_id)) {
                $purchaseRequest->requester_id = auth()->id();
            }
        });
    }

    /**
     * Generate unique PR Number
     */
    public static function generatePRNumber(): string
    {
        $prefix = 'PR';
        $year = date('Y');
        $month = date('m');

        // Get last PR number for current month
        $lastPR = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPR) {
            // Extract sequence from last PR number
            preg_match('/\d{4}$/', $lastPR->pr_number, $matches);
            if (!empty($matches)) {
                $sequence = intval($matches[0]) + 1;
            }
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */
    /**
     * Get the requester user.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Preferred vendor (if registered).
     */
    public function preferredVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'preferred_vendor_id');
    }

    /**
     * Get the current approver.
     */
    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    /**
     * Assigned PIC relation.
     */
    public function assignedPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    /**
     * Section head user.
     */
    public function sectionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'section_head_id');
    }

    /**
     * Division head user.
     */
    public function divisionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'division_head_id');
    }

    /**
     * Finance admin user.
     */
    public function financeAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_admin_id');
    }

    /**
     * Treasurer user.
     */
    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }

    /**
     * Final approver.
     */
    public function finalApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approver_id');
    }

    /**
     * Get the approval histories.
     */
    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'purchase_request_id')->orderByDesc('acted_at');
    }

    /**
     * Get the attachments.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status helpers (robust with enum/string)
    |--------------------------------------------------------------------------
    */

    /**
     * Normalize status to string value (works whether $this->status is enum instance or string).
     */
    public function getStatusValue(): ?string
    {
        $s = $this->status;

        if ($s instanceof PurchaseRequestStatus) {
            return $s->value;
        }

        return $s === null ? null : (string)$s;
    }

    public function isEditable(): bool
    {
        $status = $this->getStatusValue();
        return in_array($status, [
            PurchaseRequestStatus::DRAFT->value,
            PurchaseRequestStatus::NEED_REVISION->value,
        ], true);
    }

    public function isCancellable(): bool
    {
        $status = $this->getStatusValue();
        return ! in_array($status, [
            PurchaseRequestStatus::COMPLETED->value,
            PurchaseRequestStatus::CANCELLED->value,
        ], true);
    }

    public function isDraft(): bool
    {
        return $this->getStatusValue() === PurchaseRequestStatus::DRAFT->value;
    }

    public function isWaitingApproval(): bool
    {
        return $this->getStatusValue() === PurchaseRequestStatus::WAITING_APPROVAL->value;
    }

    public function isApproved(): bool
    {
        return $this->getStatusValue() === PurchaseRequestStatus::APPROVED->value;
    }

    public function isRejected(): bool
    {
        return $this->getStatusValue() === PurchaseRequestStatus::REJECTED->value;
    }

    public function canBeApproved(): bool
    {
        return in_array($this->getStatusValue(), [
            PurchaseRequestStatus::WAITING_APPROVAL->value,
        ], true);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->getStatusValue(), [
            PurchaseRequestStatus::WAITING_APPROVAL->value,
        ], true);
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query, ?int $approverId = null)
    {
        $query->where('status', PurchaseRequestStatus::WAITING_APPROVAL->value);

        if ($approverId) {
            $query->where('current_approver_id', $approverId);
        }

        return $query;
    }

    public function scopeMyPending($query, $userId)
    {
        return $query->where('status', PurchaseRequestStatus::WAITING_APPROVAL->value)
            ->where('current_approver_id', $userId);
    }

    public function scopeStatus($query, string $status)
    {
        // accept either enum or string input
        $value = $status instanceof PurchaseRequestStatus ? $status->value : $status;
        return $query->where('status', $value);
    }

    public function scopeByStatus($query, $status)
    {
        $value = $status instanceof PurchaseRequestStatus ? $status->value : $status;
        return $query->where('status', $value);
    }

    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByRequester($query, $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('request_date', [$startDate, $endDate]);
    }
}
