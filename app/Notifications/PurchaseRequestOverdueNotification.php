<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PurchaseRequest $purchaseRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pr = $this->purchaseRequest;

        return (new MailMessage)
            ->subject("Overdue approval: {$pr->pr_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("PR {$pr->pr_number} has passed its approval deadline ({$pr->approval_deadline?->format('d M Y H:i')}) and is still waiting for your decision.")
            ->line("**Requester:** " . ($pr->requester?->name ?? '—'))
            ->line("**Purpose:** " . \Str::limit($pr->purpose ?? '-', 200))
            ->line('Please review this request as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        $pr = $this->purchaseRequest;

        return [
            'purchase_request_id' => $pr->id,
            'pr_number' => $pr->pr_number,
            'action' => 'overdue',
            'message' => "PR {$pr->pr_number} is overdue for your approval",
            'approval_deadline' => $pr->approval_deadline?->toIso8601String(),
        ];
    }
}
