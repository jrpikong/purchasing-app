<?php

namespace App\Support;

use App\Models\EmailLog;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\PurchaseRequestAssignedNotification;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Notifications\PurchaseRequestCreatedNotification;
use App\Notifications\PurchaseRequestProgressNotification;
use App\Notifications\PurchaseRequestRejectedNotification;
use App\Notifications\PurchaseRequestRevisionNotification;
use App\Notifications\PurchaseRequestSentForApprovalNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Writes an email_logs row for every purchase-request email:
 * created as pending when sending starts, marked sent on success, and
 * left unsent with an error_message (and retry_count) when delivery fails.
 *
 * Registered explicitly in AppServiceProvider. Logging must never break
 * delivery, so every handler swallows (and reports) its own errors.
 */
class EmailLogRecorder
{
    public function sending(NotificationSending $event): void
    {
        $this->guard(function () use ($event) {
            if (! $this->applies($event->channel, $event->notification, $event->notifiable)) {
                return;
            }

            $log = $this->find($event->notification, $event->notifiable);

            if ($log) {
                // Same notification again → a queue retry
                $log->increment('retry_count');
                return;
            }

            $notification = $event->notification;
            $notifiable = $event->notifiable;
            $mail = $notification->toMail($notifiable);
            $to = $notifiable->routeNotificationFor('mail', $notification);

            EmailLog::create([
                'purchase_request_id' => $notification->purchaseRequest->id,
                'recipient_id' => $notifiable->id,
                'notification_id' => $notification->id,
                'email_type' => static::typeOf($notification),
                'email_to' => static::addresses($to),
                'email_subject' => (string) ($mail->subject ?? class_basename($notification)),
                'is_sent' => false,
                'retry_count' => 0,
            ]);
        });
    }

    public function sent(NotificationSent $event): void
    {
        $this->guard(function () use ($event) {
            if (! $this->applies($event->channel, $event->notification, $event->notifiable)) {
                return;
            }

            $this->find($event->notification, $event->notifiable)?->markAsSent();
        });
    }

    public function failed(NotificationFailed $event): void
    {
        $this->guard(function () use ($event) {
            if (! $this->applies($event->channel, $event->notification, $event->notifiable)) {
                return;
            }

            $error = $event->data['exception'] ?? null;
            $message = $error instanceof Throwable ? $error->getMessage() : json_encode($event->data);

            $this->find($event->notification, $event->notifiable)
                ?->update(['is_sent' => false, 'error_message' => $message]);
        });
    }

    /**
     * Called from a queued notification's failed() hook (see RecordsEmailFailure):
     * the mail channel throws instead of firing NotificationFailed.
     */
    public static function recordQueueFailure(Notification $notification, Throwable $e): void
    {
        try {
            if (! $notification->id) {
                return;
            }

            EmailLog::where('notification_id', $notification->id)
                ->where('is_sent', false)
                ->update(['error_message' => mb_substr($e->getMessage(), 0, 65000)]);
        } catch (Throwable $logError) {
            report($logError);
        }
    }

    public static function typeOf(Notification $notification): string
    {
        return match (true) {
            $notification instanceof PurchaseRequestSentForApprovalNotification => $notification->isReminder
                ? EmailLog::TYPE_REMINDER
                : EmailLog::TYPE_APPROVAL_REQUEST,
            $notification instanceof PurchaseRequestApprovedNotification => EmailLog::TYPE_APPROVED,
            $notification instanceof PurchaseRequestRejectedNotification => EmailLog::TYPE_REJECTED,
            $notification instanceof PurchaseRequestRevisionNotification => EmailLog::TYPE_REVISED,
            $notification instanceof PurchaseRequestCreatedNotification => 'created',
            $notification instanceof PurchaseRequestAssignedNotification => 'assigned',
            $notification instanceof PurchaseRequestProgressNotification => 'progress_' . $notification->event,
            default => \Illuminate\Support\Str::snake(class_basename($notification)),
        };
    }

    /**
     * Mail routes may be "a@x", ["a@x", "b@x"] or ["a@x" => "Name"].
     */
    private static function addresses(mixed $route): string
    {
        if (! is_array($route)) {
            return (string) $route;
        }

        return collect($route)
            ->map(fn ($value, $key) => is_string($key) ? $key : $value)
            ->implode(', ');
    }

    private function applies(string $channel, Notification $notification, mixed $notifiable): bool
    {
        return $channel === 'mail'
            && $notifiable instanceof User
            && property_exists($notification, 'purchaseRequest')
            && $notification->purchaseRequest instanceof PurchaseRequest
            && $notification->id;
    }

    private function find(Notification $notification, User $notifiable): ?EmailLog
    {
        return EmailLog::where('notification_id', $notification->id)
            ->where('recipient_id', $notifiable->id)
            ->latest('id')
            ->first();
    }

    private function guard(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
