<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Notifications\Concerns\RecordsEmailFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Keeps requesters and the people processing a PR (PIC / admin who sent it)
 * informed of progress they don't otherwise get an email for.
 *
 * Anything that may have changed by the time the queued job runs (actor, next
 * approver, notes) is passed in $context instead of being read from the PR,
 * because the PR is re-fetched from the database when the job is handled.
 */
class PurchaseRequestProgressNotification extends Notification implements ShouldQueue
{
    use Queueable, RecordsEmailFailure;

    public const SENT_FOR_APPROVAL  = 'sent_for_approval';   // → requester
    public const LEVEL_APPROVED     = 'level_approved';      // → requester
    public const APPROVED           = 'approved';            // → PIC / sender
    public const REJECTED           = 'rejected';            // → PIC / sender
    public const REVISION_REQUESTED = 'revision_requested';  // → PIC / sender
    public const RESUBMITTED        = 'resubmitted';         // → PIC / sender / admins
    public const CANCELLED          = 'cancelled';           // → requester, PIC, current approver
    public const COMPLETED          = 'completed';           // → requester
    public const APPROVAL_STALLED   = 'approval_stalled';    // → PIC / sender / admins, after reminders run out

    /**
     * @param array{actor?: string, next_approver?: string, note?: string, level?: int} $context
     */
    public function __construct(
        public PurchaseRequest $purchaseRequest,
        public string $event,
        public array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pr = $this->purchaseRequest;
        $actor = $this->context['actor'] ?? '—';
        $next = $this->context['next_approver'] ?? '—';
        $note = $this->context['note'] ?? null;

        $mail = (new MailMessage)
            ->subject($this->subject())
            ->greeting("Hello {$notifiable->name},");

        match ($this->event) {
            self::SENT_FOR_APPROVAL => $mail
                ->line('Your purchase request has been sent for approval.')
                ->line("**Waiting for:** {$next}"),
            self::LEVEL_APPROVED => $mail
                ->line('Your purchase request passed an approval level and has been forwarded to the next approver.')
                ->line("**Approved by:** {$actor}")
                ->line("**Next approver:** {$next}"),
            self::APPROVED => $mail
                ->line('A purchase request you are handling has been fully approved and is ready for procurement.')
                ->line("**Final approver:** {$actor}"),
            self::REJECTED => $mail
                ->line('A purchase request you are handling has been rejected.')
                ->line("**Rejected by:** {$actor}"),
            self::REVISION_REQUESTED => $mail
                ->line('A purchase request you are handling has been returned to the requester for revision.')
                ->line("**Requested by:** {$actor}"),
            self::RESUBMITTED => $mail
                ->line('A purchase request has been revised by the requester and is ready to be reviewed and sent for approval again.')
                ->line("**Resubmitted by:** {$actor}"),
            self::CANCELLED => $mail
                ->line('A purchase request has been cancelled.')
                ->line("**Cancelled by:** {$actor}"),
            self::COMPLETED => $mail
                ->line('Your purchase request has been marked as completed.')
                ->line("**Completed by:** {$actor}"),
            self::APPROVAL_STALLED => $mail
                ->line('A purchase request is still waiting for approval after several reminders. Please follow up with the approver, or resend the approval link from the PR page.')
                ->line("**Waiting for:** {$next}"),
            default => $mail->line('There is an update on a purchase request.'),
        };

        $mail->line("**PR Number:** {$pr->pr_number}")
            ->line('**Requester:** ' . ($pr->requester?->name ?? '—'))
            ->line('**Purpose:** ' . Str::limit($pr->purpose ?? '-', 200));

        if ($note) {
            $mail->line('**' . $this->noteLabel() . ':** ' . Str::limit($note, 1000));
        }

        $mail->action('View Purchase Request', url("/admin/purchase-requests/{$pr->id}"));

        if ($this->event === self::REJECTED || $this->event === self::CANCELLED) {
            $mail->error();
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_number' => $this->purchaseRequest->pr_number,
            'action' => $this->event,
            'message' => $this->subject(),
            'context' => $this->context,
        ];
    }

    private function subject(): string
    {
        $no = $this->purchaseRequest->pr_number;

        return match ($this->event) {
            self::SENT_FOR_APPROVAL  => "PR {$no} is waiting for approval",
            self::LEVEL_APPROVED     => "PR {$no} approved at level " . ($this->context['level'] ?? '?') . ' — forwarded to next approver',
            self::APPROVED           => "PR {$no} fully approved — ready for procurement",
            self::REJECTED           => "PR {$no} has been rejected",
            self::REVISION_REQUESTED => "PR {$no} returned for revision",
            self::RESUBMITTED        => "PR {$no} revised and resubmitted",
            self::CANCELLED          => "PR {$no} has been cancelled",
            self::COMPLETED          => "PR {$no} has been completed",
            self::APPROVAL_STALLED   => "PR {$no} is stuck waiting for approval",
            default                  => "Update on PR {$no}",
        };
    }

    private function noteLabel(): string
    {
        return match ($this->event) {
            self::REJECTED           => 'Reason',
            self::REVISION_REQUESTED => 'Revision notes',
            self::CANCELLED          => 'Reason',
            default                  => 'Comment',
        };
    }
}
