<?php

return [
    'alert_email' => env('PAYMENT_ALERT_EMAIL', env('MAIL_FROM_ADDRESS')),

    'thresholds' => [
        'webhook_failures' => (int) env('PAYMENT_ALERT_WEBHOOK_FAILURES', 5),
        'reconciliation_failures' => (int) env('PAYMENT_ALERT_RECONCILIATION_FAILURES', 3),
        'stock_conflicts' => (int) env('PAYMENT_ALERT_STOCK_CONFLICTS', 1),
        'queue_backlog' => (int) env('PAYMENT_ALERT_QUEUE_BACKLOG', 50),
        'failed_jobs' => (int) env('PAYMENT_ALERT_FAILED_JOBS', 1),
        'capture_success_rate_min' => (float) env('PAYMENT_ALERT_CAPTURE_SUCCESS_RATE_MIN', 90),
    ],
];
