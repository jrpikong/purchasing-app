<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestCreatedNotification extends Notification implements ShouldQueue
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
            ->subject("New Purchase Request: {$this->purchaseRequest->pr_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new purchase request has been created and requires your review.")
            ->line("**PR Number:** {$this->purchaseRequest->pr_number}")
            ->line("**Requester:** {$this->purchaseRequest->requester->name}")
            ->line("**Department:** {$this->purchaseRequest->department->name}")
            ->line("**Purpose:** {$this->purchaseRequest->purpose}")
            ->line("**Required Date:** {$this->purchaseRequest->required_date->format('d M Y')}")
            ->action('View Purchase Request', url("/admin/purchase-requests/{$this->purchaseRequest->id}"))
            ->line('Please review and assign a PIC to handle this request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_number' => $this->purchaseRequest->pr_number,
            'requester_name' => $this->purchaseRequest->requester->name,
            'action' => 'created',
            'message' => "New PR {$this->purchaseRequest->pr_number} created by {$this->purchaseRequest->requester->name}",
        ];
    }
}
