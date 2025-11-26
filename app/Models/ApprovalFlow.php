<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'min_amount',
        'max_amount',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the approval levels.
     */
    public function levels()
    {
        return $this->hasMany(ApprovalLevel::class)->orderBy('level_order');
    }

    /**
     * Check if flow is applicable for amount
     */
    public function isApplicableForAmount($amount)
    {
        return $amount >= $this->min_amount && $amount <= $this->max_amount;
    }

    /**
     * Get total levels count
     */
    public function getTotalLevelsAttribute()
    {
        return $this->levels()->count();
    }

    /**
     * Duplicate flow with levels
     */
    public function duplicate($name = null)
    {
        $newFlow = $this->replicate();
        $newFlow->name = $name ?: $this->name . ' (Copy)';
        $newFlow->save();

        foreach ($this->levels as $level) {
            $newLevel = $level->replicate();
            $newLevel->approval_flow_id = $newFlow->id;
            $newLevel->save();
        }

        return $newFlow;
    }

    /**
     * Scope for active flows
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for department
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
                ->orWhereNull('department_id');
        });
    }

    /**
     * Scope for amount range
     */
    public function scopeForAmount($query, $amount)
    {
        return $query->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount);
    }
}
