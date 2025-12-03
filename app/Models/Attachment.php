<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{

    protected $guarded = ['id'];

    protected $casts = [
        'size' => 'integer',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Helper to return full URL for file (works with local or s3)
     */
    public function url(): ?string
    {
        if (! $this->filepath) {
            return null;
        }

        return \Storage::disk($this->storage_disk)->url($this->filepath);
    }
}
