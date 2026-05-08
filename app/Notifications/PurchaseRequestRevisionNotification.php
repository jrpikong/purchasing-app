<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestRevisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseRequest $purchaseRequest
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Revision Required - PR #' . $this->purchaseRequest->pr_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your purchase request requires revision:')
            ->line('**PR Number:** ' . $this->purchaseRequest->pr_number)
            ->line('**Purpose:** ' . $this->purchaseRequest->purpose)
            ->line('**Amount:** Rp ' . number_format((float)$this->purchaseRequest->total_amount, 2, ',', '.'))
            ->line('**Revision Notes:** ' . ($this->purchaseRequest->notes ?? 'See details in system'))
            ->action('View PR', url('/admin/purchase-requests/' . $this->purchaseRequest->id))
            ->line('Please make the necessary revisions and resubmit.');
    }

    public function toArray($notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_number' => $this->purchaseRequest->pr_number,
            'type' => 'revision_requested',
            'message' => "PR {$this->purchaseRequest->pr_number} requires revision",
        ];
    }
}
