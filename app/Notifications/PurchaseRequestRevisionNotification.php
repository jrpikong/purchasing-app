<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Notifications\Concerns\RecordsEmailFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestRevisionNotification extends Notification implements ShouldQueue
{
    use Queueable, RecordsEmailFailure;

    /**
     * @param string|null $revisionNotes notes for THIS revision request only
     *        (the PR's `notes` column accumulates every past revision/cancel note)
     */
    public function __construct(
        public PurchaseRequest $purchaseRequest,
        public ?string $revisionNotes = null,
        public ?string $requestedBy = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $pr = $this->purchaseRequest;

        $mail = (new MailMessage)
            ->subject('Revision Required - PR #' . $pr->pr_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your purchase request requires revision:')
            ->line('**PR Number:** ' . $pr->pr_number)
            ->line('**Purpose:** ' . $pr->purpose)
            ->line('**Amount:** Rp ' . number_format((float) $pr->total_amount, 2, ',', '.'));

        if ($this->requestedBy) {
            $mail->line('**Requested by:** ' . $this->requestedBy);
        }

        return $mail
            ->line('**Revision Notes:** ' . ($this->revisionNotes ?: 'See details in system'))
            ->action('View PR', url('/admin/purchase-requests/' . $pr->id))
            ->line('Please make the necessary revisions, then click "Submit Ulang" on the PR.');
    }

    public function toArray($notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_number' => $this->purchaseRequest->pr_number,
            'type' => 'revision_requested',
            'message' => "PR {$this->purchaseRequest->pr_number} requires revision",
            'revision_notes' => $this->revisionNotes,
        ];
    }
}
