<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pr_number',
        'requester_id',
        'department_id',
        'request_date',
        'required_date',
        'purpose',
        'total_amount',
        'status',
        'current_approver_id',
        'priority',
        'notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_WAITING_APPROVAL = 'waiting_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NEED_REVISION = 'need_revision';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->pr_number)) {
                $model->pr_number = $model->generatePRNumber();
            }
            if (empty($model->request_date)) {
                $model->request_date = now();
            }
        });
    }

    /**
     * Get the requester user.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the current approver.
     */
    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    /**
     * Get the PR items.
     */
    public function items()
    {
        return $this->hasMany(PrItem::class);
    }

    /**
     * Get the vendor quotations.
     */
    public function vendorQuotations()
    {
        return $this->hasMany(VendorQuotation::class);
    }

    /**
     * Get the approval histories.
     */
    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest approval history.
     */
    public function latestApprovalHistory()
    {
        return $this->hasOne(ApprovalHistory::class)->latestOfMany();
    }

    /**
     * Get the attachments.
     */
    public function attachments()
    {
        return $this->hasMany(PrAttachment::class);
    }

    /**
     * Get the email logs.
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Get the activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Generate PR Number
     */
    public function generatePRNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastPR = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('id')
            ->first();

        $sequence = $lastPR ?
            intval(substr($lastPR->pr_number, -4)) + 1 : 1;

        return sprintf("PR/%s/%s/%04d", $year, $month, $sequence);
    }

    /**
     * Calculate total amount from items
     */
    public function calculateTotalAmount()
    {
        $this->total_amount = $this->items->sum('total_price');
        $this->save();

        return $this->total_amount;
    }

    /**
     * Determine approval flow based on amount and department
     */
    public function determineApprovalFlow()
    {
        return ApprovalFlow::where(function($query) {
            $query->where('department_id', $this->department_id)
                ->orWhereNull('department_id');
        })
            ->where('min_amount', '<=', $this->total_amount)
            ->where('max_amount', '>=', $this->total_amount)
            ->where('is_active', true)
            ->orderBy('department_id', 'desc') // Prioritize department-specific flows
            ->first();
    }

    /**
     * Get next approver in the flow
     */
    public function getNextApprover()
    {
        $flow = $this->determineApprovalFlow();
        if (!$flow) {
            return null;
        }

        $lastApproval = $this->approvalHistories()
            ->where('action', 'approved')
            ->orderBy('approval_level', 'desc')
            ->first();

        $nextLevel = $lastApproval ? $lastApproval->approval_level + 1 : 1;

        $approvalLevel = $flow->levels()
            ->where('level_order', $nextLevel)
            ->first();

        return $approvalLevel ? $approvalLevel->getApprover() : null;
    }

    /**
     * Check if PR is editable
     */
    public function isEditable()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_NEED_REVISION
        ]);
    }

    /**
     * Check if PR is cancellable
     */
    public function isCancellable()
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ]);
    }

    /**
     * Scope for pending approval
     */
    public function scopePendingApproval($query, $approverId = null)
    {
        $query->where('status', self::STATUS_WAITING_APPROVAL);

        if ($approverId) {
            $query->where('current_approver_id', $approverId);
        }

        return $query;
    }

    /**
     * Scope for status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for department
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('request_date', [$startDate, $endDate]);
    }
}
