<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:reconcile')->everyFiveMinutes();
Schedule::command('payments:metrics:aggregate')->everyFiveMinutes();
Schedule::command('payments:alerts:check')->everyFiveMinutes();
Schedule::command('orders:cleanup-stale-pending')->hourly();
Schedule::command('queue:health-check')->everyFiveMinutes();
Schedule::command('carts:recover-abandoned --minutes=30')->everyThirtyMinutes()->description('Send cart recovery emails');
