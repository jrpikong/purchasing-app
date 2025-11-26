<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorQuotation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_request_id',
        'vendor_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'total_amount',
        'file_path',
        'file_name',
        'is_selected',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    /**
     * Get the purchase request.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Check if quotation is valid
     */
    public function isValid()
    {
        return $this->valid_until >= now()->toDateString();
    }

    /**
     * Mark as selected
     */
    public function markAsSelected()
    {
        // First, unselect all other quotations for this PR
        self::where('purchase_request_id', $this->purchase_request_id)
            ->where('id', '!=', $this->id)
            ->update(['is_selected' => false]);

        // Then select this one
        $this->update(['is_selected' => true]);
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Scope for valid quotations
     */
    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', now()->toDateString());
    }

    /**
     * Scope for selected quotations
     */
    public function scopeSelected($query)
    {
        return $query->where('is_selected', true);
    }
}
