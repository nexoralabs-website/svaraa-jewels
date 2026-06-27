# Queue Supervisor Runbook

Production workers should run the email queue before the default queue so customer notifications are not starved by general jobs.

## Supervisor Configuration

```ini
[program:svaraa-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/svaraa-jewels/artisan queue:work database --queue=emails,default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/svaraa-jewels/storage/logs/queue-worker.log
stopwaitsecs=3600
```

## Scheduler

```cron
* * * * * cd /var/www/svaraa-jewels && php artisan schedule:run >> /dev/null 2>&1
```

## Dead-letter Strategy

1. Inspect `/admin/queue-health` for failed jobs older than 24 hours.
2. Fix the root cause before retrying.
3. Retry by UUID with `php artisan queue:retry {uuid}` or from the admin recovery action.
4. If retry is unsafe, archive the payload and run `php artisan queue:forget {uuid}`.

## Health Checks

```bash
php artisan queue:health-check
php artisan payments:alerts:check
php artisan payments:metrics:aggregate
```

## Scheduled Jobs

| Schedule | Command | Purpose |
|----------|---------|---------|
| Every 5 min | `payments:reconcile` | Reconcile pending Razorpay payments |
| Every 5 min | `payments:metrics:aggregate` | Aggregate daily payment metrics |
| Every 5 min | `payments:alerts:check` | Check payment health thresholds |
| Hourly | `orders:cleanup-stale-pending` | Mark stale pending orders as failed |
| Every 5 min | `queue:health-check` | Report queue backlog and alerts |

## Alert Thresholds (Environment Variables)

```env
PAYMENT_ALERT_EMAIL=ops@svaraa.com
PAYMENT_ALERT_WEBHOOK_FAILURES=5
PAYMENT_ALERT_RECONCILIATION_FAILURES=3
PAYMENT_ALERT_STOCK_CONFLICTS=1
PAYMENT_ALERT_QUEUE_BACKLOG=50
PAYMENT_ALERT_FAILED_JOBS=1
PAYMENT_ALERT_CAPTURE_SUCCESS_RATE_MIN=90
```
