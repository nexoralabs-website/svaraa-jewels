# Monitoring & Operations Guide

## Admin Dashboard

Access `/admin/operations` to view:

| Metric | Description |
|--------|-------------|
| Orders Today | Number of orders created in the current day |
| Revenue Today | Total captured payment amount (INR) |
| Pending Payments | Orders awaiting capture |
| Failed Payments | Orders that failed payment |
| Refund Count | Refunded orders count |
| Stock Conflicts | Failed captures due to insufficient stock |

Use date range filters to analyze specific periods. Filter by payment or order status to drill down.

## CSV Export

Click "Export CSV" to download filtered orders with columns:
- Order Number, Customer, Email, Total, Payment Status, Order Status, Paid At, Created At

## Payment Audit Trail

Access `/admin/payment-audit` to view:

**Immutable Payment History**
- All payment events are logged and immutable
- View request/response payloads, correlation IDs, latency

**Admin Activity Log**
- All admin retry actions are logged
- Actor, timestamp, and action type recorded

## Queue Health

Access `/admin/queue-health` to view:

| Metric | Meaning |
|--------|---------|
| Pending Jobs | Jobs waiting to be processed |
| Email Jobs | Queued email notifications |
| Failed Jobs | Jobs that exceeded max retries |

**Dead-letter Candidates**: Failed jobs older than 24 hours require manual review before retry.

## Recovering Failed Payments

### Via Admin Panel

1. Navigate to Orders in Filament admin
2. Find failed orders (payment_status = failed)
3. Click "Retry Payment" to reset status to pending
4. For orders stuck in pending, use:
   - "Retry Reconciliation" - re-check with Razorpay API
   - "Retry Verification" - re-verify payment signature
   - "Retry Email" - resend order confirmations
   - "Retry Finalization" - run reconciliation + email

### Via CLI

```bash
# Check alerts
php artisan payments:alerts:check

# Reconcile pending payments
php artisan payments:reconcile

# View failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {uuid}

# Clear all failed jobs
php artisan queue:forget --all
```

## Webhook Retry

If webhooks failed:
1. Check PaymentAudit for webhook entries
2. Note the request payload
3. Click "Retry" on the specific log entry
4. Or manually verify via Razorpay dashboard

## Queue Failure Inspection

```bash
# View failed job details
php artisan queue:failed --verbose

# Retry all failed jobs
php artisan queue:retry all

# Delete failed jobs
php artisan queue:forget all
```

## Alert Thresholds

Configure in `.env`:

```env
PAYMENT_ALERT_WEBHOOK_FAILURES=5       # Alert after 5 failed webhooks/day
PAYMENT_ALERT_RECONCILIATION_FAILURES=3 # Alert after 3 failed reconciliations/day
PAYMENT_ALERT_STOCK_CONFLICTS=1          # Alert on any stock conflict
PAYMENT_ALERT_QUEUE_BACKLOG=50           # Alert when 50+ jobs pending
PAYMENT_ALERT_FAILED_JOBS=1              # Alert on any failed job
PAYMENT_ALERT_CAPTURE_SUCCESS_RATE_MIN=90 # Alert when success < 90%
```