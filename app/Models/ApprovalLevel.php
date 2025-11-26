<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLevel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_flow_id',
        'level_order',
        'approver_id',
        'role_type',
        'role_name',
        'description',
    ];

    /**
     * Role type constants
     */
    const ROLE_TYPE_SPECIFIC_USER = 'specific_user';
    const ROLE_TYPE_DEPARTMENT_HEAD = 'department_head';
    const ROLE_TYPE_SECTION_HEAD = 'section_head';        // TAMBAH
    const ROLE_TYPE_DIVISION_HEAD = 'division_head';      // TAMBAH
    const ROLE_TYPE_FINANCE_ADMIN = 'finance_admin';      // TAMBAH
    const ROLE_TYPE_TREASURER = 'treasurer';              // TAMBAH
    const ROLE_TYPE_ROLE_BASED = 'role_based';

    /**
     * Get the approval flow.
     */
    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    /**
     * Get the specific approver user.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the actual approver based on role type
     */
    public function getApprover($purchaseRequest = null)
    {
        switch ($this->role_type) {
            case self::ROLE_TYPE_SPECIFIC_USER:
                return $this->approver;

            case self::ROLE_TYPE_DEPARTMENT_HEAD:
                if ($purchaseRequest && $purchaseRequest->department) {
                    return $purchaseRequest->department->head;
                }
                break;

            case self::ROLE_TYPE_ROLE_BASED:
                // Get first active user with the specified role
                return User::where('role', $this->role_name)
                    ->where('is_active', true)
                    ->first();
        }

        return null;
    }

    /**
     * Get display name for approver
     */
    public function getApproverDisplayNameAttribute()
    {
        switch ($this->role_type) {
            case self::ROLE_TYPE_SPECIFIC_USER:
                return $this->approver ? $this->approver->name : 'Not Set';

            case self::ROLE_TYPE_DEPARTMENT_HEAD:
                return 'Department Head';

            case self::ROLE_TYPE_ROLE_BASED:
                return ucfirst(str_replace('_', ' ', $this->role_name));

            default:
                return 'Unknown';
        }
    }

    /**
     * Check if this is the final level
     */
    public function isFinalLevel()
    {
        $maxLevel = $this->approvalFlow->levels()->max('level_order');
        return $this->level_order === $maxLevel;
    }

    /**
     * Get next level
     */
    public function getNextLevel()
    {
        return $this->approvalFlow->levels()
            ->where('level_order', '>', $this->level_order)
            ->orderBy('level_order')
            ->first();
    }
}
