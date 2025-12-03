<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestAssignedNotification extends Notification implements ShouldQueue
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
        $pr = $this->purchaseRequest;
        $requesterName = $pr->requester?->name ?? '—';
        $department = $pr->department?->name ?? '—';
        $url = url("/admin/purchase-requests/{$pr->id}");

        return (new MailMessage)
            ->subject("Assigned PR: {$pr->pr_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned as PIC for a purchase request.")
            ->line("**PR Number:** {$pr->pr_number}")
            ->line("**Requester:** {$requesterName}")
            ->line("**Department:** {$department}")
            ->line("**Purpose:** " . (\Str::limit($pr->purpose ?? '-', 200)))
            ->action('Open Purchase Request', $url)
            ->line('Please review the request and take the necessary action.');
    }

    public function toArray(object $notifiable): array
    {
        $pr = $this->purchaseRequest;

        return [
            'purchase_request_id' => $pr->id,
            'pr_number' => $pr->pr_number,
            'action' => 'assigned',
            'message' => "Assigned as PIC for PR {$pr->pr_number}",
        ];
    }
}
