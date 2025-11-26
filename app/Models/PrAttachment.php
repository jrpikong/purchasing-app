<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PrAttachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_request_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Delete file when model is deleted
            if ($model->file_path && Storage::exists($model->file_path)) {
                Storage::delete($model->file_path);
            }
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
     * Get the uploader.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is image
     */
    public function isImage()
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->file_type, $imageTypes);
    }

    /**
     * Check if file is PDF
     */
    public function isPdf()
    {
        return $this->file_type === 'application/pdf';
    }

    /**
     * Get file icon based on type
     */
    public function getFileIconAttribute()
    {
        if ($this->isImage()) {
            return 'fa-file-image';
        } elseif ($this->isPdf()) {
            return 'fa-file-pdf';
        } elseif (str_contains($this->file_type, 'word')) {
            return 'fa-file-word';
        } elseif (str_contains($this->file_type, 'excel') || str_contains($this->file_type, 'spreadsheet')) {
            return 'fa-file-excel';
        } else {
            return 'fa-file';
        }
    }
}
