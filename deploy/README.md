# Production process requirements

This app needs two background processes running in production that are
**not** covered by `php artisan serve` / the web server alone:

1. **Queue worker** — all `ShouldQueue` notifications (approval emails,
   overdue reminders) are dispatched to the `database` queue connection.
   Nothing sends them unless a worker is running.
   Install `supervisor-worker.conf` (see comments inside) so
   `php artisan queue:work` runs continuously and restarts on crash/deploy.

2. **Scheduler** — `routes/console.php` registers
   `approvals:check-overdue` to run daily via Laravel's scheduler. The
   scheduler itself only ticks when something calls `php artisan schedule:run`
   every minute. Add this cron entry on the server:

   ```
   * * * * * cd /var/www/purchasing-app && php artisan schedule:run >> /dev/null 2>&1
   ```

   The same line is in `deploy/crontab` for copy/paste (`crontab -u www-data deploy/crontab`
   replaces www-data's crontab — merge by hand if it already has entries).

## What the daily `approvals:check-overdue` run does

- Reminds the current approver with a **fresh approval link** (old links stop working)
  when the PR is past its deadline, has waited 48h with no deadline, or its link
  expires within 24h — at most once per ~20h and 3 times per approval level.
- After 3 reminders without a decision it emails the PIC / admin who sent the PR
  (or all admins) once, so a human follows up.
- Admins can also resend the link any time with **"Kirim Ulang Link Approval"**.

## Verifying it works in production

After deploying, check each of these once:

```bash
php artisan migrate --force                 # snapshot / reminder / email-log columns
supervisorctl status purchasing-app-worker:*   # worker RUNNING
php artisan schedule:list                   # approvals:check-overdue listed, "Next Due" set
php artisan approvals:check-overdue         # runs; prints reminder/escalation counts
```

Then in the database:

- `jobs` should be (near) empty — rows piling up means no worker is running.
- `failed_jobs` lists emails that failed after all retries.
- `email_logs` has one row per purchase-request email: `is_sent = 1` when delivered;
  `is_sent = 0` with `error_message` when SMTP failed; `retry_count` > 0 means it was retried.

Locally, `composer run dev` already starts a `queue:listen` worker for you,
which is why this was easy to miss — it only works because of that dev
script, not because of anything in production.
