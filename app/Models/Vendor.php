<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_code',
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'contact_phone',
        'tax_number',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->vendor_code)) {
                $model->vendor_code = $model->generateVendorCode();
            }
        });
    }

    /**
     * Get the PR items from this vendor.
     */
    public function prItems()
    {
        return $this->hasMany(PrItem::class);
    }

    /**
     * Get the vendor quotations.
     */
    public function quotations()
    {
        return $this->hasMany(VendorQuotation::class);
    }

    /**
     * Generate vendor code
     */
    public function generateVendorCode()
    {
        $lastVendor = self::latest('id')->first();
        $sequence = $lastVendor ?
            intval(substr($lastVendor->vendor_code, -4)) + 1 : 1;

        return sprintf("VND%04d", $sequence);
    }

    /**
     * Get total transactions
     */
    public function getTotalTransactionsAttribute()
    {
        return $this->quotations()
            ->whereHas('purchaseRequest', function($query) {
                $query->where('status', PurchaseRequest::STATUS_COMPLETED);
            })
            ->count();
    }

    /**
     * Get total transaction amount
     */
    public function getTotalTransactionAmountAttribute()
    {
        return $this->quotations()
            ->whereHas('purchaseRequest', function($query) {
                $query->where('status', PurchaseRequest::STATUS_COMPLETED);
            })
            ->where('is_selected', true)
            ->sum('total_amount');
    }

    /**
     * Scope for active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('vendor_code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }
}
