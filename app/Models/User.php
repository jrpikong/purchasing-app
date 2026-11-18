<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\PositionEnum;
use App\Enums\RoleEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes,HasRoles;

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
        'membership_expired_at'
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
        'membership_expired_at' => 'datetime',
    ];

    /**
     * Required outside APP_ENV=local: without FilamentUser, Filament answers 403
     * to every logged-in user in production. Active, non-expired users may enter;
     * what they can do inside is governed by policies / Shield permissions.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active !== false && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->membership_expired_at !== null
            && now()->greaterThan($this->membership_expired_at);
    }

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



    ////baru
    ///
    ///
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'requester_id');
    }

    public function assignedPurchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'assigned_pic_id');
    }

    public function pendingApprovals(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'current_approver_id')
            ->where('status', 'waiting_approval');
    }

    public function approvedPurchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'final_approver_id');
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'actor_id');
    }
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
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * Get the email logs sent to user.
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'recipient_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || $this->hasAppRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Single source of truth for "does this user have role X".
     *
     * Spatie roles win: the Filament user form only sets Spatie roles and never the
     * `role` column, whose DB default is 'admin' — so the column is only trusted for
     * legacy users that have no Spatie role at all.
     */
    public function hasAppRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        if ($this->roles->isNotEmpty()) {
            return $this->hasRole($roles);
        }

        $column = $this->role instanceof RoleEnum ? $this->role->value : $this->role;

        return in_array($column, $roles, true);
    }

    /**
     * Users holding any of the given roles — same rule as hasAppRole().
     */
    public function scopeWithAppRole($query, string|array $roles)
    {
        $roles = (array) $roles;

        return $query->where(function ($q) use ($roles) {
            $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roles))
                ->orWhere(fn ($q) => $q->doesntHave('roles')->whereIn('role', $roles));
        });
    }

    /**
     * Keep the legacy `role` column in line with the user's Spatie role, so code and
     * reports that still read the column see the real role instead of the 'admin' default.
     */
    public function syncRoleColumnFromSpatie(): void
    {
        $names = $this->roles()->pluck('name');

        // Prefer the most privileged known role, matching RoleEnum's values
        $priority = [
            self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_TREASURER, self::ROLE_FINANCE_ADMIN,
            self::ROLE_DIVISION_HEAD, self::ROLE_SECTION_HEAD, self::ROLE_APPROVER, self::ROLE_REQUESTER,
        ];

        $role = collect($priority)->first(fn ($r) => $names->contains($r));

        if ($names->isNotEmpty() && $role === null) {
            $role = self::ROLE_REQUESTER; // only non-app roles (e.g. panel_user)
        }

        if ($role !== null) {
            $this->forceFill(['role' => $role])->saveQuietly();
        }
    }

    /**
     * Active admins (is_admin flag, admin/super_admin column role or Spatie role).
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_admin', true)
                    ->orWhere(fn ($q) => $q->withAppRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]));
            });
    }

    /**
     * Check if user is approver (any approval-capable role)
     */
    public function isApprover()
    {
        return in_array($this->role instanceof \App\Enums\RoleEnum ? $this->role->value : $this->role, [
            self::ROLE_APPROVER,
            self::ROLE_SECTION_HEAD,
            self::ROLE_DIVISION_HEAD,
            self::ROLE_FINANCE_ADMIN,
            self::ROLE_TREASURER,
            self::ROLE_SUPER_ADMIN,
        ]);
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
     * Scope for approvers (all roles that can approve)
     */
    public function scopeApprovers($query)
    {
        return $query->whereIn('role', [
            self::ROLE_APPROVER,
            self::ROLE_SECTION_HEAD,
            self::ROLE_DIVISION_HEAD,
            self::ROLE_FINANCE_ADMIN,
            self::ROLE_TREASURER,
            self::ROLE_SUPER_ADMIN,
        ]);
    }
}
