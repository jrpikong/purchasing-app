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

Locally, `composer run dev` already starts a `queue:listen` worker for you,
which is why this was easy to miss — it only works because of that dev
script, not because of anything in production.
