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
    public function getApprover($purchaseRequest = null): ?User
    {
        // Explicitly assigned users must still be active to receive approvals
        $active = fn (?User $user) => $user && $user->is_active ? $user : null;

        switch ($this->role_type) {
            case self::ROLE_TYPE_SPECIFIC_USER:
                return $active($this->approver);

            case self::ROLE_TYPE_DEPARTMENT_HEAD:
                return $active($purchaseRequest?->department?->head);

            case self::ROLE_TYPE_SECTION_HEAD:
                if ($sectionHead = $active($purchaseRequest?->department?->sectionHead)) {
                    return $sectionHead;
                }

                return $this->firstActiveWithRole(User::ROLE_SECTION_HEAD);

            case self::ROLE_TYPE_DIVISION_HEAD:
                return $this->firstActiveWithRole(User::ROLE_DIVISION_HEAD);

            case self::ROLE_TYPE_FINANCE_ADMIN:
                return $this->firstActiveWithRole(User::ROLE_FINANCE_ADMIN);

            case self::ROLE_TYPE_TREASURER:
                return $this->firstActiveWithRole(User::ROLE_TREASURER);

            case self::ROLE_TYPE_ROLE_BASED:
                return $this->role_name ? $this->firstActiveWithRole($this->role_name) : null;
        }

        return null;
    }

    /**
     * First active user holding the role — via the `role` column or a Spatie role
     * (users created in the Filament user form only get Spatie roles).
     */
    private function firstActiveWithRole(string $role): ?User
    {
        return User::query()
            ->where('is_active', true)
            ->withAppRole($role)
            ->orderBy('id')
            ->first();
    }

    /**
     * Get display name for approver
     */
    public function getApproverDisplayNameAttribute(): string
    {
        return match ($this->role_type) {
            self::ROLE_TYPE_SPECIFIC_USER  => $this->approver?->name ?? 'Not Set',
            self::ROLE_TYPE_DEPARTMENT_HEAD => 'Department Head',
            self::ROLE_TYPE_SECTION_HEAD    => 'Section Head',
            self::ROLE_TYPE_DIVISION_HEAD   => 'Division Head',
            self::ROLE_TYPE_FINANCE_ADMIN   => 'Finance Admin',
            self::ROLE_TYPE_TREASURER       => 'Treasurer',
            self::ROLE_TYPE_ROLE_BASED      => ucfirst(str_replace('_', ' ', $this->role_name ?? '')),
            default                         => 'Unknown',
        };
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
