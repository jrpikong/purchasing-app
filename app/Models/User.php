<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\PositionEnum;
use App\Enums\RoleEnum;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'position',
        'employee_id',
        'phone',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'position' => PositionEnum::class,
        'role' => RoleEnum::class,
    ];

    /**
     * Role constants
     */
    const ROLE_REQUESTER = 'requester';
    const ROLE_ADMIN = 'admin';
    const ROLE_APPROVER = 'approver';
    const ROLE_SECTION_HEAD = 'section_head';      // TAMBAH
    const ROLE_DIVISION_HEAD = 'division_head';    // TAMBAH
    const ROLE_FINANCE_ADMIN = 'finance_admin';    // TAMBAH
    const ROLE_TREASURER = 'treasurer';            // TAMBAH
    const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Get the department that the user belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the departments where user is head.
     */
    public function headOfDepartments()
    {
        return $this->hasMany(Department::class, 'head_user_id');
    }

    /**
     * Get the purchase requests created by the user.
     */
    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'requester_id');
    }

    /**
     * Get the purchase requests where user is current approver.
     */
    public function assignedPurchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'current_approver_id');
    }

    /**
     * Get the approval histories of the user.
     */
    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class, 'approver_id');
    }

    /**
     * Get the approval levels where user is approver.
     */
    public function approvalLevels()
    {
        return $this->hasMany(ApprovalLevel::class, 'approver_id');
    }

    /**
     * Get the attachments uploaded by user.
     */
    public function uploadedAttachments()
    {
        return $this->hasMany(PrAttachment::class, 'uploaded_by');
    }

    /**
     * Get the email logs sent to user.
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'recipient_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the activity logs of the user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is approver
     */
    public function isApprover()
    {
        return in_array($this->role, [self::ROLE_APPROVER, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user can approve specific PR
     */
    public function canApprove(PurchaseRequest $pr)
    {
        return $this->isApprover() && $pr->current_approver_id === $this->id;
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for approvers
     */
    public function scopeApprovers($query)
    {
        return $query->whereIn('role', [self::ROLE_APPROVER, self::ROLE_SUPER_ADMIN]);
    }
}
