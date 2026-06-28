# Bulk Upload Platform — Production Launch Checklist

## Database
- [x] Migrations tested and reversible
- [x] Foreign key constraints configured
- [x] Indexes on frequently-queried columns
- [x] Read-replica configured (if applicable)
- [x] Database backups enabled (point-in-time recovery)

## Queue
- [x] Workers running (≥2 for redundancy)
- [x] Failed jobs table monitored
- [x] Retry logic with exponential backoff configured
- [x] Max tries set appropriately
- [x] Queue timeouts set
- [x] Horizon dashboard accessible (if applicable)

## Storage
- [x] S3 / public disk configured and working
- [x] Storage quotas set
- [x] File lifecycle policies configured
- [x] CORS settings for uploads (if applicable)

## Monitoring
- [x] Health endpoint / bulk-upload:health monitored
- [x] Error tracking configured (Sentry / Bugsnag / etc.)
- [x] Metrics exported to Prometheus / StatsD (if applicable)
- [x] Dashboard for failed batches / previews
- [x] Alerts for high failure rate or queue backlog

## Backup & Recovery
- [x] BulkUploadDisasterRecoveryService available
- [x] Restore command tested
- [x] Snapshot functionality tested
- [x] Rollback strategy for published products documented

## SLA & Incident Response
- [x] SLA documented
- [x] On-call rotation established
- [x] Playbook for common issues available
- [x] Escalation path defined

## Rollback
- [x] Rollback steps documented
- [x] Database rollback tested
- [x] Code rollback tested (git revert + deployment)
- [x] Canary deployment plan if needed
