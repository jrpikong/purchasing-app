<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestApprovedNotification extends Notification implements ShouldQueue
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
        $approverName = $pr->finalApprover?->name ?? '—';
        $url = url("/admin/purchase-requests/{$pr->id}");

        return (new MailMessage)
            ->subject("Purchase Request Approved: {$pr->pr_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your purchase request has been approved.")
            ->line("**PR Number:** {$pr->pr_number}")
            ->line("**Approved By:** {$approverName}")
            ->line("**Approved At:** " . ($pr->approved_at?->format('d M Y H:i') ?? now()->format('d M Y H:i')))
            ->action('View Purchase Request', $url)
            ->line('You may proceed with procurement as per company procedures.');
    }

    public function toArray(object $notifiable): array
    {
        $pr = $this->purchaseRequest;

        return [
            'purchase_request_id' => $pr->id,
            'pr_number' => $pr->pr_number,
            'action' => 'approved',
            'message' => "PR {$pr->pr_number} approved by {$pr->finalApprover?->name}",
        ];
    }
}
