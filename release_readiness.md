# Bulk Upload System - Release Readiness

## Architecture
The Svaraa Jewels Bulk Upload System is a modular architecture with:
- Domain models: UploadBatch, BulkUploadPreview, BulkUploadJobLog
- Service layer: BulkUploadService, BulkUploadHealthService, BulkUploadAnalyticsService, BulkUploadRecoveryService
- Queue system using Laravel Queues
- Event-driven architecture for audit and metrics tracking
- Admin UI using Filament
- Notifications via Database and Mail channels

## Metrics
All metrics are captured using BulkUploadJobLog with:
- Job name, worker, status
- Duration, memory usage
- Batch and preview relationships

## Known Limitations
- No support for multi-part PDFs
- No rollback for published products
- No parallel processing of a single batch
- Max job retries: 3

## Rollback
- Use BulkUploadRecoveryService::rollbackBatch() to revert a batch to REVIEW state
- Migrations are reversible using `php artisan migrate:rollback`
- Queue workers can be restarted safely
- Cache can be cleared using `php artisan optimize:clear`

## Operational Runbook
1. **Monitor System Health**
   - Use `php artisan bulk-upload:health` for quick check
   - Monitor the Admin Operations Dashboard

2. **Retry Failed Batches**
   - Use `php artisan bulk-upload:retry-failed` with optional `--limit=N` or `--batch=UUID`

3. **Cleanup Old Data**
   - Use `php artisan bulk-upload:cleanup` with `--dry-run` first to preview changes

4. **Troubleshoot Issues**
   - Check `failed_jobs` table for failed queue jobs
   - Check `bulk_upload_job_logs` for step-by-step batch history
   - Check Laravel logs at `storage/logs/laravel.log`
