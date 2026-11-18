<?php

namespace App\Notifications\Concerns;

use App\Support\EmailLogRecorder;
use Throwable;

/**
 * Laravel calls failed() on a queued notification once all retries are used up.
 * The mail channel throws rather than firing NotificationFailed, so this is
 * where the delivery error reaches email_logs.
 */
trait RecordsEmailFailure
{
    public function failed(Throwable $e): void
    {
        EmailLogRecorder::recordQueueFailure($this, $e);
    }
}
