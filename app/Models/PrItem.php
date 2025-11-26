<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_request_id',
        'item_name',
        'description',
        'quantity',
        'unit',
        'estimated_price',
        'total_price',
        'vendor_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate total price
            $model->total_price = $model->quantity * $model->estimated_price;
        });

        static::saved(function ($model) {
            // Update PR total amount when item is saved
            $model->purchaseRequest->calculateTotalAmount();
        });

        static::deleted(function ($model) {
            // Update PR total amount when item is deleted
            $model->purchaseRequest->calculateTotalAmount();
        });
    }

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
     * Format quantity with unit
     */
    public function getFormattedQuantityAttribute()
    {
        return $this->quantity . ' ' . $this->unit;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }
}
