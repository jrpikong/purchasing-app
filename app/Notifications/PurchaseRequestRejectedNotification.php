<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseRequest $purchaseRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Purchase Request Rejected: {$this->purchaseRequest->pr_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Unfortunately, your purchase request has been rejected.")
            ->line("**PR Number:** {$this->purchaseRequest->pr_number}")
            ->line("**Purpose:** {$this->purchaseRequest->purpose}")
            ->line("**Rejected By:** {$this->purchaseRequest->finalApprover->name}")
            ->line("**Rejected At:** {$this->purchaseRequest->rejected_at->format('d M Y H:i')}")
            ->line("**Reason:** {$this->purchaseRequest->rejection_reason}")
            ->action('View Purchase Request', url("/admin/purchase-requests/{$this->purchaseRequest->id}"))
            ->line('Please review the rejection reason and take necessary actions.')
            ->error();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_number' => $this->purchaseRequest->pr_number,
            'approver_name' => $this->purchaseRequest->finalApprover->name,
            'action' => 'rejected',
            'message' => "Your PR {$this->purchaseRequest->pr_number} has been rejected",
            'reason' => $this->purchaseRequest->rejection_reason,
        ];
    }
}
